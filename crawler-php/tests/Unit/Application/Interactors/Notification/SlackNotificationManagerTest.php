<?php

namespace Unit\Adapters;

use App\Application\Interactors\Notification\SlackNotificationManager;
use App\Application\Repositories\Notification\NotificationClient;
use App\Application\OutputData\InnerApiResponse\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use \Mockery;

class SlackNotificationManagerTest extends TestCase
{
    /**
     * notifyRiskComment
     * @test
     *
     * @return void
     */
    public function notifyRiskComment(): void
    {
        $notification = [
            'sns' => 'tiwtter',
            'title' => 'kms',
            'language' => 'en',
            'messages' => [
                [
                    'text' => '',
                    'translated' => '',
                ]
            ],
        ];

        $responseModel = Mockery::mock(NotificationResponseModel::class);

        $client = Mockery::mock(NotificationClient::class)
            ->shouldReceive(
                [
                    'notify' => $responseModel,
                ]

            )->once()
            ->getMock();

        $manager = new SlackNotificationManager($client);
        $actual = $manager->notifyRiskComment($notification);

        $this->assertEquals($responseModel, $actual);
    }

    /**
     * notifyRiskCommentList
     * @test
     *
     * @return void
     */
    public function notifyRiskCommentList(): void
    {
        $riskComment = new RiskCommentList(
            200,
            collect([
                [
                    'app_name' => 'kms',
                    'language' => 'en',
                    'id' => 'idxxx',
                    'text' => '',
                    'translated' => '',
                ]
            ]),
            'no message'
        );

        $responseModel = Mockery::mock(NotificationResponseModel::class);

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive(['get' => ''])
            ->with('crawl.name')
            ->once();

        $client = Mockery::mock(NotificationClient::class)
            ->shouldReceive(
                [
                    'notify' => $responseModel,
                ]

            )->once()
            ->getMock();

        $manager = new SlackNotificationManager($client);
        $actual = $manager->notifyRiskCommentList($riskComment);
    }
}
