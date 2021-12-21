<?php

namespace Unit\Entities\Translation;

use App\Entities\Translation\TranslationDataList;
use Tests\TestCase;
use \Mockery;

class TranslationDataListTest extends TestCase
{

    /**
     * translationData
     * @test
     *
     * @return void
     */
    public function translationData(): void
    {
        $api = $trans = [];
        for ($i = 0; $i < 10; $i++) {
            $api[$i] = [
                'id' => $i + 1,
                'text' => sprintf('text_%s', $i + 1),
            ];
            $trans[$i] =
                Mockery::mock(StdClass::class)
                ->shouldReceive([
                    'getBodyAsArray' => [
                        'id' => $i * 1,
                        'text' => sprintf('text_%s', $i * 1),
                    ],
                ])
                ->getMock();
            $expected[$i] = [
                'id' => $i + 1,
                'text' => sprintf('text_%s', $i + 1),
                'translated' => sprintf('text_%s', $i * 1),
            ];
        }

        $apiCollection = collect($api);
        $translated = collect($trans);

        $entity = new TranslationDataList($apiCollection, $translated);

        $this->assertEquals(collect($expected), $entity->translationData());
    }
}
