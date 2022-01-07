<?php

namespace Unit\Application\InputData\Facebook;

use App\Application\InputData\Facebook\ValueObject\FacebookAccessToken;
use App\Application\InputData\Facebook\ValueObject\FacebookId;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestType;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestTypeEnum;
use App\Application\InputData\Facebook\FacebookCommentRequestData;
use Tests\TestCase;

class FacebookCommentRequestDataTest extends TestCase
{
    /**
     * getReqestType
     * @test
     *
     * @return void
     */
    public function getReqestType(): void
    {
        $requestType = new FacebookRequestType(FacebookRequestTypeEnum::COMMENT);
        $facebookId = new FacebookId('id');
        $token = new FacebookAccessToken('token');


        $fb = new FacebookCommentRequestData($requestType, $facebookId, $token);

        $this->assertEquals($requestType, $fb->getReqestType());
    }

    /**
     * getUri
     * @test
     *
     * @return void
     */
    public function getUri(): void
    {
        $requestType = new FacebookRequestType(FacebookRequestTypeEnum::COMMENT);
        $facebookId = new FacebookId('id');
        $token = new FacebookAccessToken('token');

        $fb = new FacebookCommentRequestData($requestType, $facebookId, $token);

        $endpoint = sprintf(FacebookCommentRequestData::ENDPOINT, 'id');
        $expected = sprintf('https://%s%s%s', FacebookCommentRequestData::HOST, $endpoint, $fb->getQueryString($token));

        $this->assertEquals($expected, $fb->getUri());
    }

    /**
     * getQueryString
     * @test
     *
     * @return void
     */
    public function getQueryString(): void
    {
        $requestType = new FacebookRequestType(FacebookRequestTypeEnum::COMMENT);
        $facebookId = new FacebookId('id');
        $token = new FacebookAccessToken('token');

        $query = [
            'access_token' => $token->getAccessToken(),
            'limit' => $limit ?? FacebookCommentRequestData::LIMIT,
        ];
        $queryString = '';
        foreach ($query as $q => $v) {
            $s = empty($queryString) === true ? '?' : '&';

            $queryString .= sprintf('%s%s=%s', $s, $q, $v);
        }


        $fb = new FacebookCommentRequestData($requestType, $facebookId, $token);

        $this->assertEquals($queryString, $fb->getQueryString($token));
    }
}
