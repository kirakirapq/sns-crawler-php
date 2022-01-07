<?php

namespace Unit\Interractoers\RiskWord;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\Interactors\RiskWord\RiskWordInvoker;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Entities\RiskWord\RiskCommentList;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class RiskWordInvokerTest extends TestCase
{
    /**
     * invokeNotifyRiskCommentList
     * @test
     * @dataProvider invokeNotifyRiskCommentListDataProvider
     *
     * @return void
     */
    public function invokeGetRiskComments(string|array $response, ?string $errorMessage, int $statusCode, bool $isSuccess): void
    {
        $useCase = Mockery::mock(RiskWordUseCase::class);
        if ($isSuccess === true) {
            $responseModel = new RiskCommentList($statusCode, collect($response), $errorMessage);
            $useCase->shouldReceive(
                [
                    'getRiskComment' => $responseModel,
                ]

            )->once();
        } else {
            Log::shouldReceive('error');
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, 500);
            $exception = new OuterErrorException($ed, $response);
            $useCase->shouldReceive('getRiskComment')->andThrow($exception);
        }

        $invoker = new RiskWordInvoker($useCase);
        $actual = $invoker->invokeGetRiskComments('title', 'language');

        if ($isSuccess === true) {
            if ($errorMessage === null) {
                $expected = OuterApiResponseAdapter::getFromCollection(collect($response), $statusCode);
            } else {
                $expected = OuterApiResponseAdapter::getFromArray([$errorMessage], $statusCode);
            }
        } else {
            $expected = OuterApiResponseAdapter::getFromOuterErrorException($exception);
        }

        $this->assertEquals($expected, $actual);
    }

    public function invokeNotifyRiskCommentListDataProvider(): array
    {
        return [
            'has error case' => [
                'response' => [],
                'errorMessage' => 'error message',
                'statusCode' => 404,
                'isSuccess' => true,
            ],
            'success case' => [
                'response' => [
                    [
                        'id' => 'id-xx1',
                        'text' => 'xxxx'
                    ]
                ],
                'errorMessage' => null,
                'statusCode' => 200,
                'isSuccess' => true,
            ],
            'error case' => [
                'response' => 'error test',
                'errorMessage' => null,
                'statusCode' => 500,
                'isSuccess' => false,
            ]
        ];
    }
}
