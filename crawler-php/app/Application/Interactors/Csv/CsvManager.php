<?php

namespace App\Application\Interactors\Csv;

use App\Application\Repositories\Csv\CsvRepository;
use App\Application\UseCases\Csv\CsvUseCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class CsvManager implements CsvUseCase
{
    private CsvRepository $csvRepository;

    public function __construct(
        CsvRepository $csvRepository
    ) {
        $this->csvRepository = $csvRepository;
    }
    /**
     * makeCsv
     * filePathへCSVを作成する
     *
     * @param  mixed $fileName
     * @param  mixed $data
     * @return string
     */
    public function loadCsv(string $fileName, Collection $data): string
    {
        $response = $this->csvRepository->loadCsv($fileName, $data);

        if ($response->hasError() === true) {
            Log::error('CsvManager:loadCsv', [$response->getBody()]);

            return '';
        }

        return $response->getBody();
    }

    public function deleteFile(string $fileName)
    {
        $response = $this->csvRepository->deleteFile($fileName);
        if ($response->hasError() === true) {
            Log::error('CsvManager:deleteFile', [$response->getBody()]);

            return '';
        }

        return $response->getBody();
    }
}
