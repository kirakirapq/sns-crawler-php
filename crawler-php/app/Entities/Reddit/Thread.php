<?php

namespace App\Entities\Reddit;

use Carbon\Carbon;

final class Thread
{
    const HOST_NAME = 'www.reddit.com';

    private ?CommentList $commentList = null;
    private string $title;
    private string $text;
    private string $url;

    public function __construct(string $title, string $text, string $url)
    {
        $this->title = $title;
        $this->text = $text;
        $this->url = sprintf('%s.json', rtrim($url, '/'));
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(int $limit = 1000): string
    {
        return sprintf('%s?limit=%s', $this->url, $limit);
    }

    public function setComments(array $children)
    {
        $comments = [];
        foreach ($children as $child) {
            $comment = $child['data'];
            if (empty($getCommentResult = $this->getCommentData($comment)) === false) {
                $comments[] = $getCommentResult;
            }
            // $comments = $this->getProgenyCommentsWithRefarence($comment, $comments);
            $progenyComments = $this->getProgenyComments($comment);
            foreach ($progenyComments ?? [] as $progeny) {
                $comments[] = $progeny;
            }
        }

        $this->commentList = new CommentList($comments);
    }

    public function getCommentList(): CommentList
    {
        return $this->commentList;
    }

    public function getCommentData(array $data)
    {
        if (isset($data['body']) === false) {
            return [];
        }

        $carbon = Carbon::parse($data['created']);

        return [
            'id' => $data['id'],
            'parent_id' => $data['parent_id'],
            'subreddit' => $data['subreddit'],
            'text' => $data['body'],
            'permalink' => $data['permalink'],
            'created' => (int)$data['created'],
            'created_at' => (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d H:i:s'),
            'date' => (new Carbon($carbon, 'Asia/Tokyo'))->format('Y-m-d'),
        ];
    }

    /**
     * getProgeny
     * 参照渡しで子孫のデータを取得
     *
     * @return void
     */
    public function getProgenyCommentsWithRefarence(array $baseData, array &$comments)
    {
        if (isset($baseData['replies']['data']['children']) === true) {
            $replies = $baseData['replies']['data']['children'];
            foreach ($replies as $replie) {
                $comment = $replie['data'];
                $comments[] = $this->getCommentData($comment);
                $comments = $this->getProgenyCommentsWithRefarence($replie, $comments);
            }
        }

        return $comments;
    }

    /**
     * getProgeny
     * 子孫のデータを取得
     *
     * @return void
     */
    public function getProgenyComments(array $baseData)
    {
        $comments = [];
        if (isset($baseData['replies']['data']['children']) === true) {
            $children = $baseData['replies']['data']['children'];
            foreach ($children as $child) {
                $comment = $child['data'];
                $comments[] = $this->getCommentData($comment);

                $progenyComments = $this->getProgenyComments($comment);
                foreach ($progenyComments ?? [] as $progeny) {
                    $comments[] = $progeny;
                }
            }
        }

        return $comments;
    }
}
