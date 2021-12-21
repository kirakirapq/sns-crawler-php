<?php

namespace App\Entities\Facebook;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use \Carbon\Carbon;

abstract class FacebookDataList
{
    protected ?Collection $dataList = null;

    private ?string $nextPage = null;

    /**
     * getInstance
     *
     * @return FacebookDataList
     */
    abstract public static function getInstance(array $apiResponse): FacebookDataList;

    public function setData(array $apiResponse)
    {
        $dataList = array_map(
            function ($item) {
                $carbon = Carbon::parse($item['created_time']);
                $item['created_at'] = $item['created_time'];
                $item['date'] = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d');

                return $item;
            },
            $apiResponse['data']
        );

        $this->setNextPage($apiResponse);

        if (is_null($this->dataList) === true) {
            $this->dataList = collect($dataList);
        } else {
            foreach ($dataList as $item) {
                $this->dataList->push($item);
            }
        }
    }

    /**
     * hasData
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        if (is_null($this->dataList) === true) {
            return true;
        }

        if ($this->getDataList()->count() === 0) {
            return true;
        }

        return false;
    }

    /**
     * getDataList
     *
     * @return Collection
     */
    public function getDataList(): ?Collection
    {
        return $this->dataList;
    }

    /**
     * getNextPage
     *
     * @return string
     */
    public function getNextPage(): ?string
    {
        return $this->nextPage;
    }

    /**
     * hasNextPage
     *
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return is_null($this->nextPage) === true ? false : true;
    }

    /**
     * setNextPage
     *
     * @param  mixed $response
     * @return void
     */
    protected function setNextPage(array $response): void
    {
        $this->nextPage = null;
        if ($this->isNextPage($response) === true) {
            $this->nextPage = $response['paging']['next'];
        } else {
            $this->nextPage = null;
        }
    }

    /**
     * isNextPage
     *
     * @param  mixed $response
     * @return bool
     */
    protected function isNextPage(array $response): bool
    {
        if (array_key_exists('paging', $response) === true) {
            if (array_key_exists('next', $response['paging']) === true && empty($response['paging']['next']) === false) {
                return true;
            }
        }

        return false;
    }
}
