<?php

namespace App\Adapters;

use App\Application\InputData\TargetDate;

final class TargetDateAdapter
{
    /**
     * getTargetDate
     *
     * @param  string $target_date
     * @return TargetDate
     */
    static public function getTargetDate(string $target_date): TargetDate
    {
        return new TargetDate($target_date);
    }
}
