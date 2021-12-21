<?php

namespace App\Entities\Translation;

use Illuminate\Support\Collection;
use Google\Cloud\Translate\V3\TranslateTextResponse;

class GoogleTlanslationResponseData
{
    private static ?GoogleTlanslationResponseData $_instance = null;
    protected ?array $response = null;

    /**
     * __construct
     *
     * @param  mixed $apiResponse
     * @return void
     */
    private function __construct(mixed $translationResponse)
    {
        $this->setData($translationResponse);
    }

    /**
     * getInstance
     *
     * @return GoogleTlanslationResponseData
     */
    final public static function getInstance(mixed $translationResponse): GoogleTlanslationResponseData
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new GoogleTlanslationResponseData($translationResponse);
        } else {
            self::$_instance->setData($translationResponse);
        }

        return self::$_instance;
    }

    public function setData(mixed $translationResponse)
    {
        $translated = [];
        if ($translationResponse instanceof TranslateTextResponse) {
            foreach ($translationResponse->getTranslations() as $value) {
                $translated[]['text'] = $value->getTranslatedText();
            }
        } else {
            $translated = $translationResponse;
        }


        foreach ($translated as $item) {
            $this->response[] = $item;
        }
    }

    /**
     * getResponse
     *
     * @return array
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }
}
