<?php

namespace Unit\Application\InputData\Translation;

use App\Application\InputData\RequestType;
use App\Application\InputData\Translation\TranslationRequestDataWithGAS;
use Tests\TestCase;

class TranslationRequestDataWithGASTest extends TestCase
{
    /**
     * getProjectId
     * @test
     * @dataProvider getUriDataProvider
     *
     * @return void
     */
    public function getUri($text, $from, $to): void
    {
        $text_1 = 'hoge';
        $from_1 = 'ko';
        $to_1  = 'ch';

        $text_2 = $text ?? $text_1;
        $from_2 = $from ?? $from_1;
        $to_2 = $to ?? $to_1;

        $uri = sprintf(TranslationRequestDataWithGAS::BASE_URI, TranslationRequestDataWithGAS::API_KEY);
        $expected = sprintf('%s?text=%s&source=%s&target=%s', $uri, $text_2, $from_2, $to_2);

        $request = new TranslationRequestDataWithGAS($text_1, $from_1, $to_1);

        $this->assertEquals($expected, $request->getUri($text, $from, $to));
    }

    public function getUriDataProvider(): array
    {
        return [
            'case 1' => [
                'text' => null,
                'from' => null,
                'to' => null,
            ],
            'case 2' => [
                'text' => 'text',
                'from' => null,
                'to' => null,
            ],
            'case 3' => [
                'text' => 'text',
                'from' => 'en',
                'to' => null,
            ],
            'case 4' => [
                'text' => 'text',
                'from' => 'en',
                'to' => 'ja',
            ],
            'case 5' => [
                'text' => null,
                'from' => 'en',
                'to' => 'ja',
            ],
            'case 6' => [
                'text' => null,
                'from' => 'en',
                'to' => null,
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
        $expected =
            [
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ];
        $request = new TranslationRequestDataWithGAS('text', 'en', 'ja');

        $this->assertEquals($expected, $request->getOptions());
    }

    /**
     * getMethod
     * @test
     *
     * @return void
     */
    public function getMethod(): void
    {
        $request = new TranslationRequestDataWithGAS('text', 'en', 'ja');
        $this->assertEquals(RequestType::GET, $request->getMethod());
    }
}
