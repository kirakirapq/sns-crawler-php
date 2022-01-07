<?php

namespace Unit\Application\Interactors\Notification;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\Interactors\Notification\SlackNotificationInvoker;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Entities\Notification\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class SlackNotificationInvokerTest extends TestCase
{
    /**
     * invokeNotifyRiskCommentList
     * @test
     * @dataProvider invokeNotifyRiskCommentListDataProvider
     *
     * @return void
     */
    public function invokeNotifyRiskCommentList(string|array $response, int $statusCode, bool $isSuccess): void
    {
        $useCase = Mockery::mock(NotificationUseCase::class);
        if ($isSuccess === true) {
            $responseModel = new NotificationResponseModel($response);
            $useCase->shouldReceive(
                [
                    'notifyRiskCommentList' => $responseModel,
                ]

            )->once();
        } else {
            Log::shouldReceive('error');
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, 500);
            $exception = new OuterErrorException($ed, $response);
            $useCase->shouldReceive('notifyRiskCommentList')->andThrow($exception);
        }

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
        $commentList = collect([]);
        foreach ($notification['messages'] ?? [] as $data) {
            $commentList->push($data);
        }
        $riskCommens = new RiskCommentList(200, $commentList);
        $invoker = new SlackNotificationInvoker($useCase);
        $actual = $invoker->invokeNotifyRiskCommentList($riskCommens);

        if ($isSuccess === true) {
            $outerResponse = [
                'message' => 'notification success',
                'description' => $responseModel->getBody(),
            ];
            $expected = OuterApiResponseAdapter::getFromArray($outerResponse, $statusCode);
        } else {
            $expected = OuterApiResponseAdapter::getFromOuterErrorException($exception);
        }

        $this->assertEquals($expected, $actual);
    }

    public function invokeNotifyRiskCommentListDataProvider(): array
    {
        return [
            'success case' => [
                'response' => ['response'],
                'statusCode' => 200,
                'isSuccess' => true,
            ],
            'error case' => [
                'response' => 'error test',
                'statusCode' => 500,
                'isSuccess' => false,
            ]
        ];
    }
}
