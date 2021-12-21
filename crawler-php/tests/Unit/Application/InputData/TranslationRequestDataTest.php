<?php

namespace Unit\Application\InputData;

use App\Application\InputData\TranslationRequestData;
use Tests\TestCase;

class TranslationRequestDataTest extends TestCase
{
    /**
     * getUri
     * @test
     * @dataProvider getUriData
     *
     * @return void
     */
    public function getUri($text, $from, $to, $expected): void
    {
        $model = new TranslationRequestData('', '', '');

        $this->assertTrue(0 < strpos($model->getUri($text, $from, $to), $expected));
    }

    public function getUriData(): array
    {
        return [
            'text is null case' => [
                'text' => null,
                'from' => 'from',
                'to'   => 'to',
                'expected' => 'text=&source=from&target=to',
            ],
            'from is null case' => [
                'text' => 'text',
                'from' => null,
                'to'   => 'to',
                'expected' => 'text=text&source=&target=to',
            ],
            'to is null case' => [
                'text' => 'text',
                'from' => 'from',
                'to'   => null,
                'expected' => 'text=text&source=from&target=',
            ]
        ];
    }
}
