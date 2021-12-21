<?php

namespace App\Application\Interactors\Twitter;

use App\Adapters\SqlModelAdapter;
use App\Application\Repositories\TwitterApiRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Twitter\TwitterApiUseCase;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

// use \Datetime;

/**
 * TwitterApiUseCaseの実装クラス
 */
final class TwitterApiManager implements TwitterApiUseCase
{
    const LIMIT_COUNT = 500;

    private TwitterApiRepository $repository;
    private BigQueryUseCase $bigQueryUseCase;

    public function __construct(
        TwitterApiRepository $repository,
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
            Log::error('TwitterApiManager:getLatestData', [$response->getErrorMessage()]);

            return null;
        }

        if ($response->getDataList()->count() === 0) {
            return null;
        }

        return $response->getDataList()->first()['created_at'] ?? null;
    }

    public function getTwitterMentionList(string $userId, $createdAt = null): ?Collection
    {
        Log::debug('TwitterApiManager:getTwitterMentionList');
        $response = null;
        $loop = true;
        $paginationToken = null;
        do {
            $response = $this->repository->getMentions($userId, $paginationToken);
            $mentionList = $response->getMentionList();

            if (self::LIMIT_COUNT <= $mentionList->count()) {
                Log::debug('getTwitterMentionList Count:', [$mentionList->count()]);
                $loop = false;
            }

            $result = $this->slice($mentionList, $createdAt);

            if ($result['isSlice'] === true) {
                return $result['data'];
            }

            $metaData = $response->getMetaData();
            $paginationToken = $metaData->getNextToken();

            if (is_null($paginationToken) === true) {
                $loop = false;
                break;
            }
        } while ($loop);

        if (is_null($createdAt) === true) {
            return  $response->getMentionList();
        }

        return  $response->getMentionList()->where('created_at', '>', $createdAt);
    }

    /**
     * isOlder
     * 戻り値の最小日のデータと登録済のデータを比較
     * 古いデータ（=取得済み）があればTrue
     *
     * @param  Collection $response
     * @param  mixed $createdAt
     * @return array
     */
    public function slice(Collection $response, $createdAt = null): array
    {
        Log::debug('TwitterApiManager:slice');
        if (is_null($createdAt) === true) {
            return ['isSlice' => false, 'data' => $response];
        }

        $original = $response->count();

        $bqLatestDate = new Carbon($createdAt);
        $slice = $response->filter(function ($value) use ($bqLatestDate) {
            $targetDate = new Carbon($value['created_at']);
            Log::debug('get data(created_at) > bq data(created_at):', ["$targetDate > $bqLatestDate"]);

            // get data > bq latest data
            return $targetDate->gt($bqLatestDate);
        })
            ->values(); // keyフリ直し

        return [
            'isSlice' => $original != $slice->count(),
            'data' => $slice
        ];
    }
}
