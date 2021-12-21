<?php

namespace App\Application\UseCases\Notification;

use App\Entities\Notification\NotificationResponseModel;
use App\Entities\RiskWord\RiskCommentList;

interface NotificationUseCase
{
    public function notifyRiskComment(array $notification): NotificationResponseModel;
    public function notifyRiskCommentList(RiskCommentList $riskComment): NotificationResponseModel;
}
