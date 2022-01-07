<?php

namespace Unit\Entities\Translation;

use App\Entities\Translation\TranslationDataList;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * TranslationDataListTest
 * @runTestsInSeparateProcesses
 */
class TranslationDataListTest extends TestCase
{

    /**
     * translationData
     * @test
     * @dataProvider getInstanceProvider
     * @preserveGlobalState disabled
     *
     * @return void
     */
    public function translationData(bool $instanceIsNull, ?Collection $apiDataFirst, Collection $apiData, Collection $translationData, Collection $expected): void
    {
        if ($instanceIsNull === true) {
            $actual = TranslationDataList::getInstance($apiData, $translationData);
        } else {
            TranslationDataList::getInstance($apiDataFirst, collect([]));
            $actual = TranslationDataList::getInstance($apiData, $translationData);
        }

        $this->assertEquals($expected, $actual->translationData());
    }

    public function getInstanceProvider(): array
    {
        return [
            'instance is null case' => [
                'instanceIsNull' => true,
                'apiDataFirst' => null,
                'apiData' => collect([
                    [
                        'id' => 'xxx-1',
                        'text' => 'text-1'
                    ],
                    [
                        'id' => 'xxx-2',
                        'text' => 'text-2'
                    ]
                ]),
                'translationData' => collect([
                    [
                        'id' => 'xxx-1',
                        'text' => 'translated-1'
                    ],
                    [
                        'id' => 'xxx-2',
                        'text' => 'translated-2'
                    ]
                ]),
                'expected' => collect(
                    [
                        [
                            'id' => 'xxx-1',
                            'text' => 'text-1',
                            'translated' => 'translated-1'
                        ],
                        [
                            'id' => 'xxx-2',
                            'text' => 'text-2',
                            'translated' => 'translated-2'
                        ]
                    ]
                )
            ],
            'instance is null and include not exists key case' => [
                'instanceIsNull' => true,
                'apiDataFirst' => null,
                'apiData' => collect([
                    [
                        'id' => 'xxx-1',
                        'text' => 'text-1'
                    ],
                    [
                        'id' => 'xxx-2',
                        'text' => 'text-2'
                    ]
                ]),
                'translationData' => collect([
                    [
                        'id' => 'xxx-1',
                        'text' => 'translated-1'
                    ],
                    [
                        'id' => 'xxx-2',
                        'text' => 'translated-2'
                    ],
                    [
                        'id' => 'xxx-2',
                        'text' => 'translated-2'
                    ]
                ]),
                'expected' => collect(
                    [
                        [
                            'id' => 'xxx-1',
                            'text' => 'text-1',
                            'translated' => 'translated-1'
                        ],
                        [
                            'id' => 'xxx-2',
                            'text' => 'text-2',
                            'translated' => 'translated-2'
                        ]
                    ]
                )
            ],

            'instance is not null and include not exists key case' => [
                'instanceIsNull' => false,
                'apiDataFirst' => collect(
                    [
                        [
                            'id' => 'xxx-1',
                            'text' => 'text-1',
                            'translated' => 'translated-1'
                        ],
                        [
                            'id' => 'xxx-2',
                            'text' => 'text-2',
                            'translated' => 'translated-2'
                        ]
                    ]
                ),
                'apiData' => collect([
                    [
                        'id' => 'xxx-3',
                        'text' => 'text-3'
                    ],
                    [
                        'id' => 'xxx-4',
                        'text' => 'text-4'
                    ]
                ]),
                'translationData' => collect([
                    [
                        'id' => 'xxx-3',
                        'text' => 'translated-3'
                    ],
                    [
                        'id' => 'xxx-4',
                        'text' => 'translated-4'
                    ],
                    [
                        'id' => 'xxx-5',
                        'text' => 'translated-5'
                    ]
                ]),
                'expected' => collect(
                    [
                        [
                            'id' => 'xxx-1',
                            'text' => 'text-1',
                            'translated' => 'translated-1'
                        ],
                        [
                            'id' => 'xxx-2',
                            'text' => 'text-2',
                            'translated' => 'translated-2'
                        ],
                        [
                            'id' => 'xxx-3',
                            'text' => 'text-3',
                            'translated' => 'translated-3'
                        ],
                        [
                            'id' => 'xxx-4',
                            'text' => 'text-4',
                            'translated' => 'translated-4'
                        ]
                    ]
                )
            ]
        ];
    }
}
