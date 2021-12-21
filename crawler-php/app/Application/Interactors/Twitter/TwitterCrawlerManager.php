<?php

namespace App\Application\Interactors\Twitter;

use App\Adapters\OuterApiResponseAdapter;
use App\Adapters\TranslationDataAdapter;
use App\Application\OutputData\InnerApiResponse\NotificationResponseModel;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\UseCases\Twitter\TwitterApiUseCase;
use App\Application\UseCases\Twitter\TwitterCrawlerUseCase;
use App\Entities\BigQuery\Colmun;
use App\Entities\RiskWord\RiskCommentList;
use App\Entities\Translation\TranslationDataList;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * TwitterCrawlerUseCaseの実装クラス
 */
final class TwitterCrawlerManager implements TwitterCrawlerUseCase
{
    private BigQueryUseCase $bigQueryUseCase;
    private TwitterApiUseCase $twitterApiUseCase;
    private TranslationUseCase $translationUseCase;
    private CsvUseCase $csvUseCase;
    private RiskWordUseCase $riskWordUseCase;
    private NotificationUseCase $notificationUseCase;

    public function __construct(
        BigQueryUseCase $bigQueryUseCase,
        TwitterApiUseCase $twitterApiUseCase,
        TranslationUseCase $translationUseCase,
        CsvUseCase $csvUseCase,
        RiskWordUseCase $riskWordUseCase,
        NotificationUseCase $notificationUseCase

    ) {
        $this->bigQueryUseCase = $bigQueryUseCase;
        $this->twitterApiUseCase = $twitterApiUseCase;
        $this->translationUseCase = $translationUseCase;
        $this->csvUseCase = $csvUseCase;
        $this->riskWordUseCase = $riskWordUseCase;
        $this->notificationUseCase = $notificationUseCase;
    }

    public function invokeCrawling(string $sns, string $title, string $language): OuterApiResponse
    {
        try {
            Config::set('crawl.name', 'twitter');

            $userId = Config::get(sprintf('twitter.%s.%s.id', $title, $language));

            Log::info('invokeGetLatestData.');
            $createdAt = $this->invokeGetLatestData($title, $language);

            // メンションリスト取得
            Log::info('invokeGetTwitterMentionList.');
            $mentionDataList = $this->invokeGetTwitterMentionList($userId, $createdAt);

            // 新しいデータがなければ以下の処理をスキップ
            if ($mentionDataList->count() == 0) {
                Log::info('Since there was no data, the subsequent processing will be skipped.');
                return OuterApiResponseAdapter::getFromArray(
                    [
                        'new mention list is not found.'
                    ],
                    200
                );
            }

            // 翻訳
            Log::info('invokeGetTranslationList.');
            $translationData = $this->invokeGetTranslationList($mentionDataList, $language);

            // CSVファイルへ保存
            Log::info('invokeLoadCsvFile.');
            $filename = $this->invokeLoadCsvFile($title, $userId, $translationData);

            // BigQueryへロード
            Log::info('invokeLoadBigQuery.');
            $this->invokeLoadBigQuery($title, $language, $filename);

            // Csvファイルを削除
            Log::info('invokeDeleteCsvFile.');
            $this->invokeDeleteCsvFile($filename);

            // リスクワードを含むコメントを保存
            Log::info('invokeRiskWordCheckUse.');
            $this->invokeRiskWordCheck(Config::get('crawl.name'), $title, $language, $createdAt);

            // リスクコメントを取得
            $riskCommentList = $this->invokeGetRiskCommentList($title, $language, $createdAt);

            if (0 < $riskCommentList->getCommentList()->count()) {
                // リスクコメントを通知
                Log::info('invokeSlackNotify.');
                $notifyModel = $this->invokeSlackNotify($riskCommentList);
            }

            $response = [
                'TwitterCommentDataList' => $translationData->translationData()->all(),
                'RiskCommentList' => $riskCommentList->getCommentList()->all(),
            ];

            return OuterApiResponseAdapter::getFromArray($response, 200);
        } catch (OuterErrorException $e) {
            return OuterApiResponseAdapter::getFromOuterErrorException($e);
        }
    }

    /**
     * 取得済みの最新の１件を取得する
     *
     * @param  mixed $title
     * @param  mixed $language
     * @return string|null
     */
    private function invokeGetLatestData(string $title, string $language): ?Colmun
    {
        $latestData = $this->twitterApiUseCase->getLatestData($title, $language);

        if (is_null($latestData) === true) {
            return null;
        }

        return $latestData->getColmun('created_at');
    }


    /**
     * 前回取得済データより新しいmentionリストを取得する
     *
     * @return void
     */
    public function invokeGetTwitterMentionList(string $userId, ?Colmun $createdAt = null): ?Collection
    {
        return $this->twitterApiUseCase->getTwitterMentionList($userId, $createdAt);
    }

    /**
     * 翻訳する
     *
     * @return void
     */
    public function invokeGetTranslationList(Collection $mentionDataList, string $lung): TranslationDataList
    {
        $request = null;
        // $request = TranslationDataAdapter::getTranslationRequestDataWithGAS('', $lung, 'ja'); // GAS版

        $collection =  $mentionDataList->map(function ($item) use ($lung, $request) {
            // $item['url'] = $request->getUri($item['text']); // GAS版
            $item['language']['source'] = $lung;
            $item['language']['target'] = 'ja';
            $item['translateionTargetFieldName'] = 'text';

            return $item;
        });

        return $this->translationUseCase->translationlist($collection);
    }

    /**
     * CSVファイルへ保存
     *
     * @return string finaname
     */
    public function invokeLoadCsvFile(string $title, string $userId, TranslationDataList $translationData): string
    {
        $filename = sprintf('%s_%s_%s.csv', $title, $userId, time());

        $filtered = $translationData->translationData()->map(function ($item, $key) {
            // unset($item['url']);
            unset($item['language']);
            unset($item['translateionTargetFieldName']);

            return $item;
        });

        return $this->csvUseCase->loadCsv($filename, $filtered);
    }

    /**
     * BigQueryへ保存
     *
     * @return void
     */
    public function invokeLoadBigQuery(string $title, string $language, string $filename)
    {
        $this->bigQueryUseCase->loadBigQuery($title, $language, $filename);
    }

    /**
     * invokeDeleteCsvFile
     *
     * @param  mixed $filename
     * @return void
     */
    public function invokeDeleteCsvFile(string $filename)
    {
        $this->csvUseCase->deleteFile($filename);
    }

    /**
     * リスクワードがないかチェック
     *
     * @return void
     */
    public function invokeRiskWordCheck(string $crawlName, string $title, string $language, ?Colmun  $createdAt = null)
    {
        $targetField = 'text';
        $this->riskWordUseCase->loadRiskComment($crawlName, $title, $language, $targetField, $createdAt);
    }

    /**
     * invokeGetRiskCommentList
     *
     * @param  mixed $title
     * @param  mixed $language
     * @param  mixed $createdAt
     * @return RiskCommentList
     */
    public function invokeGetRiskCommentList(string $title, string $language, ?Colmun $createdAt = null): RiskCommentList
    {
        return $this->riskWordUseCase->getRiskComment($title, $language, $createdAt);
    }

    /**
     * invokeSlackNotify
     *
     * @param  mixed $riskCommentList
     * @return NotificationResponseModel
     */
    public function invokeSlackNotify(RiskCommentList $riskCommentList): NotificationResponseModel
    {
        return $this->notificationUseCase->notifyRiskCommentList($riskCommentList);
    }
}
