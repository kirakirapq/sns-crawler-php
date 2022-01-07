<?php

namespace App\Application\InputData\Facebook\ValueObject;

use App\Exceptions\ObjectDefinitionErrorException;
use \ReflectionClass;

final class FacebookRequestType
{
    private string $type;

    public function __construct(string $type)
    {
        $reflectionClass = new ReflectionClass(FacebookRequestTypeEnum::class);
        if (in_array($type, $reflectionClass->getConstants()) === false) {
            $message = 'type must be the value defined in the FacebookRequestTypeEnum class';

            throw new ObjectDefinitionErrorException($message, 500);
        }

        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
