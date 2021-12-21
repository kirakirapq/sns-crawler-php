<?php

namespace App\Log;

class LogFormat
{
    const FORMAT_STACK_DRIVER =
    '[%datetime%] severity:%channel%.%level_name% [%extra.class% Line %extra.line%] message:%message%' . PHP_EOL;
}
