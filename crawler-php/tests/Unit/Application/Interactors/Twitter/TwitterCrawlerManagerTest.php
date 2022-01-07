<?php

namespace Unit\Application\Interactors\Twitter;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\Interactors\Twitter\TwitterCrawlerManager;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\UseCases\Twitter\TwitterApiUseCase;
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
 * TwitterCrawlerManagerTest
 */
class TwitterCrawlerManagerTest extends TestCase
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
    public function invokeCrawling(bool $hasError, int $count,  ?OuterApiResponse $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('set')
            ->shouldReceive(['get' => '']);

        Log::shouldReceive('info');

        $collect = collect([]);
        for ($i = 0; $i < $count; $i++) {
            $collect->push([
                'created_at' => '',
                'text' => 'user_id',
            ]);
        }

        $twitterApiUseCase = Mockery::mock(TwitterApiUseCase::class);

        if ($hasError === true) {
            Log::shouldReceive('error');
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, 500);
            $exception = new OuterErrorException($ed, '');
            $twitterApiUseCase->shouldReceive('getLatestData')->andThrow($exception);
            $expected = OuterApiResponseAdapter::getFromOuterErrorException($exception);
        } else {
            $twitterApiUseCase->shouldReceive('getLatestData')->andReturn(null);
            $twitterApiUseCase->shouldReceive('getTwitterMentionList')
                ->andReturn($collect);
        }

        $translationUseCase = Mockery::mock(TranslationUseCase::class);
        $translationUseCase->shouldReceive('translationlist')->andReturn(
            TranslationDataList::getInstance(collect([]), collect([]))
        );

        $csvUseCase = Mockery::mock(CsvUseCase::class);
        $csvUseCase->shouldReceive([
            'loadCsv' => 'filename',
            'deleteFile' => 'filename',
        ]);

        $bigQueryUseCase = Mockery::mock(BigQueryUseCase::class);
        $bigQueryUseCase->shouldReceive('loadBigQuery');

        $riskWordUseCase = Mockery::mock(RiskWordUseCase::class);
        $riskWordUseCase->shouldReceive('loadRiskComment');
        $riskWordUseCase->shouldReceive('getRiskComment')
            ->andReturn(
                new RiskCommentList(200, collect([]))
            );

        $notificationUseCase = Mockery::mock(NotificationUseCase::class);
        $notificationUseCase->shouldReceive('notifyRiskCommentList');

        $manager = new TwitterCrawlerManager(
            $bigQueryUseCase,
            $twitterApiUseCase,
            $translationUseCase,
            $csvUseCase,
            $riskWordUseCase,
            $notificationUseCase
        );

        $actual = $manager->invokeCrawling('twitter', 'kms', 'en');

        $this->assertEquals($expected, $actual);
    }

    public function invokeCrawlingDataProvider(): array
    {
        return [
            'mentionDataList count 0 case' => [
                'hasError' => false,
                'count' => 0,
                'expected' => OuterApiResponseAdapter::getFromArray(
                    [
                        'new mention list is not found.'
                    ],
                    200
                ),
            ],
            'mentionDataList count not 0 case' => [
                'hasError' => false,
                'count' => 10,
                'expected' => OuterApiResponseAdapter::getFromArray(
                    [
                        'TwitterCommentDataList' => [],
                        'RiskCommentList' => [],
                    ],
                    200
                ),
            ],
            'hasError true case' => [
                'hasError' => true,
                'count' => 10,
                'expected' => null,
            ],
        ];
    }
}
