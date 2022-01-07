<?php

namespace App\Application\Interactors\Facebook;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\UseCases\Facebook\FacebookApiUseCase;
use App\Application\UseCases\Facebook\FacebookCrawlerUseCase;
use App\Entities\BigQuery\Colmun;
use App\Entities\Notification\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;
use App\Entities\Translation\TranslationDataList;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * FacebookCrawlerUseCaseの実装クラス
 */
final class FacebookCrawlerManager implements FacebookCrawlerUseCase
{
    private BigQueryUseCase $bigQueryUseCase;
    private FacebookApiUseCase $facebookApiUseCase;
    private TranslationUseCase $translationUseCase;
    private CsvUseCase $csvUseCase;
    private RiskWordUseCase $riskWordUseCase;
    private NotificationUseCase $notificationUseCase;

    public function __construct(
        BigQueryUseCase $bigQueryUseCase,
        FacebookApiUseCase $facebookApiUseCase,
        TranslationUseCase $translationUseCase,
        CsvUseCase $csvUseCase,
        RiskWordUseCase $riskWordUseCase,
        NotificationUseCase $notificationUseCase

    ) {
        $this->bigQueryUseCase = $bigQueryUseCase;
        $this->facebookApiUseCase = $facebookApiUseCase;
        $this->translationUseCase = $translationUseCase;
        $this->csvUseCase = $csvUseCase;
        $this->riskWordUseCase = $riskWordUseCase;
        $this->notificationUseCase = $notificationUseCase;
    }

    public function invokeCrawling(string $sns, string $title, string $language): OuterApiResponse
    {
        try {
            Config::set('crawl.name', 'facebook');

            $id = Config::get(sprintf('facebook.%s.%s.id', $title, $language));

            Log::info('invokeGetLatestData.');
            $colmun = $this->invokeGetLatestData($title, $language);

            // スレッド一覧取得
            Log::info('getFeedList.');
            $feedList = $this->invokeGetFeedList($title, $language, $colmun);

            if (is_null($feedList) === true) {
                return OuterApiResponseAdapter::getFromArray(
                    [
                        'feed list is not found.'
                    ],
                    200
                );
            }

            // スレッド一覧取得
            Log::info('invokeGetCommentList.');
            $commentList = $this->invokeGetCommentList($title, $language, $feedList, $colmun);

            // 新しいデータがなければ以下の処理をスキップ
            if ($commentList->count() === 0) {
                Log::info('Since there was no data, the subsequent processing will be skipped.');
                return OuterApiResponseAdapter::getFromArray(
                    [
                        'new comment list is not found.'
                    ],
                    200
                );
            }

            // 翻訳
            Log::info('invokeGetTranslationList.');
            $translationData = $this->invokeGetTranslationList($commentList, $language);

            // CSVファイルへ保存
            Log::info('invokeLoadCsvFile.');
            $filename = $this->invokeLoadCsvFile($title, $id, $translationData);

            // BigQueryへロード
            Log::info('invokeLoadBigQuery.');
            $this->invokeLoadBigQuery($title, $language, $filename);

            // Csvファイルを削除
            Log::info('invokeDeleteCsvFile.');
            $this->invokeDeleteCsvFile($filename);

            // リスクワードを含むコメントを保存
            Log::info('invokeRiskWordCheck.');
            $this->invokeLoadRiskWordCheck(Config::get('crawl.name'), $title, $language, $colmun);

            // リスクコメントを取得
            $riskCommentList = $this->invokeGetRiskCommentList($title, $language, $colmun);

            if (0 < $riskCommentList->getCommentList()->count()) {
                // リスクコメントを通知
                Log::info('invokeSlackNotify.');
                $notifyModel = $this->invokeSlackNotify($riskCommentList);
            }

            $response = [
                'FacebookCommentDataList' => $translationData->translationData()->all(),
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
        $latestData = $this->facebookApiUseCase->getLatestData($title, $language);

        if (is_null($latestData) === true) {
            return null;
        }

        return $latestData->getColmun('created_at');
    }

    /**
     * invokeGetThreadList
     * subreddit内のスレッド一覧取得
     *
     * @param  mixed $id
     * @param  mixed $latestData
     * @return Collection
     */
    public function invokeGetFeedList(string $title, string $language, $colmun = null): ?Collection
    {
        return $this->facebookApiUseCase->getFeedList($title, $language, $colmun);
    }

    /**
     * invokeGetCommentList
     * thread一覧のコメントを取得する
     *
     * @param  mixed $threadList
     * @param  mixed $latestData
     * @return Collection
     */
    public function invokeGetCommentList(string $title, string $language, Collection $feedList, $colmun = null): ?Collection
    {
        return $this->facebookApiUseCase->getCommentList($title, $language, $feedList, $colmun);
    }

    /**
     * 翻訳する
     *
     * @return void
     */
    public function invokeGetTranslationList(Collection $commentList, string $lung): TranslationDataList
    {
        // $request = TranslationRequestDataApiAdapter::getTranslationRequestData('', $lung, 'ja');

        $collection =  $commentList->map(function ($item) use ($lung) {
            $item['language']['source'] = $lung;
            $item['language']['target'] = 'ja';
            $item['translateionTargetFieldName'] = 'message';

            return $item;
        });

        return $this->translationUseCase->translationlist($collection);
    }

    /**
     * CSVファイルへ保存
     *
     * @return string finaname
     */
    public function invokeLoadCsvFile(string $title, string $id, TranslationDataList $translationData): string
    {
        $filename = sprintf('%s_%s_%s.csv', $title, $id, time());

        $filtered = $translationData->translationData()->map(function ($item, $key) {
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
    public function invokeLoadRiskWordCheck(string $crawlName, string $title, string $language, ?Colmun $colmun = null)
    {
        $targetField = 'message';
        $this->riskWordUseCase->loadRiskComment($crawlName, $title, $language, $targetField, $colmun);
    }

    /**
     * invokeGetRiskCommentList
     *
     * @param  mixed $title
     * @param  mixed $language
     * @param  mixed $colmun
     * @return RiskCommentList
     */
    public function invokeGetRiskCommentList(string $title, string $language, ?Colmun $colmun = null): RiskCommentList
    {
        return $this->riskWordUseCase->getRiskComment($title, $language, $colmun);
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
