<?php

namespace App\Application\InputData;

interface BigQuerySqlModel
{
    public function getSql(): string;

    public function getParameters(): array;

    public function hasParameters(): bool;
}
