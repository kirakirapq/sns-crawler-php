<?php

namespace App\Application\Repositories\BigQuery;

use App\Application\InputData\BigQueryRiskWordSql;
use App\Application\InputData\BigQuerySqlModel;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;

interface BigQueryRepository
{
    /**
     * existsTable
     *
     * @param  mixed $datasetId
     * @param  mixed $tableId
     * @return bool
     */
    public function existsTable(string $datasetId, string $tableId): bool;

    /**
     * loadFromCsv
     *
     * @param  mixed $datasetId
     * @param  mixed $tableId
     * @param  mixed $filename
     * @return InnerApiResponse
     */
    public function loadBigQuery(string $datasetId, string $tableId, string $filename): InnerApiResponse;

    /**
     * insertBigQuery
     *
     * @return void
     */
    public function insertBigQuery(
        string $datasetId,
        string $sorceTableId,
        string $destTableId,
        BigQueryRiskWordSql $sqlModel
    ): InnerApiResponse;

    /**
     * getData
     *
     * @param  mixed $query
     * @return InnerApiResponse
     */
    public function getData(BigQuerySqlModel $query): InnerApiResponse;

    public function exportToBucket(
        $datasetId,
        $tableId,
        $targetDate
    ): void;
}
