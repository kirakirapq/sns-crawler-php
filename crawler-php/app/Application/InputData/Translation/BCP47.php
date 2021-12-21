<?php

namespace App\Application\InputData\Translation;

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
            // TODO エラー
        }

        $bcp = new ReflectionClass(BCP47Enum::class);
        if (array_key_exists($constName, $bcp->getConstants()) === false) {
            // TODO エラー
        }

        $this->code = constant(BCP47Enum::class . '::' . $constName);
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
