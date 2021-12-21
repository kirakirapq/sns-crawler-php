<?php

namespace Unit\Application\InputData;

use App\Application\InputData\SubRedditRequestData;
use Tests\TestCase;

class SubRedditRequestDataTest extends TestCase
{
    /**
     * getUri
     * @test
     * @dataProvider getUriData
     *
     * @return void
     */
    public function getUri($id, $expected): void
    {
        $model = new SubRedditRequestData();

        $this->assertEquals($model->getUri($id), $expected);
    }

    public function getUriData(): array
    {
        return [
            'id empty case' => [
                'id' => '',
                'expected' => 'https://www.reddit.com/r/.json',
            ],
            'id not empty case' => [
                'id' => 'test',
                'expected' => 'https://www.reddit.com/r/test.json',
            ],
        ];
    }

    /**
     * getOptions
     * @test
     *
     * @return void
     */
    public function getOptions(): void
    {
        $model = new SubRedditRequestData();

        $this->assertEquals($model->getOptions(), [
            'headers' => [
                'Accept'        => 'application/json',
            ]
        ]);
    }

    /**
     * getMethod
     * @test
     *
     * @return void
     */
    public function getMethod(): void
    {
        $model = new SubRedditRequestData();

        $this->assertEquals($model->getMethod(), 'GET');
    }
}
