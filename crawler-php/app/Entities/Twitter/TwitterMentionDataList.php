<?php

namespace App\Entities\Twitter;

// use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TwitterMentionDataList
{
    private static $_instance;

    private ?Collection $mentionList = null;

    private ?TwitterMetaData $metaData = null;


    /**
     * __construct
     *
     * @param  mixed $apiResponse
     * @return void
     */
    private function __construct(array $meta, array $data = [])
    {
        $this->setData($meta, $data);
    }

    /**
     * getInstance
     *
     * @return TwitterMentionDataList
     */
    final public static function getInstance($meta, $data): TwitterMentionDataList
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new TwitterMentionDataList($meta, $data);
        } else {
            self::$_instance->setData($meta, $data);
        }

        return self::$_instance;
    }

    /**
     * getMentionList
     *
     * @return ?Collection
     */
    public function getMentionList(): ?Collection
    {
        return $this->mentionList;
    }

    /**
     * getResultCount
     *
     * @return ?TwitterMetaData
     */
    public function getMetaData(): ?TwitterMetaData
    {
        return $this->metaData;
    }

    public function setData($meta, $data)
    {
        $this->metaData = new TwitterMetaData($meta);
        $this->addMentionList($data);
    }

    /**
     * addMentionList
     *
     * @param  mixed $mentionData
     * @return void
     */
    public function addMentionList(array $mentionData)
    {
        if (empty($mentionData) === true) {
            $mentions = [];
        } else {
            $mentions = array_map(
                function ($item) {
                    $carbon = Carbon::parse($item['created_at']);
                    $item['date'] = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d');

                    return $item;
                },
                $mentionData
            );
        }

        if (is_null($this->mentionList) === true) {
            $this->mentionList = collect($mentions);
        } else {
            foreach ($mentions as $item) {
                $this->mentionList->push($item);
            }
        }
    }
}
