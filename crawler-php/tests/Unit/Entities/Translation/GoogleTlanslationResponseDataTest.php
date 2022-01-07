<?php

namespace Unit\Entities\Translation;

use App\Entities\Translation\GoogleTlanslationResponseData;
use Google\Cloud\Translate\V3\TranslateTextResponse;
use Tests\TestCase;
use \Mockery;

/**
 * GoogleTlanslationResponseDataTest
 * @runTestsInSeparateProcesses
 */
class GoogleTlanslationResponseDataTest extends TestCase
{

    /**
     * getResponse
     * @test
     * @dataProvider getInstanceProvider
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function getResponse(?array $instance, ?string $transValues, ?array $responses, array $expected): void
    {
        if ($transValues !== null) {
            $values = Mockery::mock(\stdClass::class)
                ->shouldReceive(['getTranslatedText' => $transValues])
                ->getMock();
            $responses = Mockery::mock(TranslateTextResponse::class)
                ->shouldReceive(['getTranslations' => [$values]])
                ->getMock();
        }

        if ($instance === null) {
            $actual = GoogleTlanslationResponseData::getInstance($responses);
        } else {
            GoogleTlanslationResponseData::getInstance($instance);
            $actual = GoogleTlanslationResponseData::getInstance($responses);
        }

        $this->assertEquals($expected, $actual->getResponse());
    }

    public function getInstanceProvider(): array
    {
        $values = [
            new class
            {
                public function getTranslatedText()
                {
                    return 'trans-1';
                }
            },
            new class
            {
                public function getTranslatedText()
                {
                    return 'trans-2';
                }
            },
        ];

        return [
            'array case' => [
                'instance' => null,
                'transValues' => null,
                'responses' => [
                    [
                        'text' => 'text-1'
                    ],
                    [
                        'text' => 'text-2'
                    ]
                ],
                'expected' => [
                    [
                        'text' => 'text-1'
                    ],
                    [
                        'text' => 'text-2'
                    ]
                ],
            ],
            'TranslateTextResponse case' => [
                'instance' => null,
                'transValues' => 'trans-1',
                'responses' => null,
                'expected' => [
                    [
                        'text' => 'trans-1'
                    ],
                ],
            ],
            'instance is not null case' => [
                'instance' => [
                    [
                        'text' => 'text-1'
                    ],
                    [
                        'text' => 'text-2'
                    ]
                ],
                'transValues' => null,
                'responses' => [
                    [
                        'text' => 'text-10'
                    ],
                    [
                        'text' => 'text-20'
                    ]
                ],
                'expected' => [
                    [
                        'text' => 'text-1'
                    ],
                    [
                        'text' => 'text-2'
                    ],
                    [
                        'text' => 'text-10'
                    ],
                    [
                        'text' => 'text-20'
                    ]
                ],
            ]
        ];
    }
}
