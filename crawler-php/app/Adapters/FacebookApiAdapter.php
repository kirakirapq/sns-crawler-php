<?php

namespace App\Adapters;

use App\Application\InputData\Facebook\FacebookCommentRequestData;
use App\Application\InputData\Facebook\FacebookFeedRequestData;
use App\Application\InputData\Facebook\FacebookRequestData;
use App\Application\InputData\Facebook\ValueObject\FacebookAccessToken;
use App\Application\InputData\Facebook\ValueObject\FacebookId;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestType;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestTypeEnum;
use App\Entities\Facebook\FacebookDataList;
use App\Entities\Facebook\FacebookFeedDataList;
use App\Entities\Facebook\FacebookCommentDataList;
use App\Exceptions\ObjectDefinitionErrorException;
use Illuminate\Support\Facades\Config;

final class FacebookApiAdapter
{

    /**
     * getFacebookDataList
     *
     * @param  mixed $innerApiResponse
     * @return FacebookDataList
     */
    static public function getFacebookDataList(FacebookRequestType $requestType, array $innerApiResponse): FacebookDataList
    {
        if ($requestType->getType() === FacebookRequestTypeEnum::FEED) {
            return FacebookFeedDataList::getInstance($innerApiResponse);
        }

        if ($requestType->getType() === FacebookRequestTypeEnum::COMMENT) {
            return FacebookCommentDataList::getInstance($innerApiResponse);
        }
    }

    /**
     * getRequestType
     *
     * @param  mixed $type
     * @return FacebookRequestType
     */
    static public function getRequestType(string $type): FacebookRequestType
    {
        return new FacebookRequestType($type);
    }

    /**
     * getFeedRueqestData
     *
     * @param  mixed $requestType
     * @param  mixed $app
     * @param  mixed $language
     * @param  mixed $nextPage
     * @return FacebookRequestData
     */
    static public function getFeedRueqestData(
        FacebookRequestType $requestType,
        string $app,
        string $language,
        ?string $nextPage = null
    ): FacebookRequestData {
        if ($requestType->getType() === FacebookRequestTypeEnum::FEED) {
            $id    = Config::get(sprintf('facebook.%s.%s.id', $app, $language));
            $token = Config::get(sprintf('facebook.%s.%s.access_token', $app, $language));

            $facebookId = new FacebookId($id);
            $accessToken = new FacebookAccessToken($token);

            return new FacebookFeedRequestData($requestType, $facebookId, $accessToken, $nextPage);
        }

        throw new ObjectDefinitionErrorException('FacebookApiAdapter::getApiReqestData parameter error.', 500);
    }

    /**
     * getCommentReqestData
     *
     * @param  mixed $requestType
     * @param  mixed $app
     * @param  mixed $language
     * @param  mixed $nextPage
     * @return FacebookRequestData
     */
    static public function getCommentReqestData(
        FacebookRequestType $requestType,
        string $id,
        string $app,
        string $language,
        ?string $nextPage = null,
        ?string $fields = null
    ): FacebookRequestData {
        if ($requestType->getType() === FacebookRequestTypeEnum::COMMENT) {
            $token = Config::get(sprintf('facebook.%s.%s.access_token', $app, $language));

            $facebookId = new FacebookId($id);
            $accessToken = new FacebookAccessToken($token);

            return new FacebookCommentRequestData($requestType, $facebookId, $accessToken, $nextPage, 100, $fields);
        }

        throw new ObjectDefinitionErrorException('FacebookApiAdapter::getApiReqestData parameter error.', 500);
    }
}
