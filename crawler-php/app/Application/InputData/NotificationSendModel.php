<?php

namespace App\Application\InputData;

interface NotificationSendModel
{
    public function getAddress(): string;

    public function getMessage();
}
