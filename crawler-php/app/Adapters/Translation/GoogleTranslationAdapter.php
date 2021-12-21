<?php

namespace App\Adapters\Translation;

use App\Adapters\TranslationDataAdapter;
use App\Application\InputData\Translation\BCP47;
use App\Application\OutputData\InnerApiResponse\TranslationResponse;
use App\Entities\Translation\TranslationDataList;
use App\Entities\Translation\TranslationData;
use Illuminate\Support\Collection;
use Google\Cloud\Translate\V3\TranslateTextResponse;
use App\Entities\Translation\GoogleTlanslationResponseData;

class GoogleTranslationAdapter
{
    /**
     * ISO639コードからBCP-47言語コードを取得する
     *
     * @param  string $isoLanguageCode
     * @return BCP47
     */
    static public function getBCP47(string $isoLanguageCode): BCP47
    {
        return new BCP47($isoLanguageCode);
    }

    /**
     * getTranlationV3OptionalArray
     *
     * @param  mixed $projectId
     * @param  mixed $location
     * @param  mixed $bcp47
     * @return array
     */
    static public function getTranlationV3OptionalArray(string $projectId, string $location, BCP47 $bcp47): array
    {
        $model = sprintf('projects/%s/locations/%s/models/general/nmt', $projectId, $location);
        return [
            'mimeType' => 'text/plain',
            'sourceLanguageCode' => $bcp47->getCode(),
            'model' => $model,
        ];
    }

    static public function getTranlationDataFromV2(array $result): TranslationData
    {
        return new TranslationData(200, collect([$result]));
    }

    static public function getTranlationDataFromV3(TranslateTextResponse $translateTextResponse): TranslationData
    {
        $translated = [];
        foreach ($translateTextResponse->getTranslations() as $value) {
            $translated[]['text'] = $value->getTranslatedText();
        }

        return new TranslationData(200, collect($translated));
    }

    /**
     * getTranlationDataListFromV2
     * 有料版のGoogle翻訳API結果（配列形式）をAPIコレクションに詰める
     *
     * @param  mixed $apiCollection
     * @param  mixed $translated
     * @return TranslationDataList
     */
    static public function getTranlationDataListFromArray(Collection $apiCollection, array $translated): TranslationDataList
    {
        return TranslationDataAdapter::getTranslationDataList($apiCollection, collect($translated));
    }

    /**
     * convertToArrayFromTranslateTextResponse
     * 有料版の翻訳結果を共通のデータ形式（GoogleTlanslationResponseData）に変換する
     *
     * @param  array|TranslateTextResponse $translateTextResponse
     * @return GoogleTlanslationResponseData
     */
    static public function getTranslationResponse(mixed $translateTextResponse): GoogleTlanslationResponseData
    {
        return GoogleTlanslationResponseData::getInstance($translateTextResponse);
    }
}
