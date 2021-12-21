<?php

namespace App\Application\InputData;

use Carbon\Carbon;

class TargetDate
{
    private Carbon $target_date;

    public function __construct(string $created_at, $timezone = 'Asia/Tokyo')
    {
        $carbon = Carbon::parse($created_at);
        $this->target_date = new Carbon($carbon, $timezone);
    }

    public function getTargetDate(string $format = 'Y-m-d')
    {
        return $this->target_date->format($format);
    }
}
