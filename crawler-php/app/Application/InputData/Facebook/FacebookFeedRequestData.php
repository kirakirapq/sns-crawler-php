<?php

namespace App\Application\InputData\Facebook;

use App\Application\InputData\Facebook\ValueObject\FacebookAccessToken;
use App\Application\InputData\Facebook\ValueObject\FacebookId;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestType;

/**
 * Facebook feedを取得
 */
final class FacebookFeedRequestData implements FacebookRequestData
{
    const HOST = 'graph.facebook.com';
    const ENDPOINT = '/v12.0/%s/feed';
    const LIMIT = 100;
    private string $uri;
    private FacebookRequestType $requestType;

    public function __construct(
        FacebookRequestType $requestType,
        FacebookId $facebookId,
        FacebookAccessToken $accessToken,
        ?string $nextPage = null,
        ?int $limit = null
    ) {
        $this->requestType = $requestType;
        if (is_null($nextPage) === true) {
            $query = $this->getQueryString($accessToken, $limit);
            $this->uri = $this->setUri($facebookId, $query);
        } else {
            $this->uri = $nextPage;
        }
    }

    /**
     * getRequestTypeName
     *
     * @return FacebookRequestType
     */
    public function getReqestType(): FacebookRequestType
    {
        return $this->requestType;
    }

    /**
     * getUrl
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * setUrl
     *
     * @param  mixed $facebookId
     * @param  mixed $query
     * @return string
     */
    public function setUri(FacebookId $facebookId, string $query): string
    {
        $endpoint = sprintf(self::ENDPOINT, $facebookId->getId());

        return sprintf('https://%s%s%s', self::HOST, $endpoint, $query);
    }

    /**
     * getQueryString
     *
     * @param  mixed $accessToken
     * @param  mixed $limit
     * @return string
     */
    public function getQueryString(FacebookAccessToken $accessToken, ?int $limit = null): string
    {
        $query = [
            'access_token' => $accessToken->getAccessToken(),
            'limit' => $limit ?? self::LIMIT,
        ];

        $queryString = '';
        foreach ($query as $q => $v) {
            $s = empty($queryString) === true ? '?' : '&';

            $queryString .= sprintf('%s%s=%s', $s, $q, $v);
        }

        return $queryString;
    }
}
