<?php

namespace App\Application\Interactors\Reddit;

use App\Adapters\BigQueryResponseAdapter;
use App\Adapters\RedditApiAdapter;
use App\Adapters\SqlModelAdapter;
use App\Adapters\TargetDateAdapter;
use App\Application\Repositories\RedditApiRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Reddit\RedditApiUseCase;
use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
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
    const LIMIT_COUNT = 1000;

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
     * @return LatestData|null
     */
    public function getLatestData(string $title, string $language): ?LatestData
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

        return BigQueryResponseAdapter::getLatestData($tableId, $response->getDataList());
    }

    public function getThreadList(string $id, ?Colmun $createdAt = null): ?Collection
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

    public function getCommentList(Collection $threadList, ?Colmun $createdAt = null): ?Collection
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

        $commentList = $this->slice(
            $commentList->unique('id')->values(),
            $createdAt
        );
        info('slice -  count:', [$commentList->count()]);

        return $commentList;
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
    public function slice(Collection $commentList, ?Colmun $createdAt = null): Collection
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

        $bqLatestDate = TargetDateAdapter::getTargetDate($createdAt->getValue());

        return $commentList->filter(function ($value) use ($bqLatestDate, $createdAt) {
            if (array_key_exists('text', $value) === false) {
                Log::debug('undefined index [text]', [$value]);
                return false;
            }

            if (empty($value['text']) === true) {
                Log::debug('empty data', [$value]);
                return false;
            }

            $targetDate = TargetDateAdapter::getTargetDate($value[$createdAt->getName()]);

            if ($targetDate->getCarbon()->gt($bqLatestDate->getCarbon()) === true) {
                Log::info(
                    '[前回取得したデータよりも新しい] reddit data(created) : bq data(created):',
                    [$value[$createdAt->getName()] . " : " . $createdAt->getValue()]
                );
            }

            // 時間で比較した場合と整数値で比較した場合の結果が異なる場合
            $int_result = (int) $value[$createdAt->getName()] > $createdAt->getValue();
            $time_result =  $targetDate->getCarbon()->gt($bqLatestDate->getCarbon());

            if ($int_result !== $time_result) {
                Log::info(
                    '時間で比較した場合と整数値で比較した場合の結果が異なる',
                    [
                        '実際の値' => [
                            'redditから取得した値' => $value[$createdAt->getName()],
                            'big queryのlatest data' => $createdAt->getValue(),
                        ],
                        '数値で比較した場合' => $int_result,
                        '時間で比較した場合' => $time_result,
                    ]
                );
            }

            // get data > bq latest data
            return $targetDate->getCarbon()->gt($bqLatestDate->getCarbon());
        })->values();
    }
}
