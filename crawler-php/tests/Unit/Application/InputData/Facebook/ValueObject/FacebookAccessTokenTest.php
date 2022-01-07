<?php

namespace Unit\Application\InputData\Facebook\ValueObject;

use App\Application\InputData\Facebook\ValueObject\FacebookAccessToken;
use Tests\TestCase;

class FacebookAccessTokenTest extends TestCase
{
    /**
     * getAccessToken
     * @test
     *
     * @return void
     */
    public function getAccessToken(): void
    {
        $expected = 'token';
        $token = new FacebookAccessToken($expected);

        $this->assertEquals($expected, $token->getAccessToken());
    }
}
