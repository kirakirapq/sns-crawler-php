<?php

namespace App\Adapters\Translation;

use App\Adapters\TranslationDataAdapter;
use App\Application\InputData\Translation\BCP47;
use App\Application\OutputData\InnerApiResponse\TranslationResponse;
use App\Entities\Translation\TranslationDataList;
use App\Entities\Translation\TranslationData;
use Illuminate\Support\Collection;
use Google\Cloud\Translate\V3\TranslateTextResponse;

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
     * 有料版のGoogle翻訳API V2のレスポンスからTranslationDataList取得
     *
     * @param  mixed $apiCollection
     * @param  mixed $translated
     * @return TranslationDataList
     */
    static public function getTranlationDataListFromV2(Collection $apiCollection, array $translated): TranslationDataList
    {
        return TranslationDataAdapter::getTranslationDataList($apiCollection, collect($translated));
    }

    /**
     * getTranlationDataListFromV3
     * 有料版のGoogle翻訳API V3のレスポンスからTranslationDataList取得
     *
     * @param  mixed $apiCollection
     * @param  mixed $translateTextResponse
     * @return TranslationDataList
     */
    static public function getTranlationDataListFromV3(Collection $apiCollection, TranslateTextResponse $translateTextResponse): TranslationDataList
    {
        $translated = [];
        foreach ($translateTextResponse->getTranslations() as $key => $value) {
            $translated[]['text'] = $value->getTranslatedText();
        }

        return TranslationDataAdapter::getTranslationDataList($apiCollection, collect($translated));
    }
}
