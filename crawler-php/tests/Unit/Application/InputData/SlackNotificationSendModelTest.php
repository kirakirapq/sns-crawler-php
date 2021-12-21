<?php

namespace Unit\Application\InputData;

use App\Application\InputData\SlackNotificationSendModel;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use \Mockery;

class SlackNotificationSendModelTest extends TestCase
{
    /**
     * getAddress
     * @test
     *
     * @return void
     */
    public function getAddress(): void
    {
        $url = 'exampleurl';
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($url)
            ->times(1);

        $model = new SlackNotificationSendModel(
            'twitter',
            'kms',
            'en',
            []
        );

        $this->assertEquals($url, $model->getAddress());
    }

    /**
     * getTitle
     * @test
     * @dataProvider getSubTitleDataProvider
     *
     * @param  mixed $sns
     * @param  mixed $appName
     * @param  mixed $language
     * @param  mixed $expected
     * @return void
     */
    public function getTitle($sns, $appName, $language, $expected): void
    {
        $model = new SlackNotificationSendModel(
            $sns,
            $appName,
            $language,
            []
        );

        $this->assertIsString($model->getTitle());
    }

    /**
     * getSubTitle
     * @test
     * @dataProvider getSubTitleDataProvider
     *
     * @param  mixed $sns
     * @param  mixed $appName
     * @param  mixed $language
     * @param  mixed $expected
     * @return void
     */
    public function getSubTitle($sns, $appName, $language, $expected): void
    {
        $model = new SlackNotificationSendModel(
            $sns,
            $appName,
            $language,
            []
        );

        $this->assertEquals($expected, $model->getSubTitle());
    }

    public function getSubTitleDataProvider(): array
    {
        return [
            'test case' => [
                'sns' => 'twitter',
                'appName' => 'kms',
                'language' => 'en',
                'expected' => sprintf('SNS: %s, App: %s, language: %s', 'twitter', 'kms', 'en'),
            ],
        ];
    }

    /**
     * getBlocks
     * @test
     * @dataProvider getFieldsDataProvider
     *
     * @param  mixed $sns
     * @param  mixed $appName
     * @param  mixed $language
     * @param  mixed $texts
     * @param  mixed $expected
     * @return void
     */
    public function getBlocks($sns, $appName, $language, $texts, $expected): void
    {
        $model = new SlackNotificationSendModel(
            $sns,
            $appName,
            $language,
            $texts
        );

        $this->assertEquals($expected, $model->getBlocks());
    }

    public function getFieldsDataProvider(): array
    {
        return [
            'test case' => [
                'sns' => 'twitter',
                'appName' => 'kms',
                'language' => 'en',
                'texts' => [
                    [
                        'text' => 't1',
                        'translated' => 't1',
                    ],
                    [
                        'id' => 'id2',
                        'text' => 't2',
                        'translated' => 't2',
                    ]
                ],
                'expected' => [

                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Risk word detection.',
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'SNS: twitter, App: kms, language: en',
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Field*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Value*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'text',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't1',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'translated',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't1',
                            ],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Field*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Value*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'id',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'id2',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'text',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't2',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'translated',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't2',
                            ],
                        ],
                    ]
                ]
            ],
        ];
    }

    /**
     * getMessage
     * @test
     * @dataProvider getMessageDataProvider
     *
     * @param  mixed $sns
     * @param  mixed $appName
     * @param  mixed $language
     * @param  mixed $texts
     * @param  mixed $expected
     * @return void
     */
    public function getMessage($sns, $appName, $language, $texts, $channelName, $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($channelName)
            ->times(1);

        $model = new SlackNotificationSendModel(
            $sns,
            $appName,
            $language,
            $texts
        );

        $this->assertEquals($expected, $model->getMessage());
    }

    public function getMessageDataProvider(): array
    {
        $channelName = 'wfs-sns-alert-test';
        $channel = sprintf('#%s', $channelName);
        $username = 'wwo-crawler';

        $expected =
            [
                'channel' => $channel,
                'username' => $username,
                'text' => 'Risk word detection.',
                'blocks' => [

                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Risk word detection.',
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'SNS: twitter, App: kms, language: en',
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Field*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Value*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'text',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't1',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'translated',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't1',
                            ],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Field*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => '*Value*',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'id',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'id2',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'text',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't2',
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => 'translated',
                            ],
                            [
                                'type' => 'plain_text',
                                'text' => 't2',
                            ],
                        ],
                    ]
                ],
            ];

        return [
            'test case' => [
                'sns' => 'twitter',
                'appName' => 'kms',
                'language' => 'en',
                'texts' => [
                    [
                        'text' => 't1',
                        'translated' => 't1',
                    ],
                    [
                        'id' => 'id2',
                        'text' => 't2',
                        'translated' => 't2',
                    ]
                ],
                'channelName' => $channelName,
                'expected' => $expected,
            ],
        ];
    }
}
