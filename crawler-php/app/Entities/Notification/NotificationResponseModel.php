<?php

namespace App\Entities\Notification;

/**
 * InnerApiResponse
 * ResponseData
 */
class NotificationResponseModel
{
    private array $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    /**
     * getBody
     *
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }
}
