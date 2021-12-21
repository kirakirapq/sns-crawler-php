<?php

namespace App\Application\InputData;

use Carbon\Carbon;

class TargetDate
{
    private Carbon $target_date;

    public function __construct(mixed $created_at, $timezone = 'Asia/Tokyo')
    {
        if (is_int($created_at) === true) {
            $this->target_date = Carbon::createFromTimestampUTC($created_at, $timezone);
        } else {
            $this->target_date = Carbon::parse($created_at, $timezone);
            // $this->target_date = new Carbon($carbon, $timezone);
        }
    }

    public function getTargetDate(string $format = 'Y-m-d')
    {
        return $this->target_date->format($format);
    }

    public function getCarbon(): Carbon
    {
        return $this->target_date;
    }
}
