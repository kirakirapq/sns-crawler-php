<?php

namespace App\Application\Interactors\Reddit;

use App\Adapters\OuterApiResponseAdapter;
use App\Adapters\TranslationDataAdapter;
use App\Application\OutputData\InnerApiResponse\NotificationResponseModel;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\UseCases\Reddit\RedditApiUseCase;
use App\Application\UseCases\Reddit\RedditCrawlerUseCase;
use App\Entities\RiskWord\RiskCommentList;
use App\Entities\Translation\TranslationDataList;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use \DateTime;

/**
 * RedditCrawlerUseCaseの実装クラス
 */
final class RedditCrawlerManager implements RedditCrawlerUseCase
{
    private BigQueryUseCase $bigQueryUseCase;
    private RedditApiUseCase $redditApiUseCase;
    private TranslationUseCase $translationUseCase;
    private CsvUseCase $csvUseCase;
    private RiskWordUseCase $riskWordUseCase;
    private NotificationUseCase $notificationUseCase;

    public function __construct(
        BigQueryUseCase $bigQueryUseCase,
        RedditApiUseCase $redditApiUseCase,
        TranslationUseCase $translationUseCase,
        CsvUseCase $csvUseCase,
        RiskWordUseCase $riskWordUseCase,
        NotificationUseCase $notificationUseCase

    ) {
        $this->bigQueryUseCase = $bigQueryUseCase;
        $this->redditApiUseCase = $redditApiUseCase;
        $this->translationUseCase = $translationUseCase;
        $this->csvUseCase = $csvUseCase;
        $this->riskWordUseCase = $riskWordUseCase;
        $this->notificationUseCase = $notificationUseCase;
    }

    public function invokeCrawling(string $sns, string $title, string $language): OuterApiResponse
    {
        try {
            Config::set('crawl.name', 'reddit');

            $id = Config::get(sprintf('reddit.%s.%s.id', $title, $language));

            Log::info('invokeGetLatestData.');
            $createdAt = $this->invokeGetLatestData($title, $language);

            // スレッド一覧取得
            Log::info('getThreadList.');
            $threadList = $this->invokeGetThreadList($id, $createdAt);

            if (is_null($threadList) === true) {
                return OuterApiResponseAdapter::getFromArray(
                    [
                        'thread list is not found.'
                    ],
                    200
                );
            }

            // スレッド一覧取得
            Log::info('invokeGetCommentList.');
            $commentList = $this->invokeGetCommentList($threadList, $createdAt);

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
            $this->invokeLoadRiskWordCheck(Config::get('crawl.name'), $title, $language, $createdAt);

            // リスクコメントを取得
            $riskCommentList = $this->invokeGetRiskCommentList($title, $language, $createdAt);

            if (0 < $riskCommentList->getCommentList()->count()) {
                // リスクコメントを通知
                Log::info('invokeSlackNotify.');
                $notifyModel = $this->invokeSlackNotify($riskCommentList);
            }

            $response = [
                'RedditCommentDataList' => $translationData->translationData()->all(),
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
    private function invokeGetLatestData(string $title, string $language): ?string
    {
        return $this->redditApiUseCase->getLatestData($title, $language);
    }

    /**
     * invokeGetThreadList
     * subreddit内のスレッド一覧取得
     *
     * @param  mixed $id
     * @param  mixed $createdAt
     * @return Collection
     */
    public function invokeGetThreadList(string $id, $createdAt = null): ?Collection
    {
        return $this->redditApiUseCase->getThreadList($id, $createdAt);
    }

    /**
     * invokeGetCommentList
     * thread一覧のコメントを取得する
     *
     * @param  mixed $threadList
     * @param  mixed $createdAt
     * @return Collection
     */
    public function invokeGetCommentList(Collection $threadList, $createdAt = null): ?Collection
    {
        return $this->redditApiUseCase->getCommentList($threadList, $createdAt);
    }

    /**
     * 翻訳する
     *
     * @return void
     */
    public function invokeGetTranslationList(Collection $commentList, string $lung): TranslationDataList
    {
        $request = null;
        // $request = TranslationDataAdapter::getTranslationRequestDataWithGAS('', $lung, 'ja');

        $collection =  $commentList->map(function ($item) use ($lung, $request) {
            // $item['url'] = $request->getUri($item['text']);
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
    public function invokeLoadRiskWordCheck(string $crawlName, string $title, string $language, string $createdAt = null)
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
    public function invokeGetRiskCommentList(string $title, string $language, string $createdAt = null): RiskCommentList
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
