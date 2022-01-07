<?php

namespace Unit\Application\InputData\Translation;

use App\Application\InputData\Translation\BCP47;
use App\Application\InputData\Translation\TranslationRequestData;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use \Mockery;

class TranslationRequestDataTest extends TestCase
{
    /**
     * getProjectId
     * @test
     *
     * @return void
     */
    public function getProjectId(): void
    {
        $location = 'global';
        $projectId = 'prj';
        $textData = ['text'];
        $version = 'V3';
        $from = 'en';
        $to = 'ja';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($location, $projectId)
            ->times(2);

        $request = new TranslationRequestData($textData, $from, $to, $version);

        $this->assertEquals($projectId, $request->getProjectId());
    }

    /**
     * getLocation
     * @test
     *
     * @return void
     */
    public function getLocation(): void
    {
        $location = 'global';
        $projectId = 'prj';
        $textData = ['text'];
        $version = 'V3';
        $from = 'en';
        $to = 'ja';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($location, $projectId)
            ->times(2);

        $request = new TranslationRequestData($textData, $from, $to, $version);

        $this->assertEquals($location, $request->getLocation());
    }

    /**
     * getTextData
     * @test
     *
     * @return void
     */
    public function getTextData(): void
    {
        $location = 'global';
        $projectId = 'prj';
        $textData = ['text'];
        $version = 'V3';
        $from = 'en';
        $to = 'ja';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($location, $projectId)
            ->times(2);

        $request = new TranslationRequestData($textData, $from, $to, $version);

        $this->assertEquals($textData, $request->getTextData());
    }

    /**
     * getVersion
     * @test
     *
     * @return void
     */
    public function getVersion(): void
    {
        $location = 'global';
        $projectId = 'prj';
        $textData = ['text'];
        $version = 'V3';
        $from = 'en';
        $to = 'ja';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($location, $projectId)
            ->times(2);

        $request = new TranslationRequestData($textData, $from, $to, $version);

        $this->assertEquals($version, $request->getVersion());
    }

    /**
     * getTargetLanguageCode
     * @test
     *
     * @return void
     */
    public function getTargetLanguageCode(): void
    {
        $location = 'global';
        $projectId = 'prj';
        $textData = ['text'];
        $version = 'V3';
        $from = 'en';
        $to = 'ja';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($location, $projectId)
            ->times(2);

        $request = new TranslationRequestData($textData, $from, $to, $version);

        $this->assertEquals('ja', $request->getTargetLanguageCode());
    }

    /**
     * getSourceLanguageCode
     * @test
     *
     * @return void
     */
    public function getSourceLanguageCode(): void
    {
        $location = 'global';
        $projectId = 'prj';
        $textData = ['text'];
        $version = 'V3';
        $from = 'en';
        $to = 'ja';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($location, $projectId)
            ->times(2);

        $request = new TranslationRequestData($textData, $from, $to, $version);

        $this->assertEquals('en-US', $request->getSourceLanguageCode());
    }

    /**
     * getOption
     * @test
     *
     * @return void
     */
    public function getOption(): void
    {
        $location = 'global';
        $projectId = 'prj';
        $textData = ['text'];
        $version = 'V3';
        $from = 'en';
        $to = 'ja';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($location, $projectId)
            ->times(2);

        $request = new TranslationRequestData($textData, $from, $to, $version);

        $expected = [
            'mimeType' => 'text/plain',
            'sourceLanguageCode' => 'en-US',
            'model' => sprintf('projects/%s/locations/%s/models/general/nmt', $projectId, $location),
        ];

        $this->assertEquals($expected, $request->getOption());
    }
}
