<?php

namespace App\Application\UseCases\Csv;

use Illuminate\Support\Collection;

interface CsvUseCase
{
    /**
     * makeCsv
     * filePathへCSVを作成する
     *
     * @param  mixed $fileName
     * @param  mixed $data
     * @return string
     */
    public function loadCsv(string $fileName, Collection $data): string;

    public function deleteFile(string $fileName);
}
