<?php

namespace Unit\Adapters;

use App\Adapters\TargetDateAdapter;
use App\Application\InputData\TargetDate;
use Tests\TestCase;
use \Mockery;

class TargetDateAdapterTest extends TestCase
{
    /**
     * getTargetDate
     * @test
     *
     * @return void
     */
    public function getTargetDate(): void
    {
        $target_date = '2021-01-01';

        $actual = TargetDateAdapter::getTargetDate($target_date);

        $this->assertInstanceOf(TargetDate::class, $actual);
    }
}
