<?php

namespace Unit\Adapters;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\OutputData\OuterApiResponse\ApiResponse;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class OuterApiResponseAdapterTest extends TestCase
{
    /**
     * getFromOuterErrorException
     * @test
     */
    public function getFromOuterErrorException(): void
    {
        Log::shouldReceive('error');
        $errorDefinition = new ErrorDefinition(LayerCode::CONTROLL_LAYER_CODE, 500);
        $errorExeption = new OuterErrorException($errorDefinition, 'message');

        $expected = new ApiResponse(json_decode($errorExeption->getMessage(), true), $errorExeption->getCode());
        $actual = OuterApiResponseAdapter::getFromOuterErrorException($errorExeption);

        $this->assertEquals($expected, $actual);
    }

    /**
     * getFromOuterErrorException
     * @test
     *
     * @return void
     */
    public function getFromCollection(): void
    {
        $code = 200;
        $collection = collect(['test']);
        $expected = new ApiResponse($collection->all(), $code);
        $actual = OuterApiResponseAdapter::getFromCollection($collection, $code);

        $this->assertEquals($expected, $actual);
    }

    /**
     * getFromArray
     * @test
     *
     * @return void
     */
    public function getFromArray(): void
    {
        $code = 200;
        $array = ['test', '123'];
        $expected = new ApiResponse($array, $code);
        $actual = OuterApiResponseAdapter::getFromArray($array, $code);

        $this->assertEquals($expected, $actual);
    }
}
