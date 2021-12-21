<?php

namespace App\Application\Interactors\Facebook;

use App\Adapters\FacebookApiAdapter;
use App\Adapters\SqlModelAdapter;
use App\Application\Repositories\FacebookApiRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Facebook\FacebookApiUseCase;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

// use \Datetime;

/**
 * FacebookApiUseCaseの実装クラス
 */
final class FacebookApiManager implements FacebookApiUseCase
{
    const LIMIT_COUNT = 500;

    private FacebookApiRepository $repository;
    private BigQueryUseCase $bigQueryUseCase;

    public function __construct(
        FacebookApiRepository $repository,
        BigQueryUseCase $bigQueryUseCase
    ) {
        $this->repository = $repository;
        $this->bigQueryUseCase = $bigQueryUseCase;
    }

    /**
     * getLatestDate
     *
     * @param  mixed $title
     * @return string|null
     */
    public function getLatestData(string $title, string $language): ?string
    {
        if ($this->bigQueryUseCase->existsTable($title, $language) === false) {
            return null;
        }

        $crawlName = Config::get('crawl.name');
        $projectId = $this->bigQueryUseCase->getProjectId();
        $datasetId = $this->bigQueryUseCase->getDatasetId($crawlName);
        $tableId   = $this->bigQueryUseCase->getTableId($crawlName, $title, $language);

        $sqlModel = SqlModelAdapter::getLatestCommentSql($projectId, $datasetId, $tableId);

        $response = $this->bigQueryUseCase->getData($sqlModel);

        if (is_null($response) === true) {
            return null;
        }

        if ($response->hasError() === true) {
            Log::error('FacebookApiManager:getLatestData', [$response->getErrorMessage()]);

            return null;
        }

        if ($response->getDataList()->count() === 0) {
            return null;
        }

        return $response->getDataList()->first()['created_at'] ?? null;
    }

    public function getFeedList(string $title, string $language, $createdAt = null): ?Collection
    {
        Log::debug('FacebookApiManager:getFeedList');
        $type = FacebookApiAdapter::getRequestType('feed');
        $nextPage = null;
        $repeat = true;
        do {
            $requestModel = FacebookApiAdapter::getFeedReqestData($type, $title, $language, $nextPage);
            $fbFeedDataList = $this->repository->getFacebookDataList($requestModel);

            if ($fbFeedDataList->hasNextPage() === false) {
                $repeat = false;
                break;
            }

            $nextPage = $fbFeedDataList->getNextPage();
        } while ($repeat === true);


        if ($fbFeedDataList->isEmpty() === true) {
            return null;
        }

        return $fbFeedDataList->getDataList();
    }

    public function getCommentList(string $title, string $language, Collection $feedList, $createdAt = null): ?Collection
    {
        Log::debug('FacebookApiManager:getCommentList');
        $type = FacebookApiAdapter::getRequestType('comment');
        $field = 'id,created_time,message';
        foreach ($feedList as $i => $feed) {
            $nextPage = null;
            $repeat = true;
            do {
                $requestModel = FacebookApiAdapter::getCommentReqestData($type, $feed['id'], $title, $language, $nextPage, $field);
                $fbCommentkDataList = $this->repository->getFacebookDataList($requestModel);

                if ($fbCommentkDataList->hasNextPage() === false) {
                    $repeat = false;
                    break;
                }

                $nextPage = $fbCommentkDataList->getNextPage();
            } while ($repeat === true);
        }

        if ($fbCommentkDataList->isEmpty() === true) {
            return null;
        }

        return $this->slice($fbCommentkDataList->getDataList(), $createdAt);
    }

    /**
     * isOlder
     * 戻り値の最小日のデータと登録済のデータを比較
     * 古いデータ（=取得済み）があればTrue
     *
     * @param  Collection $commentList
     * @param  mixed $createdAt
     * @return Collection
     */
    public function slice(Collection $commentList, $createdAt = null): Collection
    {
        Log::debug('FacebookApiManager:slice');
        if (is_null($createdAt) === true) {
            return $commentList;
        }

        $bqLatestDate = new Carbon($createdAt);
        return $commentList->filter(function ($value) use ($bqLatestDate) {
            $targetDate = new Carbon($value['created_at']);
            Log::debug('get data(created_at) : bq data(created_at):', ["$targetDate : $bqLatestDate"]);

            // get data > bq latest data
            return $targetDate->gt($bqLatestDate);
        })->values();
    }
}
