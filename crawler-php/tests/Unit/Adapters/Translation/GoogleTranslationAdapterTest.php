<?php

namespace Unit\Adapters\Translation;


use App\Adapters\Translation\GoogleTranslationAdapter;
use App\Adapters\TranslationDataAdapter;
use App\Application\InputData\Translation\BCP47;
use App\Application\InputData\Translation\BCP47Enum;
use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\InputData\Translation\TranslationRequestDataWithGAS;
use App\Entities\Translation\TranslationData;
use App\Entities\Translation\TranslationDataList;
use App\Entities\Translation\GoogleTlanslationResponseData;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Config;
use App\Exceptions\ObjectDefinitionErrorException;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Translate\V3\TranslateTextResponse;
use Tests\TestCase;
use \Mockery;
use stdClass;

class GoogleTranslationAdapterTest extends TestCase
{
    /**
     * getTranslationDataList
     * @test
     * @dataProvider getBCP47DataProvider
     *
     * @return void
     */
    public function getBCP47(string $isoCode, ?BCP47 $expected): void
    {
        if (is_null($expected) === true) {
            Log::shouldReceive('error');
            $this->expectException(ObjectDefinitionErrorException::class);
            $this->expectExceptionCode(500);
        }

        $actual = GoogleTranslationAdapter::getBCP47($isoCode);

        if (is_null($expected) === false) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function getBCP47DataProvider(): array
    {
        return [
            'ja case' => [
                'isoCode' => 'ja',
                'expected' => new BCP47('ja'),
            ],
            'jpn case' => [
                'isoCode' => 'jpn',
                'expected' => new BCP47('jpn'),
            ],
            'ko case' => [
                'isoCode' => 'ko',
                'expected' => new BCP47('ko'),
            ],
            'kor case' => [
                'isoCode' => 'kor',
                'expected' => new BCP47('kor'),
            ],
            'en case' => [
                'isoCode' => 'en',
                'expected' => new BCP47('en'),
            ],
            'eng case' => [
                'isoCode' => 'eng',
                'expected' => new BCP47('eng'),
            ],
            'other case' => [
                'isoCode' => 'ch',
                'expected' => null,
            ],
        ];
    }

    /**
     * getTranlationV3OptionalArray
     * @test
     *
     * @return void
     */
    public function getTranlationV3OptionalArray(): void
    {
        $projectId = 'prj';
        $location = 'location';
        $bcp47 = new BCP47('en');

        $actual = GoogleTranslationAdapter::getTranlationV3OptionalArray($projectId, $location, $bcp47);

        $model = sprintf('projects/%s/locations/%s/models/general/nmt', $projectId, $location);
        $expected = [
            'mimeType' => 'text/plain',
            'sourceLanguageCode' => $bcp47->getCode(),
            'model' => $model,
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * getTranlationDataFromArray
     * @test
     * @dataProvider getTranlationDataFromArrayDataProvider
     *
     * @param  mixed $data
     * @param  mixed $expected
     * @return void
     */
    public function getTranlationDataFromArray(array $data, TranslationData $expected): void
    {
        $actual = GoogleTranslationAdapter::getTranlationDataFromArray($data);

        $this->assertEquals($expected, $actual);
    }

    public function getTranlationDataFromArrayDataProvider(): array
    {
        return [
            'value is array case' => [
                'data' => [
                    [
                        'code' => 200,
                        'text' => '文'
                    ]
                ],
                'expected' => new TranslationData(
                    200,
                    collect([
                        [
                            [
                                'code' => 200,
                                'text' => '文'
                            ]
                        ]
                    ])
                )
            ],
            'value is not array case' => [
                'data' => [
                    'code' => 200,
                    'text' => '文'
                ],
                'expected' => new TranslationData(
                    200,
                    collect([
                        [
                            'code' => 200,
                            'text' => '文'
                        ]
                    ])
                )
            ],
        ];
    }

    /**
     * getTranlationDataFromV3
     * @test
     *
     * @return void
     */
    public function getTranlationDataFromV3(): void
    {
        $value = new class
        {
            public function getTranslatedText()
            {
                return '';
            }
        };
        $values = [$value];

        $response = Mockery::mock(TranslateTextResponse::class)
            ->shouldReceive('getTranslations')
            ->andReturn($values)
            ->getMock();

        $actual = GoogleTranslationAdapter::getTranlationDataFromV3($response);

        $expected = new TranslationData(200, collect([['text' => '']]));

        $this->assertEquals($expected, $actual);
    }

    /**
     * getTranlationDataListFromArray
     * @test
     *
     * @return void
     */
    public function getTranlationDataListFromArray(): void
    {
        $api = [
            ['text' => '123'],
            ['text' => '234'],
            ['text' => '345'],
        ];
        $translated = [
            ['text' => 'abc'],
            ['text' => 'bcd'],
            ['text' => 'def'],
        ];
        $apiCollection = collect($api);
        $actual = GoogleTranslationAdapter::getTranlationDataListFromArray($apiCollection, $translated);
        $expected = TranslationDataAdapter::getTranslationDataList($apiCollection, collect($translated));

        $this->assertEquals($expected, $actual);
    }

    /**
     * getTranslationResponse
     * @test
     * @dataProvider getTranslationResponseDataProvider
     *
     * @param  mixed $translateTextResponse
     * @return void
     */
    public function getTranslationResponse($translateTextResponse): void
    {
        $actual = GoogleTranslationAdapter::getTranslationResponse($translateTextResponse);
        $expected = GoogleTlanslationResponseData::getInstance($translateTextResponse);

        $this->assertEquals($expected, $actual);
    }

    public function getTranslationResponseDataProvider(): array
    {
        $value = new class
        {
            public function getTranslatedText()
            {
                return '';
            }
        };
        $values = [$value];

        $response = Mockery::mock(TranslateTextResponse::class)
            ->shouldReceive('getTranslations')
            ->andReturn($values)
            ->getMock();

        return [
            'v2 case' => [
                'translateTextResponse' => ['1', '2', '3'],
            ],
            'v3 case' =>
            [
                'translateTextResponse' => $response,
            ],
        ];
    }
}
