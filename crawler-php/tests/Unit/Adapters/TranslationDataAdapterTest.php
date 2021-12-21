<?php

namespace Unit\Adapters;

use App\Adapters\TranslationDataAdapter;
use App\Entities\Translation\TranslationDataList;
use Tests\TestCase;
use \Mockery;

class TranslationDataAdapterTest extends TestCase
{
    /**
     * getTranslationDataList
     * @test
     *
     * @return void
     */
    public function getTranslationDataList(): void
    {
        $apiCollection = collect([]);
        $translated    = collect([]);

        $response = new TranslationDataList($apiCollection, $translated);

        $adapter = Mockery::mock('alias:' . TranslationDataAdapter::class);
        $adapter->shouldReceive('getTranslationDataList')->andReturn($response);

        $actual = TranslationDataAdapter::getTranslationDataList($apiCollection, $translated);

        $this->assertInstanceOf(TranslationDataList::class, $actual);
    }
}
