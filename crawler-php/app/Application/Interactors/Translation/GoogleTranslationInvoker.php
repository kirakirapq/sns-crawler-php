<?php

namespace App\Application\Interactors\Translation;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\Translation\TranslationInvoker;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Exceptions\OuterErrorException;

class GoogleTranslationInvoker implements TranslationInvoker
{
    private TranslationUseCase $translationUseCase;

    public function __construct(TranslationUseCase $translationUseCase)
    {
        $this->translationUseCase = $translationUseCase;
    }
    public function invokeTranslation(TranslationRequestData $requestData): OuterApiResponse
    {
        try {
            $translated = $this->translationUseCase->translation($requestData);

            $response = [
                'contents' => $requestData->getTextData(),
                'translated' => $translated->getText(),
                'language' => [
                    'translation from' => $requestData->getSourceLanguageCode(),
                    'translation to' => $requestData->getTargetLanguageCode(),
                ],
            ];

            return OuterApiResponseAdapter::getFromArray($response, $translated->getStatusCode());
        } catch (OuterErrorException $e) {
            return OuterApiResponseAdapter::getFromOuterErrorException($e);
        }
    }
}
