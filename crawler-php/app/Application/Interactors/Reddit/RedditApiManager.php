<?php

namespace App\Application\Interactors\Reddit;

use App\Adapters\RedditApiAdapter;
use App\Adapters\SqlModelAdapter;
use App\Application\Repositories\RedditApiRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Reddit\RedditApiUseCase;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

// use \Datetime;

/**
 * RedditApiUseCaseの実装クラス
 */
final class RedditApiManager implements RedditApiUseCase
{
    const LIMIT_COUNT = 500;

    private RedditApiRepository $repository;
    private BigQueryUseCase $bigQueryUseCase;

    public function __construct(
        RedditApiRepository $repository,
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
            Log::error('RedditApiManager:getLatestData', [$response->getErrorMessage()]);

            return null;
        }

        if ($response->getDataList()->count() === 0) {
            return null;
        }

        return $response->getDataList()->first()['created_at'] ?? null;
    }

    public function getThreadList(string $id, $createdAt = null): ?Collection
    {
        Log::debug('RedditApiManager:getThreadList');
        $requestModel = RedditApiAdapter::getSubRedditRequestData();
        $response = $this->repository->getSubReddit($requestModel, $id);

        $threadList = $response->getThreadList();

        if ($threadList->count() === 0) {
            return null;
        }

        return $threadList;
    }

    public function getCommentList(Collection $threadList, $createdAt = null): ?Collection
    {
        $commentList = collect([]);
        Log::debug('RedditApiManager:getCommentList');
        foreach ($threadList as $i => $thread) {
            $requestModel = RedditApiAdapter::getCommentRequestData($thread);
            $new_thread = $this->repository->getComment($requestModel);
            $comments = $new_thread->getCommentList();

            if (count($comments) == 0) {
                continue;
            }

            foreach ($comments as $i => $comment) {
                $commentList->push($comment);
            }
        }

        return $this->slice($commentList, $createdAt);
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
        Log::debug('RedditApiManager:slice');
        if (is_null($createdAt) === true) {
            return $commentList->filter(function ($value) {
                if (array_key_exists('text', $value) === false) {
                    Log::debug('undefined index [text]', [$value]);
                    return false;
                }

                if (empty($value['text']) === true) {
                    Log::debug('empty data', [$value]);
                    return false;
                }

                return true;
            })->values();
        }

        $bqLatestDate = new Carbon($createdAt);
        return $commentList->filter(function ($value) use ($bqLatestDate) {
            if (array_key_exists('text', $value) === false) {
                Log::debug('undefined index [text]', [$value]);
                return false;
            }

            if (empty($value['text']) === true) {
                Log::debug('empty data', [$value]);
                return false;
            }

            $targetDate = new Carbon($value['created_at']);
            Log::debug('get data(created_at) : bq data(created_at):', ["$targetDate : $bqLatestDate"]);

            // get data > bq latest data
            return $targetDate->gt($bqLatestDate);
        })->values();
    }
}
