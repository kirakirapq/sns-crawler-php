<?php

namespace App\Entities\RiskWord;

use App\Application\InputData\TargetDate;
use Illuminate\Support\Collection;

final class RiskCommentList
{
    private int $statusCode;
    private bool $hasError;
    private $errorMessage;
    private Collection $commentList;

    /**
     * __construct
     *
     * @param  mixed $bigQueryData
     * @return void
     */
    public function __construct(int $statusCode, Collection $dataList, $errorMessage = null)
    {
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
        $this->commentList = collect([]);

        if (is_null($this->errorMessage) === true || empty($this->errorMessage) === true) {
            $this->hasError = false;
        } else {
            $this->hasError = true;
        }

        foreach ($dataList ?? [] as $i => $data) {
            if (empty($data) === true) {
                continue;
            }
            $list = [];
            foreach ($data as $key => $value) {
                $list[$key] = $value;
                if ($key === 'date') {
                    if (empty($value) === false) {
                        $carbon_d = new TargetDate($value);
                        $list[$key] = $carbon_d->getTargetDate('Y-m-d');
                    }
                }

                if ($key === 'created_at') {
                    if (empty($value) === false) {
                        $carbon_c = new TargetDate($data['created_at'], 'UTC');
                        $list[$key] = $carbon_c->getTargetDate('Y-m-d H:i:s');
                    }
                }
            }

            $this->commentList->push($list);
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
     * hasError
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
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
     * getCommentList
     *
     * @return Collection
     */
    public function getCommentList(): Collection
    {
        return $this->commentList;
    }
}
