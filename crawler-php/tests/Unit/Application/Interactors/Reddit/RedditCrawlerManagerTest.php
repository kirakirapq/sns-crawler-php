<?php

namespace Unit\Application\Interactors\Reddit;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\Interactors\Reddit\RedditCrawlerManager;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\UseCases\Reddit\RedditApiUseCase;
use App\Entities\Notification\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;
use App\Entities\Translation\TranslationDataList;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

/**
 * RedditCrawlerManagerTest
 */
class RedditCrawlerManagerTest extends TestCase
{
    /**
     * invokeCrawling
     * @test
     * @dataProvider invokeCrawlingDataProvider
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param  mixed $count
     * @param  mixed $expected
     * @return void
     */
    public function invokeCrawling(bool $isError, ?array $threadListArray, array $commentListArray, RiskCommentList $riskCommentList, ?OuterApiResponse $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('set')
            ->shouldReceive(['get' => '']);

        Log::shouldReceive('info');
        $e = null;
        $threadList = null;
        if (is_null($threadListArray) === false) {
            $threadList = collect($threadListArray);
        }
        $commentList = collect($commentListArray);

        $redditApiUseCase = Mockery::mock(RedditApiUseCase::class);

        if ($isError === true) {
            Log::shouldReceive('error');
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, 500);
            $e = new OuterErrorException($ed, 'error test');
            $redditApiUseCase->shouldReceive('getLatestData')->andThrow($e);
        } else {
            $redditApiUseCase->shouldReceive('getLatestData')->andReturn(null);
            $redditApiUseCase->shouldReceive('getThreadList')
                ->andReturn($threadList);
            $redditApiUseCase->shouldReceive('getCommentList')
                ->andReturn($commentList);
        }

        $translationUseCase = Mockery::mock(TranslationUseCase::class);
        $csvUseCase = Mockery::mock(CsvUseCase::class);
        $riskWordUseCase = Mockery::mock(RiskWordUseCase::class);

        if (count($commentListArray) === 0) {
            $translationUseCase->shouldReceive('translationlist')->never();
            $csvUseCase->shouldReceive([
                'loadCsv' => 'filename',
                'deleteFile' => 'filename',
            ])->never();
            $riskWordUseCase->shouldReceive(
                [
                    'loadRiskComment' => '',
                    'getRiskComment' => $riskCommentList
                ]
            )->never();
        } else {
            $translationUseCase->shouldReceive('translationlist')->andReturn(
                TranslationDataList::getInstance(collect([]), collect([]))
            )->once();

            $csvUseCase->shouldReceive([
                'loadCsv' => 'filename',
                'deleteFile' => 'filename',
            ])->once();

            $riskWordUseCase->shouldReceive(
                [
                    'loadRiskComment' => '',
                    'getRiskComment' => $riskCommentList
                ]
            )->once();
        }


        $notificationUseCase = Mockery::mock(NotificationUseCase::class);
        if ($riskCommentList->getCommentList()->count() === 0) {
            $notificationUseCase->shouldReceive('notifyRiskCommentList')->never();
        } else {
            $notificationUseCase->shouldReceive(
                ['notifyRiskCommentList' => new NotificationResponseModel([])]
            )->once();
        }

        $bigQueryUseCase = Mockery::mock(BigQueryUseCase::class);
        $bigQueryUseCase->shouldReceive('loadBigQuery');


        $manager = new RedditCrawlerManager(
            $bigQueryUseCase,
            $redditApiUseCase,
            $translationUseCase,
            $csvUseCase,
            $riskWordUseCase,
            $notificationUseCase
        );
        $actual  = $manager->invokeCrawling('reddit', 'kms', 'en');

        if ($isError === true) {
            $this->assertEquals(OuterApiResponseAdapter::getFromOuterErrorException($e), $actual);
        } else {
            $this->assertEquals($expected, $actual);
        }
    }

    public function invokeCrawlingDataProvider(): array
    {
        $commentData = [
            [
                'id' => '',
                'text' => '',
                'translated' => ''
            ],
            [
                'id' => '',
                'text' => '',
                'translated' => ''
            ]
        ];

        return [
            'eeror case' => [
                'isError' => true,
                'threadListArray' => null,
                'commentListArray' => [],
                'riskCommentList' => (new RiskCommentList(200, collect([]))),
                'expected' => OuterApiResponseAdapter::getFromArray(
                    [
                        'thread list is not found.'
                    ],
                    200
                ),
            ],
            'threadList is null case' => [
                'isError' => false,
                'threadListArray' => null,
                'commentListArray' => [],
                'riskCommentList' => (new RiskCommentList(200, collect([]))),
                'expected' => OuterApiResponseAdapter::getFromArray(
                    [
                        'thread list is not found.'
                    ],
                    200
                ),
            ],
            'commentList count 0 case' => [
                'isError' => false,
                'threadListArray' => [
                    [
                        'text' => '',
                        'id' => '',
                    ]
                ],
                'commentListArray' => [],
                'riskCommentList' => (new RiskCommentList(200, collect([]))),
                'expected' => OuterApiResponseAdapter::getFromArray(
                    [
                        'new comment list is not found.'
                    ],
                    200
                ),
            ],
            'RiskCommentList count 0 case' => [
                'isError' => false,
                'threadListArray' => [
                    [
                        'text' => '',
                        'id' => '',
                    ]
                ],
                'commentListArray' => $commentData,
                'riskCommentList' => (new RiskCommentList(200, collect([[]]))),
                'expected'  => OuterApiResponseAdapter::getFromArray(
                    [
                        'RedditCommentDataList' => [],
                        'RiskCommentList' => [],
                    ],
                    200
                ),
            ],
            'normal case' => [
                'isError' => false,
                'threadListArray' => $commentData,
                'commentListArray' => $commentData,
                'riskCommentList' => (new RiskCommentList(200, collect($commentData))),
                'expected'  => OuterApiResponseAdapter::getFromArray(
                    [
                        'RedditCommentDataList' => [],
                        'RiskCommentList' => $commentData,
                    ],
                    200
                ),
            ],
        ];
    }
}
