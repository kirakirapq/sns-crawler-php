<?php

namespace Unit\Entities\Reddit;

use App\Entities\Reddit\CommentList;
use App\Entities\Reddit\Thread;
use Tests\TestCase;
use \Carbon\Carbon;

class ThreadTest extends TestCase
{
    /**
     * getTitle
     * @test
     *
     * @return void
     */
    public function getTitle(): void
    {
        $expected = $title = 'thread title';

        $model = new Thread($title, 'text', 'url');

        $this->assertEquals($expected, $model->getTitle());
    }

    /**
     * getTitle
     * @test
     *
     * @return void
     */
    public function getText(): void
    {
        $expected = $text = 'text comment';

        $model = new Thread('thread title', $text, 'url');

        $this->assertEquals($expected, $model->getText());
    }

    /**
     * getUrl
     * @test
     * @dataProvider getUrlDataProvider
     *
     * @return void
     */
    public function getUrl(string $url, ?int $limit, string $expected): void
    {
        $model = new Thread('thread title', 'text comment', $url);
        if (is_null($limit) === true) {
            $actual = $model->getUrl();
        } else {
            $actual = $model->getUrl($limit);
        }

        $this->assertEquals($expected, $actual);
    }

    public function getUrlDataProvider(): array
    {
        return [
            'set limit case' => [
                'url' => 'http://huga.hoge/',
                'limit' => 500,
                'expected' => 'http://huga.hoge.json?limit=500'
            ],
            'default limit case' => [
                'url' => 'http://huga.hoge/',
                'limit' => null,
                'expected' => 'http://huga.hoge.json?limit=1000'
            ]
        ];
    }

    /**
     * setComments
     * @test
     * @dataProvider setCommentsDataProvider
     *
     * @param  mixed $children
     * @param  mixed $expected
     * @return void
     */
    public function setComments(array $children, ?CommentList $expected): void
    {
        $model = new Thread('thread title', 'text comment', 'url');
        $model->setComments($children);

        $this->assertEquals($expected, $model->getCommentList());
    }

