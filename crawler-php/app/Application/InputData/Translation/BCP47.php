<?php

namespace App\Application\InputData\Translation;

use App\Exceptions\ObjectDefinitionErrorException;
use \ReflectionClass;

class BCP47
{
    private string $code;

    public function __construct(string $isoLanguageCode)
    {
        $sourceCode = null;
        $iso = new ReflectionClass(ISO639::class);
        $isoConst = $iso->getConstants();
        foreach ($isoConst as $key => $const) {
            if (in_array($isoLanguageCode, $const) === true) {
                $sourceCode = $isoLanguageCode;
                $constName = $key;
            }
        }

        if (is_null($sourceCode) === true) {
            $message = 'The language code format is ISO-639 and must be included in this list [ja, ko, en].';

            throw new ObjectDefinitionErrorException($message, 500);
        }

        $bcp = new ReflectionClass(BCP47Enum::class);
        if (array_key_exists($constName, $bcp->getConstants()) === false) {
            $message = 'Could not convert from ISO-639 to BCP-47';

            throw new ObjectDefinitionErrorException($message, 500);
        }

        $this->code = constant(BCP47Enum::class . '::' . $constName);
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
