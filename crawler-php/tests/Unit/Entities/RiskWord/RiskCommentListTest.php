<?php

namespace Unit\Entities\Reddit;

use App\Entities\RiskWord\RiskCommentList;
use App\Application\InputData\TargetDate;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Tests\TestCase;

class RiskCommentListTest extends TestCase
{
    /**
     * getStatusCode
     * @test
     *
     * @return void
     */
    public function getStatusCode(): void
    {
        $expected = $code = 200;
        $model = new RiskCommentList($code, collect([]));

        $this->assertEquals($expected, $model->getStatusCode());
    }

    /**
     * hasError
     * @test
     * @dataProvider hasErrorDataProvider
     *
     * @return void
     */
    public function hasError(?string $errorMessage, bool $expected): void
    {
        $code = 200;
        $model = new RiskCommentList($code, collect([]), $errorMessage);

        $this->assertEquals($expected, $model->hasError());
    }

    public function hasErrorDataProvider(): array
    {
        return [
            'error message is null case' => [
                'error message' => null,
                'expected' => false,
            ],
            'error message is empty case' => [
                'error message' => '',
                'expected' => false,
            ],
            'error message is not empty case' => [
                'error message' => 'error.',
                'expected' => true,
            ]
        ];
    }

    /**
     * getErrorMessage
     * @test
     * @dataProvider getErrorMessageProvider
     *
     * @return void
     */
    public function getErrorMessage(?string $errorMessage, ?string $expected): void
    {
        $code = 200;
        $model = new RiskCommentList($code, collect([]), $errorMessage);

        $this->assertEquals($expected, $model->getErrorMessage());
    }

    public function getErrorMessageProvider(): array
    {
        return [
            'error message is null case' => [
                'error message' => null,
                'expected' => null,
            ],
            'error message is empty case' => [
                'error message' => '',
                'expected' => '',
            ],
            'error message is not empty case' => [
                'error message' => 'error.',
                'expected' => 'error.',
            ]
        ];
    }

    /**
     * getCommentList
     * @test
     * @dataProvider getCommentListDataProvider
     *
     * @return void
     */
    public function getCommentList(?Collection $commentList, ?Collection $expected): void
    {
        $code = 200;
        $model = new RiskCommentList($code, $commentList);

        $this->assertEquals($expected, $model->getCommentList());
    }

    public function getCommentListDataProvider(): array
    {
        $created = '2021-10-01';
        $carbon = Carbon::createFromFormat('Y-m-d', $created);

        $utcDate = (new Carbon($carbon, 'UTC'))->format('Y-m-d');
        $tokyoDate = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d');

        $utcCreatedAt = (new Carbon($carbon, 'UTC'))->format('Y-m-d H:i:s');

        return [
            'commentList is empty case' => [
                'commentList' => collect([]),
                'expected' => collect(
                    [],
                )
            ],
            'date is not empty case' => [
                'commentList' => collect(
                    [
                        [
                            'id' => 'id123',
                            'date' => $utcDate,
                        ]
                    ]
                ),
                'expected' => collect(
                    [
                        [
                            'id' => 'id123',
                            'app_name' => '',
                            'language' => '',
                            'text' => '',
                            'translated' => '',
                            'date' => $tokyoDate,
                            'created_at' => '',
                        ]
                    ],
                )
            ],
            'created_at is not empty case' => [
                'commentList' => collect(
                    [
                        [
                            'id' => 'id123',
                            'created_at' => $utcCreatedAt,
                        ]

                    ]
                ),
                'expected' => collect(
                    [
                        [
                            'id' => 'id123',
                            'app_name' => '',
                            'language' => '',
                            'text' => '',
                            'translated' => '',
                            'date' => '',
                            'created_at' => $utcCreatedAt,
                        ]
                    ],
                )
            ],
        ];
    }
}
