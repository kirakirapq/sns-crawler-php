<?php

namespace App\Entities\ResponseData\BigQuery;

use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use \Iterator;
// use Google\Cloud\Core\Iterator\ItemIterator;
use Illuminate\Support\Collection;

class BigQueryData
{
    private int $statusCode;

    private ?Collection $dataList = null;

    private string $projectId;

    private string $jobId;

    private string $location;

    private string $errorMessage;

    private bool $hasError;

    public function __construct(InnerApiResponse $apiResponse)
    {
        $this->statusCode = $apiResponse->getStatusCode();
        $this->hasError = $apiResponse->hasError();

        $responsBody = $apiResponse->getBody();


        $this->setDataList($responsBody->rows());

        if ($apiResponse->hasError() === true) {
            $this->errorMessage = $responsBody->info();
        }

        if (method_exists(get_class($responsBody), 'identity') === true) {
            $identity = $responsBody->identity();
            $this->projectId = $identity['projectId'] ?? '';
            $this->jobId     = $identity['jobId'] ?? '';
            $this->location = $identity['location'] ?? '';
        }
    }

    protected function setDataList(array|Iterator $rawData)
    {
        if ($rawData instanceof Iterator) {
            $rawData = iterator_to_array($rawData);
        }
        $this->addDataList($rawData);
    }

    /**
     * addDataList
     *
     * @param  mixed $rawData
     * @return void
     */
    public function addDataList(array $rawData)
    {
        if (is_null($this->dataList) === true) {
            $this->dataList = collect($rawData);
        } else {
            foreach ($rawData as $item) {
                $this->dataList->push($item);
            }
        }
    }

    /**
     * getStatusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * getDataList
     *
     * @return ?Collection
     */
    public function getDataList(): ?Collection
    {
        return $this->dataList;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * getErrorMessage
     *
     * @return void
     */
    public function getErrorMessage()
    {
        return $this->errorMessage ?? null;
    }

    /**
     * hasError
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }
}
