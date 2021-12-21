<?php

namespace App\Application\Repositories\Translation;

use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\InputData\Translation\TranslationRequestDataWithGAS;
use App\Entities\Translation\TranslationData;
use App\Entities\Translation\TranslationDataList;
use Illuminate\Support\Collection;

interface TranslationRepository
{
    public function translation(TranslationRequestData $requestData): TranslationData;

    public function translationWithGAS(TranslationRequestDataWithGAS $requestData): TranslationData;

    public function translationlist(Collection $apiCollection, string $version = ''): TranslationDataList;

    public function translationlistWithGAS(Collection $apiCollection): TranslationDataList;
}
