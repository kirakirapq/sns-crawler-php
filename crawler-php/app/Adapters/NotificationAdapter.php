<?php

namespace App\Adapters;

use App\Application\InputData\NotificationSendModel;
use App\Application\InputData\SlackNotificationSendModel;
use App\Entities\Notification\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;

final class NotificationAdapter
{
    static public function convertRiskCommentToSlackNotification(string $sns, RiskCommentList $riskComment): NotificationSendModel
    {
        $commentList = $riskComment->getCommentList();

        $title = $commentList->pluck('app_name')->first();
        $language = $commentList->pluck('language')->first();

        return new SlackNotificationSendModel($sns, $title, $language, $commentList->all());
    }

    /**
     * getSlackNotificationSendModel
     *
     * @param string $sns
     * @param string $title
     * @param string $language
     * @param array $messages
     *
     * @return NotificationSendModel
     */
    static public function getSlackNotificationSendModel(
        string $sns,
        string $title,
        string $language,
        array $messages
    ): NotificationSendModel {
        return new SlackNotificationSendModel($sns, $title, $language, $messages);
    }

    static public function getNotificationResponseModel(array $apiResponse): NotificationResponseModel
    {
        return new NotificationResponseModel($apiResponse);
    }
}
