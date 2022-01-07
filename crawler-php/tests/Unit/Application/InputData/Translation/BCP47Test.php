<?php

namespace Unit\Application\InputData\Translation;

use App\Application\InputData\Translation\BCP47;
use App\Exceptions\ObjectDefinitionErrorException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BCP47Test extends TestCase
{
    /**
     * getCode
     * @test
     * @dataProvider getCodeDataProvider
     *
     * @return void
     */
    public function getCode(?string $isoCode, ?BCP47 $expected): void
    {
        if (is_null($expected) === true) {
            Log::shouldReceive('error');
            $this->expectException(ObjectDefinitionErrorException::class);
            $this->expectExceptionCode(500);
        }

        $bcp = new BCP47($isoCode);

        if (is_null($expected) === false) {
            $this->assertEquals($expected->getCode(), $bcp->getCode());
        }
    }

    /**
     * construct
     * @test
     * @dataProvider getCodeDataProvider
     *
     * @param  mixed $type
     * @param  mixed $expected
     * @return void
     */
    public function construct(string $isoCode, ?BCP47 $expected): void
    {
        if (is_null($expected) === true) {
            Log::shouldReceive('error');
            $this->expectException(ObjectDefinitionErrorException::class);
            $this->expectExceptionCode(500);
        }

        $actual = new BCP47($isoCode);

        if (is_null($expected) === false) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function getCodeDataProvider(): array
    {
        return [
            'ja case' => [
                'isoCode' => 'ja',
                'expected' => new BCP47('ja'),
            ],
            'jpn case' => [
                'isoCode' => 'jpn',
                'expected' => new BCP47('jpn'),
            ],
            'ko case' => [
                'isoCode' => 'ko',
                'expected' => new BCP47('ko'),
            ],
            'kor case' => [
                'isoCode' => 'kor',
                'expected' => new BCP47('kor'),
            ],
            'en case' => [
                'isoCode' => 'en',
                'expected' => new BCP47('en'),
            ],
            'eng case' => [
                'isoCode' => 'eng',
                'expected' => new BCP47('eng'),
            ],
            'other case' => [
                'isoCode' => 'ch',
                'expected' => null,
            ],
        ];
    }
}
