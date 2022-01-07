<?php

namespace Unit\Entities\Translation;

use App\Entities\Translation\TranslationData;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * TranslationDataTest
 */
class TranslationDataTest extends TestCase
{
    /**
     * getStatusCode
     * @test
     *
     * @return void
     */
    public function getStatusCode(): void
    {
        $expected = $code = 200;
        $actual = new TranslationData($code, collect([]));

        $this->assertEquals($expected, $actual->getStatusCode());
    }

    /**
     * getText
     * @test
     * @dataProvider getTextProvider
     *
     * @return void
     */
    public function getText(array $data, ?array $expected): void
    {
        $code = 200;
        $actual = new TranslationData($code, collect($data));

        $this->assertEquals($expected, $actual->getText());
    }

    public function getTextProvider(): array
    {
        return [
            'array case' => [
                'data' => [
                    [
                        [
                            'text' => '1-xxxx'
                        ],
                        [
                            'text' => '1-yyyy'
                        ],
                        [
                            'text' => '1-zzzz'
                        ],
                    ],
                    [
                        [
                            'text' => '2-xxxx'
                        ],
                        [
                            '' => '2-yyyy' // not exists key
                        ],
                        [
                            'text' => '2-zzzz'
                        ],
                    ],
                ],
                'expected' => [
                    '1-xxxx', '1-yyyy', '1-zzzz', '2-xxxx', '', '2-zzzz'
                ]
            ],

        ];
    }
}