    public function setCommentsDataProvider(): array
    {
        $created = '2021-10-01';
        $carbon = Carbon::createFromFormat('Y-m-d', $created);
        $timestamp = $carbon->getTimestamp();
        $createdAt = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d H:i:s');
        $date = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d');

        $progenyComments['data']['children'] = [
            [
                'data' => [
                    'id' => 'child-id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'body' => 'comment 1',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                ]
            ],
            [
                'data' => [
                    'id' => 'child-id-2',
                    'parent_id' => 'id-parent-2',
                    'subreddit' => 'page name 2',
                    'body' => 'comment 2',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                ]
            ],
        ];

        return [
            'not filter and empty progenyComments  case' => [
                'children' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'body' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                        ]
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'body' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                        ]
                    ],
                ],
                'expected' => new CommentList(
                    [
                        [
                            'id' => 'id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'text' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'text' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ]
                    ]
                ),
            ],
            'not filter and not empty progenyComments  case' => [
                'children' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'body' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'replies' => $progenyComments,

                        ]
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'body' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'replies' => $progenyComments,
                        ]
                    ],
                    [
                        'data' => [
                            'id' => 'id-3',
                            'parent_id' => 'id-parent-3',
                            'subreddit' => 'page name 3',
                            'body' => 'comment 3',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'replies' => null,
                        ]
                    ],
                ],
                'expected' => new CommentList(
                    [
                        [
                            'id' => 'id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'text' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'text' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'text' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'text' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'text' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'text' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'id-3',
                            'parent_id' => 'id-parent-3',
                            'subreddit' => 'page name 3',
                            'text' => 'comment 3',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                    ]
                ),
            ],
            'filter by empty data and empty progenyComments case' => [
                'children' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                        ]
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'body' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                        ]
                    ],
                ],
                'expected' => new CommentList(
                    [
                        [
                            'id' => 'id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'text' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ]
                    ]
                ),
            ],
            'filter by empty data and progenyComments case' => [
                'children' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'body' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'replies' => $progenyComments,

                        ]
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'replies' => $progenyComments,
                        ]
                    ],
                    [
                        'data' => [
                            'id' => 'id-3',
                            'parent_id' => 'id-parent-3',
                            'subreddit' => 'page name 3',
                            'body' => 'comment 3',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'replies' => null,
                        ]
                    ],
                ],
                'expected' => new CommentList(
                    [
                        [
                            'id' => 'id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'text' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'text' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'text' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-1',
                            'parent_id' => 'id-parent-1',
                            'subreddit' => 'page name 1',
                            'text' => 'comment 1',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'child-id-2',
                            'parent_id' => 'id-parent-2',
                            'subreddit' => 'page name 2',
                            'text' => 'comment 2',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                        [
                            'id' => 'id-3',
                            'parent_id' => 'id-parent-3',
                            'subreddit' => 'page name 3',
                            'text' => 'comment 3',
                            'permalink' => '/thread',
                            'created' => $timestamp,
                            'created_at' => $createdAt,
                            'date' => $date,
                        ],
                    ]
                ),
            ],
        ];
    }

    /**
     * getCommentData
     * @test
     * @dataProvider getCommentDataProvider
     *
     * @param  mixed $comment
     * @param  mixed $expected
     * @return void
     */
    public function getCommentData(array $comment, array $expected): void
    {
        $model = new Thread('thread title', 'text comment', 'url');

        $this->assertEquals($expected, $model->getCommentData($comment));
    }

    public function getCommentDataProvider(): array
    {
        $created = '2021-10-01';
        $carbon = Carbon::createFromFormat('Y-m-d', $created);
        $timestamp = $carbon->getTimestamp();
        $createdAt = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d H:i:s');
        $date = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d');

        return [
            'not exists body case' => [
                'comment' => [
                    'id' => 'id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'permalink' => '/thread',
                    'created' => $timestamp,

                ],
                'expected' => [],
            ],
            'empty body case' => [
                'comment' => [
                    'id' => 'id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'body' => '',
                    'permalink' => '/thread',
                    'created' => $timestamp,

                ],
                'expected' => [
                    'id' => 'id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'text' => '',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                    'created_at' => $createdAt,
                    'date' => $date,
                ],
            ],
            'normal case' => [
                'comment' => [
                    'id' => 'id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'body' => 'body 1',
                    'permalink' => '/thread',
                    'created' => $timestamp,

                ],
                'expected' => [
                    'id' => 'id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'text' => 'body 1',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                    'created_at' => $createdAt,
                    'date' => $date,
                ],
            ],
        ];
    }

    /**
     * getProgenyCommentsWithRefarence
     * @test
     * @dataProvider getProgenyCommentsWithRefarenceDataProvider
     *
     * @param  mixed $replies
     * @param  mixed $comments
     * @param  mixed $expected
     * @return void
     */
    public function getProgenyCommentsWithRefarence(array $replies, array $comments, array $expected): void
    {
        $model = new Thread('thread title', 'text comment', 'url');
        $comments = $model->getProgenyCommentsWithRefarence($replies, $comments);

        $this->assertEquals($expected, $comments);
        unset($comments);
    }

    public function getProgenyCommentsWithRefarenceDataProvider(): array
    {
        $created = '2021-10-01';
        $carbon = Carbon::createFromFormat('Y-m-d', $created);
        $timestamp = $carbon->getTimestamp();
        $createdAt = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d H:i:s');
        $date = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d');

        $progenyComments['data']['children'] = [
            [
                'data' => [
                    'id' => 'child-id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'body' => 'comment 1',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                ]
            ],
            [
                'data' => [
                    'id' => 'child-id-2',
                    'parent_id' => 'id-parent-2',
                    'subreddit' => 'page name 2',
                    'body' => 'comment 2',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                ]
            ],
        ];

        return [
            'not exists replies case' => [
                'replies' => [
                    'replies' => [],
                ],
                'comments' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'id-parent-1',
                        'subreddit' => 'page name 1',
                        'text' => 'body 1',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ]
                ],
                'expected' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'id-parent-1',
                        'subreddit' => 'page name 1',
                        'text' => 'body 1',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ]
                ]
            ],
            'exists replies case' => [
                'replies' => [
                    'replies' => $progenyComments,
                ],
                'comments' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'id-parent-1',
                        'subreddit' => 'page name 1',
                        'text' => 'body 1',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ]
                ],
                'expected' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'id-parent-1',
                        'subreddit' => 'page name 1',
                        'text' => 'body 1',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ],
                    [
                        'id' => 'child-id-1',
                        'parent_id' => 'id-parent-1',
                        'subreddit' => 'page name 1',
                        'text' => 'comment 1',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ],
                    [
                        'id' => 'child-id-2',
                        'parent_id' => 'id-parent-2',
                        'subreddit' => 'page name 2',
                        'text' => 'comment 2',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ]
                ]
            ],
        ];
    }

    /**
     * getProgenyComments
     * @test
     * @dataProvider getProgenyCommentsDataProvider
     *
     * @param  mixed $replies
     * @param  mixed $expected
     * @return void
     */
    public function getProgenyComments(array $replies, array $expected): void
    {
        $model = new Thread('thread title', 'text comment', 'url');
        $actual = $model->getProgenyComments($replies);

        $this->assertEquals($expected, $actual);
    }

    public function getProgenyCommentsDataProvider(): array
    {
        $created = '2021-10-01';
        $carbon = Carbon::createFromFormat('Y-m-d', $created);
        $timestamp = $carbon->getTimestamp();
        $createdAt = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d H:i:s');
        $date = (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d');

        $progenyComments['data']['children'] = [
            [
                'data' => [
                    'id' => 'child-id-1',
                    'parent_id' => 'id-parent-1',
                    'subreddit' => 'page name 1',
                    'body' => 'comment 1',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                ]
            ],
            [
                'data' => [
                    'id' => 'child-id-2',
                    'parent_id' => 'id-parent-2',
                    'subreddit' => 'page name 2',
                    'body' => 'comment 2',
                    'permalink' => '/thread',
                    'created' => $timestamp,
                ]
            ],
        ];

        return [
            'not exists replies case' => [
                'replies' => [
                    'replies' => [],
                ],
                'expected' => []
            ],
            'exists replies case' => [
                'replies' => [
                    'replies' => $progenyComments,
                ],
                'expected' => [
                    [
                        'id' => 'child-id-1',
                        'parent_id' => 'id-parent-1',
                        'subreddit' => 'page name 1',
                        'text' => 'comment 1',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ],
                    [
                        'id' => 'child-id-2',
                        'parent_id' => 'id-parent-2',
                        'subreddit' => 'page name 2',
                        'text' => 'comment 2',
                        'permalink' => '/thread',
                        'created' => $timestamp,
                        'created_at' => $createdAt,
                        'date' => $date,
                    ]
                ]
            ],
        ];
    }
}
