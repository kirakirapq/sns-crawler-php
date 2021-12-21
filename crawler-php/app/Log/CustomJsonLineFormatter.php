<?php

namespace App\Log;

use Monolog\Processor\IntrospectionProcessor;
use Monolog\Logger;

class CustomJsonLineFormatter
{
    public function __invoke($logger)
    {
        $formatter = new JsonLineFormatter();

        $introspectionProcessor = new IntrospectionProcessor(Logger::DEBUG, ['Illuminate\\']);

        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor($introspectionProcessor);
            $handler->setFormatter($formatter);
        }
    }
}
