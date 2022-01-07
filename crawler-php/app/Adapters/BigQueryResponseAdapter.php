<?php

namespace App\Adapters;

use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use App\Entities\ResponseData\BigQuery\BigQueryData;
use Google\Cloud\BigQuery\QueryResults;
use Illuminate\Support\Collection;

final class BigQueryResponseAdapter
{
    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return InnerApiResponse
     */
    static public function getBigQueryResponse(int $statusCode, ?QueryResults $result = null): InnerApiResponse
    {
        return new BigQueryResponse($statusCode, $result);
    }

    static public function getBigqueryData(InnerApiResponse $apiResponse): BigQueryData
    {
        return new BigqueryData($apiResponse);
    }

    static public function getLatestData(string $tableName, Collection $rows): LatestData
    {
        $colmuns = [];
        foreach ($rows->first() as $colmn => $value) {
            $colmuns[$colmn] = new Colmun($colmn, $value);
        }

        return new LatestData($tableName, $colmuns);
    }
}
