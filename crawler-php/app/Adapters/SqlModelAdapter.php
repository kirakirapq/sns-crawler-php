<?php

namespace App\Adapters;

use App\Application\InputData\BigQuerySqlModel;
use App\Application\InputData\BigQueryRiskWordSql;
use App\Application\InputData\LatestCommentSql;
use App\Application\InputData\RiskCommentListSql;
use App\Application\InputData\RiskWordListSql;
use Illuminate\Support\Collection;

class SqlModelAdapter
{
    /**
     * getBigQueryRiskWordSql
     *
     * @param  mixed $projectId
     * @param  mixed $datasetId
     * @param  mixed $tableId
     * @param  mixed $riskManageTable
     * @param  mixed $riskwords
     * @param  mixed $appName
     * @param  mixed $language
     * @param  mixed $createdAt
     * @return BigQueryRiskWordSql
     */
    static public function getBigQueryRiskWordSql(
        string $projectId,
        string $datasetId,
        string $tableId,
        string $riskManageTable,
        Collection $riskwords,
        string $appName,
        string $language,
        string $targetField = '',
        ?string $createdAt = null
    ): BigQuerySqlModel {
        return new BigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskwords,
            $appName,
            $language,
            $targetField,
            $createdAt
        );
    }

    /**
     * getLatestCommentSql
     *
     * @param  mixed $projectId
     * @param  mixed $datasetId
     * @param  mixed $tableId
     * @return BigQuerySqlModel
     */
    static public function getLatestCommentSql(
        string $projectId,
        string $datasetId,
        string $tableId
    ): BigQuerySqlModel {
        return new LatestCommentSql($projectId, $datasetId, $tableId);
    }

    /**
     * getRiskWordListSql
     *
     * @param  mixed $projectId
     * @param  mixed $datasetId
     * @return BigQuerySqlModel
     */
    static public function getRiskWordListSql(string $projectId, string $datasetId): BigQuerySqlModel
    {
        return new RiskWordListSql($projectId, $datasetId);
    }

    /**
     * getRisCommentListSql
     *
     * @param  mixed $projectId
     * @param  mixed $datasetId
     * @param  mixed $tableId
     * @param  mixed $title
     * @param  mixed $language
     * @param  mixed $createdAt
     * @return BigQuerySqlModel
     */
    static public function getRisCommentListSql(string $projectId, string $datasetId, string $tableId, ?string $title = null, ?string $language = null, ?string $createdAt = null): BigQuerySqlModel
    {
        return new RiskCommentListSql($projectId, $datasetId, $tableId, $title, $language, $createdAt);
    }
}
