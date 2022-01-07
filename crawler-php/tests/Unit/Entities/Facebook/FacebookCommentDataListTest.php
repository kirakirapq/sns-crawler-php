<?php

namespace Unit\Entities\BigQuery;

use App\Entities\Facebook\FacebookCommentDataList;
use App\Entities\Facebook\FacebookDataList;
use App\Exceptions\ObjectDefinitionErrorException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * FacebookCommentDataListTest
 * @runTestsInSeparateProcesses
 */
class FacebookCommentDataListTest extends TestCase
{
    /**
     * getDataList
     * @test
     * @dataProvider getInstanceProvider
     * @preserveGlobalState disabled
     *
     * @param  mixed $instanceIsNull
     * @param  mixed $instanceData
     * @param  mixed $newData
     * @param  mixed $expected
     * @return void
     */
    public function getDataList(bool $instanceIsNull, ?array $instanceData, array $newData, $expected): void
    {
        if ($instanceIsNull === true) {
            $actual = FacebookCommentDataList::getInstance($newData);
        } else {
            FacebookCommentDataList::getInstance($instanceData);
            $actual = FacebookCommentDataList::getInstance($newData);
        }

        $this->assertEquals($expected, $actual->getDataList());
    }

    public function getInstanceProvider(): array
    {
        return [
            'instance is null case' => [
                'instanceIsNull' => true,
                'instanceData' => null,
                'newData' => [
                    'data' => [
                        [
                            'created_time' => '2021-01-01 01:00:00'
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00'
                        ]
                    ]
                ],
                'expected' => collect(
                    [
                        [
                            'created_time' => '2021-01-01 01:00:00',
                            'created_at' => '2021-01-01 01:00:00',
                            'date' => '2021-01-01',
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00',
                            'created_at' => '2021-02-01 02:00:00',
                            'date' => '2021-02-01',
                        ]
                    ]
                )
            ],
            'instance is not null case' => [
                'instanceIsNull' => false,
                'instanceData' => [
                    'data' => [
                        [
                            'created_time' => '1990-01-01 01:00:00'
                        ],
                        [
                            'created_time' => '1990-02-01 02:00:00'
                        ]
                    ]
                ],
                'newData' => [
                    'data' => [
                        [
                            'created_time' => '2021-01-01 01:00:00'
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00'
                        ]
                    ]
                ],
                'expected' => collect(
                    [
                        [
                            'created_time' => '1990-01-01 01:00:00',
                            'created_at' => '1990-01-01 01:00:00',
                            'date' => '1990-01-01',
                        ],
                        [
                            'created_time' => '1990-02-01 02:00:00',
                            'created_at' => '1990-02-01 02:00:00',
                            'date' => '1990-02-01',
                        ],
                        [
                            'created_time' => '2021-01-01 01:00:00',
                            'created_at' => '2021-01-01 01:00:00',
                            'date' => '2021-01-01',
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00',
                            'created_at' => '2021-02-01 02:00:00',
                            'date' => '2021-02-01',
                        ]
                    ]
                )

            ]
        ];
    }

    /**
     * getNextPage
     * @test
     * @dataProvider getNextPageProvider
     * @preserveGlobalState disabled
     *
     * @param  mixed $data
     * @param  mixed $expected
     * @return void
     */
    public function getNextPage(array $data, ?string $expected): void
    {
        $actual = FacebookCommentDataList::getInstance($data);
        $this->assertEquals($expected, $actual->getNextPage());
    }

    public function getNextPageProvider(): array
    {
        return [
            'has next page' => [
                'data' => [
                    'data' => [
                        [
                            'created_time' => '2021-01-01 01:00:00'
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00'
                        ]
                    ],
                    'paging' => [
                        'next' => 'next-page-token',
                    ],
                ],
                'expected' => 'next-page-token',
            ],
            'does not have next page' => [
                'data' => [
                    'data' => [
                        [
                            'created_time' => '2021-01-01 01:00:00'
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00'
                        ]
                    ],
                ],
                'expected' => null,
            ],
        ];
    }

    /**
     * hasNextPage
     * @test
     * @dataProvider hasNextPageProvider
     * @preserveGlobalState disabled
     *
     * @param  mixed $data
     * @param  mixed $expected
     * @return void
     */
    public function hasNextPage(array $data, bool $expected): void
    {
        $actual = FacebookCommentDataList::getInstance($data);
        $this->assertEquals($expected, $actual->hasNextPage());
    }

    public function hasNextPageProvider(): array
    {
        return [
            'has next page' => [
                'data' => [
                    'data' => [
                        [
                            'created_time' => '2021-01-01 01:00:00'
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00'
                        ]
                    ],
                    'paging' => [
                        'next' => 'next-page-token',
                    ],
                ],
                'expected' => true,
            ],
            'does not have next page' => [
                'data' => [
                    'data' => [
                        [
                            'created_time' => '2021-01-01 01:00:00'
                        ],
                        [
                            'created_time' => '2021-02-01 02:00:00'
                        ]
                    ],
                ],
                'expected' => false,
            ],
        ];
    }
}
