<?php

namespace Unit\Adapters;

use App\Adapters\TranslationRequestDataApiAdapter;
use App\Application\InputData\TranslationRequestData;
use App\Application\Interactors\Translation\TranslationManager;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\Repositories\Translation\TranslationRepository;
use App\Entities\ResponseData\Translation\TranslationData;
use App\Entities\Translation\TranslationDataList;
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
        $apiResponse     = Mockery::mock(InnerApiResponse::class);
        $apiResponse->shouldReceive([
            'getStatusCode' => 200,
            'hasError' => false,
            'getBodyAsArray' => [
                'text' => '',
                'code' => 200,
            ],
        ]);
        $translationData = new TranslationData($apiResponse);
        $requestData     = new TranslationRequestData('test', 'from', 'to');

        Mockery::mock('alias:' . TranslationRequestDataApiAdapter::class)
            ->shouldReceive('getTranslationRequestData')
            ->andReturn($requestData)
            ->once();

        $repository = Mockery::mock(TranslationRepository::class)
            ->shouldReceive('translation')
            ->with($requestData)
            ->andReturn($translationData)
            ->getMock();

        $manager = new TranslationManager($repository);
        $actual = $manager->translation('test', 'from', 'to');

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
        $translationDataList = new TranslationDataList(collect([]), collect([]));

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
