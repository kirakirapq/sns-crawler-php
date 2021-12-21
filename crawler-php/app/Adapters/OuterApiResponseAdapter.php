<?php

namespace App\Adapters;

use App\Application\OutputData\OuterApiResponse\ApiResponse;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Collection;

class OuterApiResponseAdapter
{
    /**
     * getFromOuterErrorException
     *
     * @param  mixed $errorExeption
     * @return OuterApiResponse
     */
    static public function getFromOuterErrorException(OuterErrorException $errorExeption): OuterApiResponse
    {
        return new ApiResponse(json_decode($errorExeption->getMessage(), true), $errorExeption->getCode());
    }

    /**
     * getFromCollection
     *
     * @param  Collection $collection
     * @param  int $code
     * @return OuterApiResponse
     */
    static public function getFromCollection(Collection $collection, int $code): OuterApiResponse
    {
        return new ApiResponse($collection->all(), $code);
    }

    /**
     * getFromArray
     *
     * @param  array $response
     * @param  int $code
     * @return OuterApiResponse
     */
    static public function getFromArray(array $response, int $code): OuterApiResponse
    {
        return new ApiResponse($response, $code);
    }
}
