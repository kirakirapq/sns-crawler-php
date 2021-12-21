<?php

namespace App\Application\UseCases\Translation;

use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;

interface TranslationInvoker
{
    public function invokeTranslation(TranslationRequestData $requestData): OuterApiResponse;
}
