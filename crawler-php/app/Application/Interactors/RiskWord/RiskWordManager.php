<?php

namespace App\Application\Interactors\RiskWord;

use App\Adapters\RiskWordAdapter;
use App\Adapters\SqlModelAdapter;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Entities\BigQuery\Colmun;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use App\Entities\RiskWord\RiskCommentList;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class RiskWordManager implements RiskWordUseCase
{
    private BigQueryUseCase $bigQueryUseCase;

    public function __construct(BigQueryUseCase $bigQueryUseCase)
    {
        $this->bigQueryUseCase = $bigQueryUseCase;
    }

    /**
     * getRiskWordList
     *
     * @param  mixed $projectId
     * @param  mixed $datasetId
     * @return BigQueryData
     */
    public function getRiskWordList(): BigQueryData
    {
        $crawlName = Config::get('crawl.name');
        $projectId = $this->bigQueryUseCase->getProjectId();
        $datasetId = $this->bigQueryUseCase->getDatasetId($crawlName);

        $sqlModel = SqlModelAdapter::getRiskWordListSql($projectId, $datasetId);

        $response = $this->bigQueryUseCase->getData($sqlModel);

        if ($response->hasError() === true) {
            Log::error('RiskWordManager:getRiskWordList', [$response->getErrorMessage()]);
        }

        return $response;
    }

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
    ): ?RiskCommentList {
        $crawlName = Config::get('crawl.name');
        $projectId = $this->bigQueryUseCase->getProjectId();
        $datasetId = $this->bigQueryUseCase->getDatasetId($crawlName);
        $tableId   = $this->bigQueryUseCase->getRiskCommentTableId($crawlName, $title, $language);

        $sqlModel = SqlModelAdapter::getRisCommentListSql(
            $projectId,
            $datasetId,
            $tableId,
            $title,
            $language,
            $createdAt
        );
        $response = $this->bigQueryUseCase->getData($sqlModel);

        return RiskWordAdapter::getRiskCommentList($response);
    }

    /**
     * loadRiskComment
     *
     * @param  mixed $crawlName
     * @param  mixed $title
     * @param  mixed $language
     * @param  mixed $targetField
     * @param  mixed $createdAt
     * @return bool
     */
    public function loadRiskComment(
        string $crawlName,
        string $title,
        string $language,
        string $targetField = 'text',
        ?Colmun $createdAt = null
    ): bool {
        $projectId    = $this->bigQueryUseCase->getProjectId();
        $datasetId    = $this->bigQueryUseCase->getDatasetId($crawlName);
        $sorceTableId = $this->bigQueryUseCase->getTableId($crawlName, $title, $language);
        $destTableId  = $this->bigQueryUseCase->getRiskCommentTableId($crawlName, $title, $language);

        $riskWordResponse = $this->getRiskWordList();
        $riskWords = $riskWordResponse->getDataList();

        $sqlModel = SqlModelAdapter::getBigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $sorceTableId,
            $destTableId,
            $riskWords,
            $title,
            $language,
            $targetField,
            $createdAt
        );

        $insertResponse = $this->bigQueryUseCase->insertBigQuery(
            $datasetId,
            $sorceTableId,
            $destTableId,
            $sqlModel
        );

        if ($insertResponse->hasError() === true) {
            Log::error('RiskWordManager:loadRiskWordComment', [$insertResponse->getBody()]);

            return false;
        }

        return true;
    }
}
