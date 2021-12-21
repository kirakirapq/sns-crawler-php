<?php

namespace App\Application\Interactors\Notification;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\Notification\NotificationInvoker;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Entities\RiskWord\RiskCommentList;
use App\Exceptions\OuterErrorException;

class SlackNotificationInvoker implements NotificationInvoker
{
    private NotificationUseCase $notificationUsecase;

    /**
     * __construct
     *
     * @param  mixed $notificationUsecase
     * @return void
     */
    public function __construct(NotificationUseCase $notificationUsecase)
    {
        $this->notificationUsecase = $notificationUsecase;
    }

    /**
     * invokeNotifyRiskCommentList
     *
     * @param  mixed $notification
     * @return OuterApiResponse
     */
    public function invokeNotifyRiskCommentList(RiskCommentList $notification): OuterApiResponse
    {
        try {
            $responseModel = $this->notificationUsecase->notifyRiskCommentList($notification);
            $response = [
                'message' => 'notification success',
                'description' => $responseModel->getBody(),
            ];

            return OuterApiResponseAdapter::getFromArray($response, 200);
        } catch (OuterErrorException $e) {
            return OuterApiResponseAdapter::getFromOuterErrorException($e);
        }
    }
}
