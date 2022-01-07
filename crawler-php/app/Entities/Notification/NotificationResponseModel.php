<?php

namespace App\Entities\Notification;

/**
 * InnerApiResponse
 * ResponseData
 */
final class NotificationResponseModel
{
    public function __construct(public array $body)
    {
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
