<?php

namespace App\Entities\Translation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class TranslationDataList
{
    private static ?TranslationDataList $_instance = null;

    private ?Collection $translationData = null;

    /**
     * __construct
     *
     * @param  mixed $apiResponse
     * @return void
     */
    private function __construct(Collection $apiCollection, Collection $translated)
    {
        $this->setData($apiCollection, $translated);
    }


    /**
     * getInstance
     *
     * @return TranslationDataList
     */
    final public static function getInstance(Collection $apiCollection, Collection $translated): TranslationDataList
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new TranslationDataList($apiCollection, $translated);
        } else {
            self::$_instance->setData($apiCollection, $translated);
        }

        return self::$_instance;
    }

    /**
     * setData
     *
     * @param  Collection $apiCollection
     * @param  Collection $translated
     * @return void
     */
    public function setData(Collection $apiCollection, Collection $translated)
    {
        $trans = $translated->all();

        if (is_null($this->translationData) === true) {
            $this->translationData = $apiCollection->map(function ($item, $key) use ($trans) {
                if (array_key_exists($key, $trans) === true) {
                    $item['translated'] = $trans[$key]['text'] ?? '';
                }

                return $item;
            });
        } else {
            $translationData = $apiCollection->map(function ($item, $key) use ($trans) {
                if (array_key_exists($key, $trans) === true) {
                    $item['translated'] = $trans[$key]['text'] ?? '';
                }

                return $item;
            });

            foreach ($translationData as $item) {
                $this->translationData->push($item);
            }
        }
    }

    /**
     * getMentionList
     *
     * @return ?Collection
     */
    public function translationData(): ?Collection
    {
        return $this->translationData;
    }
}
