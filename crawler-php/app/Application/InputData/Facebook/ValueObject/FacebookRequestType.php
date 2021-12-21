<?php

namespace App\Application\InputData\Facebook\ValueObject;

use \ReflectionClass;

class FacebookRequestType
{
    private string $type;

    public function __construct(string $type)
    {
        $reflectionClass = new ReflectionClass(FacebookRequestTypeEnum::class);
        if (in_array($type, $reflectionClass->getConstants()) === false) {
            // TODO エラー
        }

        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
