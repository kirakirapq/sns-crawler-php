<?php

namespace App\Application\Interactors\Translation;

use App\Adapters\Translation\GoogleTranslationAdapter;
use App\Adapters\TranslationDataAdapter;
use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\Repositories\Translation\TranslationRepository;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Entities\Translation\TranslationData;
use App\Entities\Translation\TranslationDataList;
use Illuminate\Support\Collection;

final class TranslationManager implements TranslationUseCase
{
    private TranslationRepository $translationRepository;

    public function __construct(
        TranslationRepository $translationRepository
    ) {
        $this->translationRepository = $translationRepository;
    }


    public function translation(TranslationRequestData $requestData): TranslationData
    {
        return $this->translationRepository->translation($requestData);
    }

    public function translationlist(Collection $transData): TranslationDataList
    {
        return $this->translationRepository->translationlist($transData);
    }
}
