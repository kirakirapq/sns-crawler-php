<?php

namespace App\Application\UseCases\Notification;

use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Entities\RiskWord\RiskCommentList;

interface NotificationInvoker
{
    public function invokeNotifyRiskCommentList(RiskCommentList $notification): OuterApiResponse;
}
