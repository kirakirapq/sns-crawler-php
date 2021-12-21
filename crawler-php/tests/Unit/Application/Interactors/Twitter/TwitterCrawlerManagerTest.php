<?php

namespace Unit\Application\Interactors\Twitter;

use App\Adapters\TranslationRequestDataApiAdapter;
use App\Application\InputData\TranslationRequestData;
use App\Application\Interactors\Twitter\TwitterCrawlerManager;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\UseCases\Twitter\TwitterApiUseCase;
use App\Entities\RiskWord\RiskCommentList;
use App\Entities\Translation\TranslationDataList;
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
     *
     * @param  mixed $count
     * @param  mixed $expected
     * @return void
     */
    public function invokeCrawling(int $count, array $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('set')
            ->shouldReceive(['get' => '']);

        Log::shouldReceive('info');

        $response = new TranslationRequestData('text', 'en', 'ja');
        $adapter = Mockery::mock('alias:' . TranslationRequestDataApiAdapter::class);
        $adapter->shouldReceive('getTranslationRequestData')->andReturn($response);

        $collect = collect([]);
        for ($i = 0; $i < $count; $i++) {
            $collect->push([
                'created_at' => '',
                'text' => 'user_id',
            ]);
        }

        $twitterApiUseCase = Mockery::mock(TwitterApiUseCase::class);
        $twitterApiUseCase->shouldReceive('getLatestData')->andReturn(null);
        $twitterApiUseCase->shouldReceive('getTwitterMentionList')
            ->andReturn($collect);

        $translationUseCase = Mockery::mock(TranslationUseCase::class);
        $translationUseCase->shouldReceive('translationlist')->andReturn(
            new TranslationDataList(collect([]), collect([]))
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
                'count' => 0,
                'expected' => ['resultCount' => 0],
            ],
            'mentionDataList count not 0 case' => [
                'count' => 10,
                'expected' => ['resultCount' => 10],
            ],
        ];
    }
}
