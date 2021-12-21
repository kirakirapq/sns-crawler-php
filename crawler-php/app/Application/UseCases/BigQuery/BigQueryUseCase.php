<?php

namespace App\Application\UseCases\BigQuery;

use App\Application\InputData\BigQuerySqlModel;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Entities\ResponseData\Bigquery\BigQueryData;

interface BigQueryUseCase
{
    /**
     * existsTable
     *
     * @param  mixed $title
     * @return bool
     */
    public function existsTable(string $title, string $language): bool;

    /**
     * loadBigQuery
     *
     * @param  mixed $title
     * @param  mixed $language
     * @param  mixed $filename
     * @return void
     */
    public function loadBigQuery(string $title, string $language, string $filename): void;

    /**
     * getData
     *
     * @param  mixed $sqlModel
     * @return BigQueryData
     */
    public function getData(BigQuerySqlModel $sqlModel): ?BigQueryData;

    /**
     * insertBigQuery
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
    ): InnerApiResponse;

    /**
     * getProjectId
     *
     * @return string
     */
    public function getProjectId(): string;

    /**
     * getDatasetId
     *
     * @param  mixed $crawlName
     * @return string
     */
    public function getDatasetId(string $crawlName): string;

    /**
     * getTableId
     *
     * @param  mixed $crawlName
     * @param  mixed $appName
     * @param  mixed $language
     * @return string
     */
    public function getTableId(string $crawlName, string $appName, string $language): string;

    /**
     * getRiskCommentTableId
     *
     * @param  mixed $crawlName
     * @param  mixed $appName
     * @param  mixed $language
     * @return string
     */
    public function getRiskCommentTableId(string $crawlName, string $appName, string $language): string;

    public function exportToBucket(
        string $datasetId,
        string $tableId,
        string $targetDate
    ): InnerApiResponse;
}
