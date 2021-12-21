<?php

namespace App\Gateways\Csv;

use App\Adapters\CsvResponseAdapter;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\OutputData\InnerApiResponse\CsvResponse;
use App\Application\Repositories\Csv\CsvRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class CsvClient implements CsvRepository
{
    /**
     * putCsv
     *
     * @param  mixed $fileName
     * @param  mixed $data
     * @return InnerApiResponse
     */
    public function loadCsv(string $fileName, Collection $data): InnerApiResponse
    {
        if ($data->count() === 0) {
            return new CsvResponse(404, 'data not found.');
        }

        // アップロードファイルのファイルパスを取得
        $filePath = Storage::path($fileName);

        $fp = fopen($filePath, 'w');
        fputcsv($fp, array_keys($data->first()));

        foreach ($data->all() as $line) {
            fputcsv($fp, preg_replace('/\n|\r|\r\n/', ' ', array_values($line)));
        }

        fclose($fp);

        return CsvResponseAdapter::getCsvResponse(201, $filePath);
    }

    /**
     * deleteFile
     *
     * @param  mixed $fileName
     * @return InnerApiResponse
     */
    public function deleteFile(string $fileName): InnerApiResponse
    {
        if (file_exists($fileName) === false) {
            return new CsvResponse(401, 'file not found.');
        }

        unlink($fileName);

        return CsvResponseAdapter::getCsvResponse(204, '');
    }
}
