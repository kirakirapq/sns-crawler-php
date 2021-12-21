<?php

namespace App\Application\Repositories\Notification;

use App\Application\InputData\NotificationSendModel;
use App\Entities\Notification\NotificationResponseModel;

interface NotificationClient
{
    public function notify(NotificationSendModel $notificationSendModel): NotificationResponseModel;
}
