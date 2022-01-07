<?php

namespace Unit\Adapters;

use App\Adapters\FacebookApiAdapter;
use App\Application\InputData\Facebook\FacebookCommentRequestData;
use App\Application\InputData\Facebook\FacebookFeedRequestData;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestType;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestTypeEnum;
use App\Application\InputData\Facebook\ValueObject\FacebookAccessToken;
use App\Application\InputData\Facebook\ValueObject\FacebookId;
use App\Entities\Facebook\FacebookFeedDataList;
use App\Entities\Facebook\FacebookCommentDataList;
use App\Entities\Facebook\FacebookDataList;
use App\Exceptions\ObjectDefinitionErrorException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class FacebookApiAdapterTest extends TestCase
{
    /**
     * getFacebookDataList
     * @test
     * @dataProvider getFacebookDataListProvider
     *
     * @param  mixed $requestType
     * @param  mixed $respones
     * @param  mixed $expected
     * @return void
     */
    public function getFacebookDataList(
        FacebookRequestType $requestType,
        array $respones,
        FacebookDataList $expected
    ): void {
        $actual = FacebookApiAdapter::getFacebookDataList($requestType, $respones);

        $this->assertEquals($expected, $actual);
    }

    public function getFacebookDataListProvider(): array
    {
        $innerApiResponse = [
            'data' => [
                ['created_time' => '2022-01-01']
            ]
        ];
        return [
            'FEED case' => [
                'requestType' => new FacebookRequestType(FacebookRequestTypeEnum::FEED),
                'respones' => $innerApiResponse,
                'expected' => FacebookFeedDataList::getInstance($innerApiResponse)
            ],
            'COMMENT case' => [
                'requestType' => new FacebookRequestType(FacebookRequestTypeEnum::COMMENT),
                'respones' => $innerApiResponse,
                'expected' => FacebookCommentDataList::getInstance($innerApiResponse)
            ],
        ];
    }

    /**
     * getRequestType
     * @test
     * @dataProvider getRequestTypeProvider
     *
     * @param  mixed $type
     * @param  mixed $expected
     * @return void
     */
    public function getRequestType(string $type, FacebookRequestType $expected): void
    {
        $actual = FacebookApiAdapter::getRequestType($type);

        $this->assertEquals($expected, $actual);
    }

    public function getRequestTypeProvider(): array
    {
        return [
            'feed case' => [
                'type' => 'feed',
                'expected' => new FacebookRequestType(FacebookRequestTypeEnum::FEED),
            ],
            'comment case' => [
                'type' => 'comment',
                'expected' => new FacebookRequestType(FacebookRequestTypeEnum::COMMENT),
            ]
        ];
    }

    /**
     * getFeedRueqestData
     * @test
     * @dataProvider getFeedRueqestDataProvider
     *
     * @param  mixed $config
     * @param  mixed $requestType
     * @param  mixed $expected
     * @return void
     */
    public function getFeedRueqestData(array $config, FacebookRequestType $requestType, ?FacebookFeedRequestData $expected): void
    {
        if (is_null($expected) === true) {
            Mockery::mock('alias:' . Config::class)
                ->shouldReceive('get')
                ->never();
            Log::shouldReceive('error');
            $this->expectException(ObjectDefinitionErrorException::class);
            $this->expectExceptionCode(500);
        } else {
            Mockery::mock('alias:' . Config::class)
                ->shouldReceive('get')
                ->andReturn($config['id'], $config['token'])
                ->times(2);
        }

        $actual = FacebookApiAdapter::getFeedRueqestData($requestType, $config['id'], $config['token']);

        if (is_null($expected) === false) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function getFeedRueqestDataProvider(): array
    {
        $id = 'id-123';
        $token = 'en';
        $facebookId = new FacebookId($id);
        $accessToken = new FacebookAccessToken($token);

        return [
            'success case' => [
                'config' => ['id' => $id, 'token' => $token],
                'requestType' => new FacebookRequestType(FacebookRequestTypeEnum::FEED),
                'expected' => new FacebookFeedRequestData(
                    new FacebookRequestType(FacebookRequestTypeEnum::FEED),
                    $facebookId,
                    $accessToken
                ),
            ],
            'error case' => [
                'config' => ['id' => $id, 'token' => $token],
                'requestType' => new FacebookRequestType(FacebookRequestTypeEnum::COMMENT),
                'expected' => null,
            ],
        ];
    }

    /**
     * getCommentReqestData
     * @test
     * @dataProvider getCommentReqestDataProvider
     *
     * @param  mixed $config
     * @param  mixed $requestType
     * @param  mixed $expected
     * @return void
     */
    public function getCommentReqestData(array $config, FacebookRequestType $requestType, ?FacebookCommentRequestData $expected): void
    {
        if (is_null($expected) === true) {
            Mockery::mock('alias:' . Config::class)
                ->shouldReceive('get')
                ->never();
            Log::shouldReceive('error');
            $this->expectException(ObjectDefinitionErrorException::class);
            $this->expectExceptionCode(500);
        } else {
            Mockery::mock('alias:' . Config::class)
                ->shouldReceive('get')
                ->andReturn($config['token'])
                ->times(1);
        }

        $actual = FacebookApiAdapter::getCommentReqestData($requestType, $config['id'], 'app', 'language');

        if (is_null($expected) === false) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function getCommentReqestDataProvider(): array
    {
        $id = 'id-123';
        $token = 'en';
        $facebookId = new FacebookId($id);
        $accessToken = new FacebookAccessToken($token);

        return [
            'success case' => [
                'config' => ['id' => $id, 'token' => $token],
                'requestType' => new FacebookRequestType(FacebookRequestTypeEnum::COMMENT),
                'expected' => new FacebookCommentRequestData(
                    new FacebookRequestType(FacebookRequestTypeEnum::COMMENT),
                    $facebookId,
                    $accessToken,
                    null,
                    100
                ),
            ],
            'error case' => [
                'config' => ['id' => $id, 'token' => $token],
                'requestType' => new FacebookRequestType(FacebookRequestTypeEnum::FEED),
                'expected' => null,
            ],
        ];
    }
}
