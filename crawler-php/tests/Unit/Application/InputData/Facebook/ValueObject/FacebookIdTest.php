<?php

namespace Unit\Application\InputData\Facebook\ValueObject;

use App\Application\InputData\Facebook\ValueObject\FacebookId;
use Tests\TestCase;

class FacebookIdTest extends TestCase
{
    /**
     * getId
     * @test
     *
     * @return void
     */
    public function getId(): void
    {
        $expected = 'id';
        $fb = new FacebookId($expected);

        $this->assertEquals($expected, $fb->getId());
    }
}
