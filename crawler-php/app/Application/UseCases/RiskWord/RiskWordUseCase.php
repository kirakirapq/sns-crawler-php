<?php

namespace App\Application\UseCases\RiskWord;

use App\Entities\BigQuery\Colmun;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use App\Entities\RiskWord\RiskCommentList;

interface RiskWordUseCase
{
    /**
     * getRiskWordList
     *
     * @param  mixed $projectId
     * @param  mixed $datasetId
     * @return BigQueryData
     */
    public function getRiskWordList(): BigQueryData;

    /**
     * getRiskComment
     *
     * @param  mixed $title
     * @param  mixed $language
     * @param  mixed $createdAt
     * @return RiskCommentList|null
     */
    public function getRiskComment(
        ?string $title = null,
        ?string $language = null,
        ?Colmun $createdAt = null
    ): ?RiskCommentList;

    /**
     * loadRiskWordComment
     *
     * @param  mixed $crawlName
     * @param  mixed $title
     * @param  mixed $language
     * @param  string $targetField
     * @param  mixed $createdAt
     * @return bool
     */
    public function loadRiskComment(
        string $crawlName,
        string $title,
        string $language,
        string $targetField,
        ?Colmun $createdAt = null
    ): bool;
}
