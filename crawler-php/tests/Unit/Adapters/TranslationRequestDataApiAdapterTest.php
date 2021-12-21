<?php

namespace Unit\Adapters;

use App\Adapters\TranslationRequestDataApiAdapter;
use App\Application\InputData\TranslationRequestData;
use Tests\TestCase;
use \Mockery;

class TranslationRequestDataApiAdapterTest extends TestCase
{
    /**
     * getTranslationDataList
     * @test
     *
     * @return void
     */
    public function getTranslationDataList(): void
    {
        $text = '';
        $from = '';
        $to   = '';

        $response = new TranslationRequestData($text, $from, $to);

        $adapter = Mockery::mock('alias:' . TranslationRequestDataApiAdapter::class);
        $adapter->shouldReceive('getTranslationRequestData')->andReturn($response);

        $actual = TranslationRequestDataApiAdapter::getTranslationRequestData($text, $from, $to);

        $this->assertInstanceOf(TranslationRequestData::class, $actual);
    }
}
