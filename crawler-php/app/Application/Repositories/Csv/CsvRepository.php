<?php

namespace App\Application\Repositories\Csv;

use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use Illuminate\Support\Collection;

interface CsvRepository
{
    public function loadCsv(string $fileName, Collection $data): InnerApiResponse;

    public function deleteFile(string $fileName): InnerApiResponse;
}
