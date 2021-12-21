<?php

namespace App\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Logger;

class CustomLineFormatter
{
    public function __invoke($logger)
    {
        $lineFormatter = new LineFormatter(LogFormat::FORMAT_STACK_DRIVER, DateFormat::DEFAULT_FORMAT, true, true);

        $introspectionProcessor = new IntrospectionProcessor(Logger::DEBUG, ['Illuminate\\']);

        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor($introspectionProcessor);
            $handler->setFormatter($lineFormatter);
        }
    }
}
