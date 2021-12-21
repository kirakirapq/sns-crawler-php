<?php

namespace App\Adapters;

use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use App\Entities\ResponseData\BigQuery\BigQueryData;
use Google\Cloud\BigQuery\QueryResults;

final class BigQueryResponseAdapter
{
    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function getBigQueryResponse(int $statusCode, ?QueryResults $result = null): BigQueryResponse
    {
        return new BigQueryResponse($statusCode, $result);
    }

    static public function getBigqueryData(BigQueryResponse $apiResponse): BigQueryData
    {
        return new BigqueryData($apiResponse);
    }
}
