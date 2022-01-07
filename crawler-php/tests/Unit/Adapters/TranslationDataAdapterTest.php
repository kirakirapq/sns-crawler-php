<?php

namespace Unit\Adapters;

use App\Adapters\TranslationDataAdapter;
use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\InputData\Translation\TranslationRequestDataWithGAS;
use App\Entities\Translation\TranslationDataList;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class TranslationDataAdapterTest extends TestCase
{
    /**
     * getTranslationDataList
     * @test
     *
     * @return void
     */
    public function getTranslationDataList(): void
    {
        $apiCollection = collect([]);
        $translated    = collect([]);
        $actual = TranslationDataAdapter::getTranslationDataList($apiCollection, $translated);

        $this->assertInstanceOf(TranslationDataList::class, $actual);
    }

    /**
     * getTranslationRequestData
     * @test
     * @dataProvider getTranslationRequestDataProvider
     *
     * @param  mixed $version
     * @return void
     */
    public function getTranslationRequestData($version): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn('', '')
            ->times(2);

        $request = [
            'contents' => ['', ''],
            'language' => [
                'from' => 'en',
                'to' => 'ja'
            ]
        ];

        $actual = TranslationDataAdapter::getTranslationRequestData($request, $version);

        $this->assertInstanceOf(TranslationRequestData::class, $actual);
    }

    public function getTranslationRequestDataProvider(): array
    {
        return [
            'version 2' => ['version' => 'V2'],
            'version 3' => ['version' => 'V3']
        ];
    }

    /**
     * getTranslationRequestDataExeptionCase
     * @test
     * @dataProvider getTranslationRequestDataExeptionCaseProvider
     *
     * @param  array $request
     * @param  mixed $version
     * @return void
     */
    public function getTranslationRequestDataExeptionCase($request): void
    {
        Log::shouldReceive('error');
        $this->expectException(OuterErrorException::class);
        $this->expectExceptionCode(500);
        TranslationDataAdapter::getTranslationRequestData($request, 'V3');
    }

    public function getTranslationRequestDataExeptionCaseProvider(): array
    {
        return [
            'contents is undefined case' => [
                'request' => [
                    'language' => [
                        'from' => 'en',
                        'to' => 'ja'
                    ],
                ],
            ],
            'contents is is null case' => [
                'request' => [
                    'contents' => null,
                    'language' => [
                        'from' => 'en',
                        'to' => 'ja'
                    ],
                ],
            ],
            'contents is is not array case' => [
                'request' => [
                    'contents' => 'string',
                    'language' => [
                        'from' => 'en',
                        'to' => 'ja'
                    ],
                ],
            ],
            'contents is is empty case' => [
                'request' => [
                    'contents' => [],
                    'language' => [
                        'from' => 'en',
                        'to' => 'ja'
                    ],
                ],
            ],
            'language.from is undefined case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'to' => 'ja'
                    ],
                ],
            ],
            'language.from is is null case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'from' => null,
                        'to' => 'ja'
                    ],
                ],
            ],
            'language.from is is not string case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'from' => ['en'],
                        'to' => 'ja'
                    ],
                ],
            ],
            'language.from is is empty case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'from' => '',
                        'to' => 'ja'
                    ],
                ],
            ],

            'language.to is undefined case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'from' => 'ja'
                    ],
                ],
            ],
            'language.to is is null case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'from' => 'en',
                        'to' => null
                    ],
                ],
            ],
            'language.to is is not string case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'from' => 'en',
                        'to' => ['ja']
                    ],
                ],
            ],
            'language.from is is empty case' => [
                'request' => [
                    'contents' => ['string'],
                    'language' => [
                        'from' => 'en',
                        'to' => ''
                    ],
                ],
            ],
        ];
    }

    /**
     * getTranslationRequestDataWithGAS
     * @test
     *
     * @return void
     */
    public function getTranslationRequestDataWithGAS(): void
    {
        $actual = TranslationDataAdapter::getTranslationRequestDataWithGAS('this is apple.',  'en', 'ja');

        $this->assertInstanceOf(TranslationRequestDataWithGAS::class, $actual);
    }
}
