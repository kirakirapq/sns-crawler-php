<?php

namespace Unit\Interractoers\Translation;

use App\Adapters\TranslationDataAdapter;
use App\Adapters\OuterApiResponseAdapter;
use App\Application\Interactors\Translation\GoogleTranslationInvoker;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Entities\Translation\TranslationData;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class TranslationnvokerTest extends TestCase
{
    /**
     * invokeNotifyRiskCommentList
     * @test
     * @dataProvider invokeNotifyRiskCommentListDataProvider
     *
     * @return void
     */
    public function invokeTranslation(string|array $response, int $statusCode, bool $isSuccess): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn('');

        $reqests = [
            'contents' => [
                'xxxx',
                'yyyy'
            ],
            'language' => [
                'from' => 'ja',
                'to' => 'en'
            ]
        ];
        $requestData = TranslationDataAdapter::getTranslationRequestData($reqests);

        $useCase = Mockery::mock(TranslationUseCase::class);
        if ($isSuccess === true) {
            $translated = new TranslationData($statusCode, collect($response));
            $useCase->shouldReceive(
                [
                    'translation' => $translated,
                ]

            )->once();
        } else {
            Log::shouldReceive('error');
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, 500);
            $exception = new OuterErrorException($ed, $response);
            $useCase->shouldReceive('translation')->andThrow($exception);
        }

        $invoker = new GoogleTranslationInvoker($useCase);
        $actual = $invoker->invokeTranslation($requestData);

        if ($isSuccess === true) {
            $response = [
                'contents' => $requestData->getTextData(),
                'translated' => $translated->getText(),
                'language' => [
                    'translation from' => $requestData->getSourceLanguageCode(),
                    'translation to' => $requestData->getTargetLanguageCode(),
                ],
            ];

            $expected = OuterApiResponseAdapter::getFromArray($response, $statusCode);
        } else {
            $expected = OuterApiResponseAdapter::getFromOuterErrorException($exception);
        }

        $this->assertEquals($expected, $actual);
    }

    public function invokeNotifyRiskCommentListDataProvider(): array
    {
        return [
            'success case' => [
                'response' => [
                    [
                        'id' => 'id-xx1',
                        'text' => 'xxxx'
                    ]
                ],
                'statusCode' => 200,
                'isSuccess' => true,
            ],
            'error case' => [
                'response' => 'error test',
                'statusCode' => 500,
                'isSuccess' => false,
            ]
        ];
    }
}
