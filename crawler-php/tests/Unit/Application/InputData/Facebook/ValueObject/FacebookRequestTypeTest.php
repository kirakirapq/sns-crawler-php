<?php

namespace Unit\Application\InputData\Facebook\ValueObject;

use App\Application\InputData\Facebook\ValueObject\FacebookRequestType;
use App\Application\InputData\Facebook\ValueObject\FacebookRequestTypeEnum;
use App\Exceptions\ObjectDefinitionErrorException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FacebookRequestTypeTest extends TestCase
{
    /**
     * getType
     * @test
     *
     * @return void
     */
    public function getType(): void
    {
        $expected = FacebookRequestTypeEnum::COMMENT;
        $fb = new FacebookRequestType(FacebookRequestTypeEnum::COMMENT);

        $this->assertEquals($expected, $fb->getType());
    }

    /**
     * construct
     * @test
     * @dataProvider constructDataProvider
     *
     * @param  mixed $type
     * @param  mixed $expected
     * @return void
     */
    public function construct(string $type, ?FacebookRequestType $expected): void
    {
        if (is_null($expected) === true) {
            Log::shouldReceive('error');
            $this->expectException(ObjectDefinitionErrorException::class);
            $this->expectExceptionCode(500);
        }

        $actual = new FacebookRequestType($type);

        if (is_null($expected) === false) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function constructDataProvider(): array
    {
        return [
            'feed case' => [
                'type' => FacebookRequestTypeEnum::FEED,
                'expected' => new FacebookRequestType(FacebookRequestTypeEnum::FEED)
            ],
            'comment case' => [
                'type' => FacebookRequestTypeEnum::COMMENT,
                'expected' => new FacebookRequestType(FacebookRequestTypeEnum::COMMENT)
            ],
            'other case' => [
                'type' => '',
                'expected' => null,
            ],
        ];
    }
}
