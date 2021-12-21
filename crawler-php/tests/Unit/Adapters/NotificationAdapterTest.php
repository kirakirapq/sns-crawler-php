<?php

namespace Unit\Adapters;

use App\Adapters\NotificationAdapter;
use App\Application\InputData\NotificationSendModel;
use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use App\Application\OutputData\InnerApiResponse\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;
use Tests\TestCase;
use \Mockery;

class NotificationAdapterTest extends TestCase
{
    /**
     * convertRiskCommentToSlackNotification
     * @test
     *
     * @return void
     */
    public function convertRiskCommentToSlackNotification(): void
    {
        $list[] = [
            'app_name' => '',
            'language' => '',
        ];
        $riskCommentList = new RiskCommentList(200, collect($list));
        $actual = NotificationAdapter::convertRiskCommentToSlackNotification('twitter', $riskCommentList);

        $this->assertInstanceOf(NotificationSendModel::class, $actual);
    }

    /**
     * getSlackNotificationSendModel
     * @test
     *
     * @return void
     */
    public function getSlackNotificationSendModel(): void
    {
        $messages = [
            [
                'app_name' => '',
                'language' => '',
                'text' => '',
                'translated' => '',
                'created_at' => '',
            ]
        ];

        $actual = NotificationAdapter::getSlackNotificationSendModel('twitter', 'kms', 'en', $messages);

        $this->assertInstanceOf(NotificationSendModel::class, $actual);
    }

    /**
     * getNotificationResponseModel
     * @test
     *
     * @return void
     */
    public function getNotificationResponseModel(): void
    {
        $apiResponse = Mockery::mock(BigQueryResponse::class);
        $apiResponse->shouldReceive('getStatusCode')->andReturn(200)->once();
        $apiResponse->shouldReceive('getBodyAsArray')->andReturn([])->once();

        $actual = NotificationAdapter::getNotificationResponseModel($apiResponse);

        $this->assertInstanceOf(NotificationResponseModel::class, $actual);
    }
}
