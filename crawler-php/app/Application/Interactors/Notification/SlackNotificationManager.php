<?php

namespace App\Application\Interactors\Notification;

use App\Adapters\NotificationAdapter;
use App\Application\Repositories\Notification\NotificationClient;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Entities\Notification\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;
use Illuminate\Support\Facades\Config;

final class SlackNotificationManager implements NotificationUseCase
{
    private NotificationClient $notificationClient;

    public function __construct(NotificationClient $notificationClient)
    {
        $this->notificationClient = $notificationClient;
    }

    /**
     * notifyRiskComment
     * 配列で受け取った通知情報から通知を行う
     * 外部APIで使っている
     *
     * @param  mixed $notification
     * @return NotificationResponseModel
     */
    public function notifyRiskComment(array $notification): NotificationResponseModel
    {
        $notificationModel = NotificationAdapter::getSlackNotificationSendModel(
            $notification['sns'],
            $notification['title'],
            $notification['language'],
            $notification['messages'],
        );

        return $this->notificationClient->notify($notificationModel);
    }

    /**
     * notifyRiskCommentList
     * 内部APIで使用している
     * RiskCommentListから通知を行う
     *
     * @param  mixed $riskComment
     * @return NotificationResponseModel
     */
    public function notifyRiskCommentList(RiskCommentList $riskComment): NotificationResponseModel
    {
        $sns = Config::get('crawl.name');
        $notificationModel = NotificationAdapter::convertRiskCommentToSlackNotification($sns, $riskComment);

        return $this->notificationClient->notify($notificationModel);
    }
}
