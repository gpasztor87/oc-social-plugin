<?php namespace Autumn\Social\Notifications;

use Autumn\Notifications\Models\Notification;

class PostLiked extends Notification
{
    public static $type = 'post_liked';

    public function render($notification)
    {
        return "{$notification->sender->name} liked {$notification->object->content}";
    }
}