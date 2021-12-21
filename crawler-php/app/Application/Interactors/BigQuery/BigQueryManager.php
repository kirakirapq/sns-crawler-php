<?php

namespace App\Application\Interactors\BigQuery;

use App\Adapters\BigQueryResponseAdapter;
use App\Application\InputData\BigQuerySqlModel;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\Repositories\BigQuery\BigQueryRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class BigQueryManager implements BigQueryUseCase
{
    private BigQueryRepository $bigqueryRepository;

    public function __construct(BigQueryRepository $bigqueryRepository)
    {
        $this->bigqueryRepository = $bigqueryRepository;
    }

    /**
     * existsTable
     *
     * @param  mixed $title
     * @param  mixed $lunguage
     * @return bool
     */
    public function existsTable(string $title, string $lunguage): bool
    {
        $crawlName = Config::get('crawl.name');
        $datasetId = $this->getDatasetId($crawlName);
        $tableId   = $this->getTableId($crawlName, $title, $lunguage);

        return $this->bigqueryRepository->existsTable($datasetId, $tableId);
    }

    /**
     * getData
     *
     * @param  mixed $sqlModel
     * @return BigQueryData
     */
    public function getData(BigQuerySqlModel $sqlModel): ?BigQueryData
    {
        $response = $this->bigqueryRepository->getData($sqlModel);

        if ($response->hasError() === true) {
            Log::error('BigQueryManager:getData', [$response->getBody()]);

            return null;
        }

        return BigQueryResponseAdapter::getBigqueryData($response);
    }

    /**
     * loadFromCsv
     * csvからデータをロード
     *
     * @param  mixed $title
     * @param  mixed $lunguage
     * @param  mixed $filename
     * @return void
     */
    public function loadBigQuery(string $title, string $lunguage, string $filename): void
    {
        $crawlName = Config::get('crawl.name');
        $datasetId = $this->getDatasetId($crawlName);
        $tableId   = $this->getTableId($crawlName, $title, $lunguage);

        $response = $this->bigqueryRepository->loadBigQuery($datasetId, $tableId, $filename);

        if ($response->hasError() === true) {
            Log::error('BigQueryManager:loadFromCsv', [$response->getBody()]);
        }
    }

    /**
     * insertBigQuery
     * $sorceTableIdのsqlの結果をd$destTableIdへロード
     *
     * @param  mixed $datasetId
     * @param  mixed $sorceTableId
     * @param  mixed $destTableId
     * @param  mixed $sqlModel
     * @return InnerApiResponse
     */
    public function insertBigQuery(
        string $datasetId,
        string $sorceTableId,
        string $destTableId,
        BigQuerySqlModel $sqlModel
    ): InnerApiResponse {
        $response = $this->bigqueryRepository->insertBigQuery(
            $datasetId,
            $sorceTableId,
            $destTableId,
            $sqlModel
        );

        if ($response->hasError() === true) {
            Log::error('TwitterBigqueryManager:insertBigQuery', [$response->getBody()]);
        }

        return $response;
    }

    /**
     * getProjectId
     *
     * @return string
     */
    public function getProjectId(): string
    {
        return Config::get('app.GCP_PROJECT_ID');
    }

    /**
     * getDatasetId
     *
     * @param  mixed $crawlName
     * @return string
     */
    public function getDatasetId(string $crawlName): string
    {
        if ($crawlName === 'twitter') {
            return Config::get('app.TWITTER_DATASET_ID');
        }

        if ($crawlName === 'reddit') {
            return Config::get('app.REDDIT_DATASET_ID');
        }

        return Config::get('app.DEFAULT_DATASET_ID');
    }

    /**
     * getTableId
     *
     * @param  mixed $appName
     * @param  mixed $crawlName
     * @return string
     */
    public function getTableId(string $crawlName, string $appName, string $lunguage): string
    {
        return Config::get(sprintf('%s.%s.%s.comment_table_id', $crawlName, $appName, $lunguage));
    }

    /**
     * getRiskCommentTableId
     *
     * @param  mixed $appName
     * @param  mixed $crawlName
     * @return string
     */
    public function getRiskCommentTableId(string $crawlName, string $appName, string $lunguage): string
    {
        return Config::get(sprintf('%s.%s.%s.risk_word_table_id', $crawlName, $appName, $lunguage));
    }

    public function exportToBucket(
        string $datasetId,
        string $tableId,
        string $targetDate
    ): InnerApiResponse {
        $response = $this->bigqueryRepository->exportToBucket(
            $datasetId,
            $tableId,
            $targetDate
        );

        if ($response->hasError() === true) {
            Log::error('TwitterBigqueryManager:insertBigQuery', [$response->getBody()]);
        }

        return $response;
    }
}
