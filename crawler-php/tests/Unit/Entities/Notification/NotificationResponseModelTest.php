<?php

namespace Unit\Entities\Notification;

use App\Entities\Notification\NotificationResponseModel;
use Tests\TestCase;

class NotificationResponseModelTest extends TestCase
{
    /**
     * getBody
     * @test
     *
     * @return void
     */
    public function getBody(): void
    {
        $model = new NotificationResponseModel([123, 456]);

        $this->assertEquals([123, 456], $model->getBody());
    }
}
