<?php

namespace Unit\Application\Interactors\Csv;

use App\Application\Interactors\Csv\CsvManager;
use App\Application\OutputData\InnerApiResponse\CsvResponse;
use App\Application\Repositories\Csv\CsvRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class CsvManagerTest extends TestCase
{
    /**
     * loadCsv
     * @test
     * @dataProvider dataProvider
     *
     * @param  mixed $hasError
     * @param  mixed $getBody
     * @param  mixed $expected
     * @return void
     */
    public function loadCsv(bool $hasError, string $getBody, string $expected): void
    {
        $csvResponse = Mockery::mock(CsvResponse::class)
            ->shouldReceive(
                [
                    'hasError' => $hasError,
                    'getBody' => $getBody,
                ]

            )->once()
            ->getMock();

        $csvRepository = Mockery::mock(CsvRepository::class);
        $csvRepository->shouldReceive('loadCsv')->andReturn($csvResponse)->once();

        if ($hasError === true) {
            Log::shouldReceive('error')->once();
        }

        $manager = new CsvManager($csvRepository);
        $actual = $manager->loadCsv('filename', collect([]));

        $this->assertEquals($expected, $actual);
    }

    /**
     * deleteFile
     * @test
     * @dataProvider dataProvider
     *
     * @param  mixed $hasError
     * @param  mixed $getBody
     * @param  mixed $expected
     * @return void
     */
    public function deleteFile(bool $hasError, string $getBody, string $expected): void
    {
        if ($hasError === true) {
            Log::shouldReceive('error')->once();
        }

        $csvResponse = Mockery::mock(CsvResponse::class)
            ->shouldReceive(
                [
                    'hasError' => $hasError,
                    'getBody' => $getBody,
                ]

            )->once()
            ->getMock();

        $csvRepository = Mockery::mock(CsvRepository::class);
        $csvRepository->shouldReceive('deleteFile')->andReturn($csvResponse)->once();

        $manager = new CsvManager($csvRepository);
        $actual = $manager->deleteFile('filename');

        $this->assertEquals($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            'error case' => [
                'hasError' => true,
                'getBody' => 'error',
                'expected' => '',
            ],
            'not error case' => [
                'hasError' => false,
                'getBody' => 'success.csv',
                'expected' => 'success.csv',
            ],
        ];
    }
}
