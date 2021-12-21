<?php

namespace App\Entities\Facebook;

final class FacebookFeedDataList extends FacebookDataList
{
    private static ?FacebookDataList $_instance = null;

    /**
     * __construct
     *
     * @param  mixed $apiResponse
     * @return void
     */
    private function __construct(array $apiResponse)
    {
        $this->setData($apiResponse);
    }

    /**
     * getInstance
     *
     * @return FacebookDataList
     */
    final public static function getInstance(array $apiResponse): FacebookDataList
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new FacebookFeedDataList($apiResponse);
        } else {
            self::$_instance->setData($apiResponse);
        }

        return self::$_instance;
    }
}
