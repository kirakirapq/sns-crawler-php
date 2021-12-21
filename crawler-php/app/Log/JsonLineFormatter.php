<?php

namespace App\Log;

use Monolog\Formatter\JsonFormatter;

class JsonLineFormatter extends JsonFormatter
{

    public function format(array $record): string
    {
        $record = [
            'datetime' => $record['datetime'],
            'channel' => $record['channel'],
            'severity' => $record['level_name'],
            'message' => $record['message'],
            'class' => sprintf('[%s, Line %s]', $record['extra']['class'], $record['extra']['line']),
        ];



        $normalized = $this->normalize($record);

        if (isset($normalized['context']) && $normalized['context'] === []) {
            if ($this->ignoreEmptyContextAndExtra) {
                unset($normalized['context']);
            } else {
                $normalized['context'] = new \stdClass;
            }
        }
        if (isset($normalized['extra']) && $normalized['extra'] === []) {
            if ($this->ignoreEmptyContextAndExtra) {
                unset($normalized['extra']);
            } else {
                $normalized['extra'] = new \stdClass;
            }
        }

        return $this->toJson($normalized, true) . ($this->appendNewline ? "\n" : '');
    }
}
