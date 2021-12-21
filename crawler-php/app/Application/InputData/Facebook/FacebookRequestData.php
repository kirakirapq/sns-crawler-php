<?php

namespace App\Application\InputData\Facebook;

use App\Application\InputData\Facebook\ValueObject\FacebookRequestType;

interface FacebookRequestData
{
    public function getReqestType(): FacebookRequestType;
    public function getUri(): string;
}
