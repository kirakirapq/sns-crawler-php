<?php

namespace App\Adapters;

use App\Application\OutputData\InnerApiResponse\CsvResponse;

final class CsvResponseAdapter
{
    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function getCsvResponse(int $statusCode, $result = null): CsvResponse
    {
        return new CsvResponse($statusCode, $result);
    }
}
