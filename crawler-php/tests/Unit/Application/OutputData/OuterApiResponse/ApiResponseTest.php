<?php

namespace Unit\Application\OutputData\OuterApiResponse;

use App\Application\OutputData\OuterApiResponse\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    /**
     * getStatusCode
     * @test
     *
     * @return void
     */
    public function getStatusCode(): void
    {
        $model = new ApiResponse([], 200);

        $this->assertEquals(200, $model->getStatusCode());
    }

    /**
     * getMessage
     * @test
     *
     * @return void
     */
    public function getMessage(): void
    {
        $model = new ApiResponse([], 200);

        $this->assertEquals([], $model->getMessage());
    }
}
