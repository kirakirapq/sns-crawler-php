<?php

namespace Unit\Entities\Twitter;

use App\Entities\Twitter\TwitterMetaData;
use Tests\TestCase;

class TwitterMetaDataTest extends TestCase
{
    /**
     * getByKey
     * @test
     *
     * @return void
     */
    public function getByKey()
    {
        $expected = 'test data';
        $key = 'id';

        $metaData = [
            $key => $expected
        ];
        $entity = new TwitterMetaData($metaData);

        $this->assertEquals($expected, $entity->getByKey($key));
    }

    /**
     * getResultCount
     * @test
     *
     * @return void
     */
    public function getResultCount(): void
    {
        $expected = 101;
        $key = 'result_count';

        $metaData = [
            $key => $expected
        ];
        $entity = new TwitterMetaData($metaData);

        $this->assertEquals($expected, $entity->getResultCount());
    }

    /**
     * getNextToken
     * @test
     *
     * @return void
     */
    public function getNextToken(): void
    {
        $expected = 'my token';
        $key = 'next_token';

        $metaData = [
            $key => $expected
        ];
        $entity = new TwitterMetaData($metaData);

        $this->assertEquals($expected, $entity->getNextToken());
    }
}
