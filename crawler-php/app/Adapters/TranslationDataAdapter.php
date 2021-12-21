<?php

namespace App\Adapters;

use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\InputData\Translation\TranslationRequestDataWithGAS;
use App\Entities\Translation\TranslationDataList;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Collection;

/**
 * TwitterApiAdapter
 * TwitterApiRepositoryがTwitterMentionDataListを取得
 */
final class TranslationDataAdapter
{
    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function getTranslationDataList(Collection $apiCollection, Collection $translated): TranslationDataList
    {
        return TranslationDataList::getInstance($apiCollection, $translated);
    }

    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function getTranslationRequestData(array $request, string $version = 'V3'): TranslationRequestData
    {
        if (
            isset($request['contents']) === false ||
            empty($request['contents']) === true ||
            is_array($request['contents']) === false
        ) {
            $ed = new ErrorDefinition(
                LayerCode::CONTROLL_LAYER_CODE,
                500
            );

            throw new OuterErrorException(
                $ed,
                'parameter error. [contents is array type and requied parameter.]'
            );
        }

        if (
            isset($request['language']['from']) === false ||
            empty($request['language']['from']) === true ||
            is_string($request['language']['from']) === false
        ) {
            $ed = new ErrorDefinition(
                LayerCode::CONTROLL_LAYER_CODE,
                500
            );

            throw new OuterErrorException(
                $ed,
                'parameter error. [language.from is string type and requied parameter.]'
            );
        }

        if (
            isset($request['language']['to']) === false ||
            empty($request['language']['to']) === true ||
            is_string($request['language']['to']) === false
        ) {
            $ed = new ErrorDefinition(
                LayerCode::CONTROLL_LAYER_CODE,
                500
            );

            throw new OuterErrorException(
                $ed,
                'parameter error. [language.to is string type and requied parameter.]'
            );
        }

        return new TranslationRequestData(
            $request['contents'],
            $request['language']['from'],
            $request['language']['to'],
            $version
        );
    }

    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function getTranslationRequestDataWithGAS(string $text, string $from, string $to): TranslationRequestDataWithGAS
    {
        return new TranslationRequestDataWithGAS($text, $from, $to);
    }
}
