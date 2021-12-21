<?php

namespace Unit\Entities\ResponseData\Translation;

use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Entities\ResponseData\Translation\TranslationData;
use Tests\TestCase;
use \Mockery;

class TranslationDataTest extends TestCase
{
    /**
     * getStatusCode
     * @test
     *
     * @return void
     */
    public function getStatusCode(): void
    {
        $expected = 200;

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => $expected,
                'hasError' => false,
                'getBodyAsArray' => [
                    'code' => 200,
                    'text' => '',

                ],
            ])
            ->getMock();

        $entity = new TranslationData($apiResponse);

        $this->assertEquals($expected, $entity->getStatusCode());
    }

    /**
     * hasError
     * @test
     *
     * @return void
     */
    public function hasError(): void
    {
        $expected = false;

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $expected,
                'getBodyAsArray' => [
                    'code' => 200,
                    'text' => '',

                ],
            ])
            ->getMock();

        $entity = new TranslationData($apiResponse);

        $this->assertEquals($expected, $entity->hasError());
    }

    /**
     * getErrorMessage
     * @test
     * @dataProvider getErrorMessageDataProvider
     *
     * @param  mixed $hasError
     * @param  mixed $responsData
     * @param  mixed $expected
     * @return void
     */
    public function getErrorMessage($hasError, $responsData, $expected): void
    {
        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBodyAsArray' => $responsData,
            ])
            ->getMock();

        $entity = new TranslationData($apiResponse);

        $this->assertEquals($expected, $entity->getErrorMessage());
    }

    public function getErrorMessageDataProvider(): array
    {
        return [
            'error case' => [
                'hasError' => true,
                'response' => ['error'],
                'expected' => ['error'],
            ],
            'not error case' => [
                'hasError' => false,
                'response' => [
                    'code' => 200,
                    'text' => '',

                ],
                'expected' => null,
            ]
        ];
    }

    /**
     * getCode
     * @test
     * @dataProvider getCodeProvider
     *
     * @param  mixed $hasError
     * @param  mixed $responsData
     * @param  mixed $expected
     * @return void
     */
    public function getCode($hasError, $responsData, $expected): void
    {
        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBodyAsArray' => $responsData,
            ])
            ->getMock();

        $entity = new TranslationData($apiResponse);

        $this->assertEquals($expected, $entity->getCode());
    }

    public function getCodeProvider(): array
    {
        return [
            'error case' => [
                'hasError' => true,
                'response' => ['error'],
                'expected' => null,
            ],
            'not error case' => [
                'hasError' => false,
                'response' => [
                    'code' => 200,
                    'text' => '',

                ],
                'expected' => 200,
            ]
        ];
    }

    /**
     * getText
     * @test
     * @dataProvider getTextProvider
     *
     * @param  mixed $hasError
     * @param  mixed $responsData
     * @param  mixed $expected
     * @return void
     */
    public function getText($hasError, $responsData, $expected): void
    {
        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBodyAsArray' => $responsData,
            ])
            ->getMock();

        $entity = new TranslationData($apiResponse);

        $this->assertEquals($expected, $entity->getText());
    }

    public function getTextProvider(): array
    {
        return [
            'error case' => [
                'hasError' => true,
                'response' => ['error'],
                'expected' => null,
            ],
            'not error case' => [
                'hasError' => false,
                'response' => [
                    'code' => 200,
                    'text' => 'text data',

                ],
                'expected' => 'text data',
            ]
        ];
    }
}
