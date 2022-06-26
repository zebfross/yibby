<?php

namespace Yibby\Models;

use \BracketSpace\Notification\Abstracts;
use Yibby\NotificationManager;

class Notification
{
    public $link;
    public $body;
    public $date;
    public $read;
    public $icon;
    public $post_id;
    public $user_id;
    public $color;

    public function __construct($post_id=0, $user_id=0, $link="", $body="", $date=null, $read=false, $icon="", $color="")
    {
        $this->post_id = $post_id;
        $this->link = $link;
        $this->body = $body;
        $this->read = !!$read;
        $this->icon = $icon;
        $this->color = $color;
        if (empty($date))
            $this->date = new \DateTime();
        else
            $this->date = new \DateTime($date);

        if (empty($user_id))
            $this->user_id = get_current_user_id();
        else
            $this->user_id = $user_id;
    }

    /**
     * @param $notif \WP_Comment
     * @return Notification
     */
    public static function fromComment($notif, $last_read_date) {
        return new self(
            $notif->comment_post_ID,
            $notif->user_id,
        $notif->comment_author_url . "",
        $notif->comment_content,
            $notif->comment_date_gmt,
            new \DateTime($notif->comment_date_gmt) < $last_read_date,
        $notif->comment_author . "",
        $notif->comment_author_email . "");

    }

    /**
     * @param $manager
     */
    public static function fromCarrier($manager, $post_id, $user_id, $link="") {
        return new self(
            $post_id,
            $user_id,
            $link,
            $manager["body"],
            null,
            false,
            $manager["icon"],
            $manager["color"]);
    }

    public function friendly_date() {
        return human_time_diff($this->date->getTimestamp(), (new \DateTime())->getTimestamp());
    }

    /**
     * Gets this notification formatted as WP_Comment data for database insert
     * @return array
     */
    public function asDatabaseEntry() {
        return [
            'comment_content' => $this->body,
            'comment_post_ID' => $this->post_id,
            'comment_type' => "yibby-notification",
            'user_id' => $this->user_id,
            'comment_author_url' => $this->link,
            'comment_author' => $this->icon,
            'comment_author_email' => $this->color,
        ];
    }
}