<?php

namespace App\Adapters;

use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\InputData\Translation\TranslationRequestDataWithGAS;
use App\Entities\Translation\TranslationDataList;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

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
    static public function getTranslationRequestData(Request $request, string $version = 'V3'): TranslationRequestData
    {
        $requestArray = $request->all();

        return new TranslationRequestData(
            $requestArray['contents'],
            $requestArray['language']['from'],
            $requestArray['language']['to'],
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
