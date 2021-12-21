<?php

namespace App\Adapters;

use App\Entities\ResponseData\BigQuery\BigQueryData;
use App\Entities\RiskWord\RiskCommentList;
use Illuminate\Http\Request;

class RiskWordAdapter
{
    /**
     * getRiskCommentList
     *
     * @param  mixed $apiResponse
     * @return void
     */
    static public function getRiskCommentList(BigQueryData $bigQueryData): RiskCommentList
    {
        $statusCode = $bigQueryData->getStatusCode();
        $errorMessage = $bigQueryData->getErrorMessage();

        $commentList = collect([]);
        foreach ($bigQueryData->getDataList() ?? [] as $data) {
            $commentList->push($data);
        }

        return new RiskCommentList($statusCode, $commentList, $errorMessage);
    }

    /**
     * getRiskCommentListByRequestData
     *
     * @param  mixed $apiResponse
     * @return void
     */
    static public function getRiskCommentListByRequestData(Request $request): RiskCommentList
    {
        $requestData = $request->all();

        $commentList = collect([]);
        foreach ($requestData['messages'] ?? [] as $data) {
            $commentList->push($data);
        }

        return new RiskCommentList(200, $commentList);
    }
}
