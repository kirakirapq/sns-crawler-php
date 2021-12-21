<?php

namespace Unit\Application\Interactors\Reddit;

use App\Adapters\TranslationRequestDataApiAdapter;
use App\Application\InputData\TranslationRequestData;
use App\Application\Interactors\Reddit\RedditCrawlerManager;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\UseCases\Reddit\RedditApiUseCase;
use App\Entities\RiskWord\RiskCommentList;
use App\Entities\Translation\TranslationDataList;
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
     *
     * @param  mixed $count
     * @param  mixed $expected
     * @return void
     */
    public function invokeCrawling(?array $threadListArray, array $commentListArray, RiskCommentList $riskCommentList, ?array $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('set')
            ->shouldReceive(['get' => '']);

        Log::shouldReceive('info');

        $threadList = null;
        if (is_null($threadListArray) === false) {
            $threadList = collect($threadListArray);
        }
        $commentList = collect($commentListArray);

        $redditApiUseCase = Mockery::mock(RedditApiUseCase::class);
        $redditApiUseCase->shouldReceive('getLatestData')->andReturn(null);
        $redditApiUseCase->shouldReceive('getThreadList')
            ->andReturn($threadList);
        $redditApiUseCase->shouldReceive('getCommentList')
            ->andReturn($commentList);

        $adapter = Mockery::mock('alias:' . TranslationRequestDataApiAdapter::class);
        $translationUseCase = Mockery::mock(TranslationUseCase::class);
        $csvUseCase = Mockery::mock(CsvUseCase::class);
        $riskWordUseCase = Mockery::mock(RiskWordUseCase::class);

        if (count($commentListArray) === 0) {
            $adapter->shouldReceive('getTranslationRequestData')->never();
            $translationUseCase->shouldReceive('translationlist')->never();
            $csvUseCase->shouldReceive([
                'loadCsv' => 'filename',
                'deleteFile' => 'filename',
            ])->never();
        } else {
            $response = new TranslationRequestData('text', 'en', 'ja');
            $adapter->shouldReceive('getTranslationRequestData')
                ->andReturn($response)
                ->once();

            $translationUseCase->shouldReceive('translationlist')->andReturn(
                new TranslationDataList(collect([]), collect([]))
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

            $notificationUseCase = Mockery::mock(NotificationUseCase::class);
            if ($riskCommentList->getCommentList()->count() === 0) {
                $notificationUseCase->shouldReceive('notifyRiskCommentList')->never();
            } else {
                $notificationUseCase->shouldReceive('notifyRiskCommentList')->once();
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
            'threadList is null case' => [
                'threadListArray' => null,
                'commentListArray' => [],
                'riskCommentList' => (new RiskCommentList(200, collect([]))),
                'expected' => null,
            ],
            'commentList count 0 case' => [
                'threadListArray' => [
                    [
                        'text' => '',
                        'id' => '',
                    ]
                ],
                'commentListArray' => [],
                'riskCommentList' => (new RiskCommentList(200, collect([]))),
                'expected' => null,
            ],
            'RiskCommentList count 0 case' => [
                'threadListArray' => [
                    [
                        'text' => '',
                        'id' => '',
                    ]
                ],
                'commentListArray' => [
                    [
                        'text' => '',
                        'id' => '',
                    ]
                ],
                'riskCommentList' => (new RiskCommentList(200, collect([[]]))),
                'expected' => ['resultCount' => count([
                    [
                        'text' => '',
                        'id' => '',
                    ]
                ])],
            ],
            'normal case' => [
                'threadListArray' => $commentData,
                'commentListArray' => $commentData,
                'riskCommentList' => (new RiskCommentList(200, collect($commentData))),
                'expected' => ['resultCount' => count($commentData)],
            ],
        ];
    }
}
