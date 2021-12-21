<?php

namespace Unit\Application\InputData;

use App\Application\InputData\TargetDate;
use Tests\TestCase;

class TargetDateTest extends TestCase
{
    /**
     * getTargetDate
     * @test
     *
     * @return void
     */
    public function getTargetDate(): void
    {
        $expected = '2021-11-01';
        $model = new TargetDate(sprintf('%s 00:00:00', $expected));

        $this->assertEquals($expected, $model->getTargetDate());
    }
}
