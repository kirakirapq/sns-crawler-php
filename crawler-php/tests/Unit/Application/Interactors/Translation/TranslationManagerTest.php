<?php

namespace Unit\Application\Interactors\Translation;

use App\Adapters\TranslationDataAdapter;
use App\Application\Interactors\Translation\TranslationManager;
use App\Application\Repositories\Translation\TranslationRepository;
use App\Entities\Translation\TranslationData;
use App\Entities\Translation\TranslationDataList;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use \Mockery;

class TranslationManagerTest extends TestCase
{
    /**
     * translation
     * @test
     *
     * @return void
     */
    public function translation(): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn('');

        $reqests = [
            'contents' => [
                'xxxx',
                'yyyy'
            ],
            'language' => [
                'from' => 'ja',
                'to' => 'en'
            ]
        ];

        $translated = collect([
            [
                'text' => '',
                'code' => 200,
            ]
        ]);
        $translationData = new TranslationData(200, $translated);
        $requestData     = TranslationDataAdapter::getTranslationRequestData($reqests);

        $repository = Mockery::mock(TranslationRepository::class)
            ->shouldReceive('translation')
            ->andReturn($translationData)
            ->getMock();

        $manager = new TranslationManager($repository);
        $actual = $manager->translation($requestData);

        $this->assertEquals($translationData, $actual);
    }

    /**
     * translationlist
     * @test
     *
     * @return void
     */
    public function translationlist(): void
    {
        $translationDataList = TranslationDataList::getInstance(collect([]), collect([]));

        $urls = collect([]);
        $repository = Mockery::mock(TranslationRepository::class)
            ->shouldReceive('translationlist')
            ->with($urls)
            ->andReturn($translationDataList)
            ->getMock();

        $manager = new TranslationManager($repository);
        $actual = $manager->translationlist($urls);

        $this->assertEquals($translationDataList, $actual);
    }
}
