<?php

namespace App\Application\UseCases\Translation;

use App\Application\InputData\Translation\TranslationRequestData;
use App\Entities\Translation\TranslationData;
use App\Entities\Translation\TranslationDataList;
use Illuminate\Support\Collection;

interface TranslationUseCase
{
    public function translation(TranslationRequestData $requestData): TranslationData;

    public function translationlist(Collection $urls): TranslationDataList;
}
