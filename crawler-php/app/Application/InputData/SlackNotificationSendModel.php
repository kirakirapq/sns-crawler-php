<?php

namespace App\Application\InputData;

use Illuminate\Support\Facades\Config;

final class SlackNotificationSendModel implements NotificationSendModel
{
    public function __construct(string $sns, string $appName, string $language, array $texts)
    {
        $this->sns = $sns;
        $this->appName = $appName;
        $this->language = $language;
        $this->texts = $texts;
    }

    public function getAddress(): string
    {
        return Config::get('app.SLACK_URL');
    }

    public function getMessage(): array
    {
        $channel = sprintf('#%s', Config::get('app.SLACK_CHANNEL_NAME'));
        $username = 'wwo-crawler';

        return [
            'channel' => $channel,
            'username' => $username,
            'text' => $this->getTitle(),
            'blocks' => $this->getBlocks(),
        ];
    }

    public function getTitle(): string
    {
        return 'Risk word detection.';
    }

    public function getSubTitle(): string
    {
        return sprintf(
            'SNS: %s, App: %s, language: %s',
            $this->sns,
            $this->appName,
            $this->language
        );
    }

    public function getBlocks(): array
    {
        $blocks[] = [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => $this->getTitle(),
            ],
        ];

        $blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => 'plain_text',
                'text' => $this->getSubTitle(),
            ],
        ];

        $num = count($blocks);
        foreach ($this->texts as $i => $data) {
            $num += $i;

            $blocks[$num] = [
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
                ],
            ];
            if (empty($data['id']) === false) {
                $blocks[$num]['fields'][] = [
                    'type' => 'mrkdwn',
                    'text' => 'id',
                ];

                $blocks[$num]['fields'][] = [
                    'type' => 'mrkdwn',
                    'text' => $data['id'],
                ];
            }

            if (empty($data['text']) === false) {
                $blocks[$num]['fields'][] = [
                    'type' => 'mrkdwn',
                    'text' => 'text',

                ];

                $blocks[$num]['fields'][] = [
                    'type' => 'plain_text',
                    'text' => $data['text'],
                ];
            }

            if (empty($data['translated']) === false) {
                $blocks[$num]['fields'][] = [
                    'type' => 'mrkdwn',
                    'text' => 'translated',
                ];

                $blocks[$num]['fields'][] = [
                    'type' => 'plain_text',
                    'text' => $data['translated'],
                ];
            }

            if (empty($data['created_at']) === false) {
                $blocks[$num]['fields'][] = [
                    'type' => 'mrkdwn',
                    'text' => 'created_at',
                ];

                $blocks[$num]['fields'][] = [
                    'type' => 'mrkdwn',
                    'text' => $data['created_at'],
                ];
            }
        }

        return $blocks;
    }
}
