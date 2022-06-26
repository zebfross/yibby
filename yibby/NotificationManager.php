<?php

namespace Yibby;

use BracketSpace\Notification\Register;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory;
use \BracketSpace\Notification\Interfaces\Triggerable;
use \BracketSpace\Notification\Abstracts;
use \BracketSpace\Notification\Defaults\Field;
use Yibby\Api\UsersApi;

/**
 * ExampleCarrier Carrier
 */
class NotificationManager extends Abstracts\Carrier {

    private static $_instance = null;
    /**
     * Carrier icon, optional
     *
     * @var string SVG
     */
    public $icon = '<svg viewBox="0 50 150 150">
<rect xmlns="http://www.w3.org/2000/svg" data-v-fde0c5aa="" fill="#fff" x="0" y="0" width="150px" height="150px" class="logo-background-square"/>
<g xmlns="http://www.w3.org/2000/svg" data-v-fde0c5aa="" id="7aa24430-e4bd-46e8-9557-da097daabc0f" stroke="none" fill="#E8E8EE" transform="matrix(2.4638854611139753,0,0,2.4638854611139753,33.027204333026994,110.5778326221764)"><path d="M2.83 18.247l26.34-9.124L2.83 0zM29.17 32V13.753L2.83 22.877z"/></g>
</svg>';
    public $slug = 'yibby-push';
    public static $token_key = "yibby_push_notification_tokens";

    /**
     * Carrier constructor
     */
    private function __construct() {
        // Provide the slug and translatable name.
        parent::__construct( $this->$slug, __( 'Yibby Push Notifications', 'textdomain' ) );

        add_shortcode('yibby_notification_list', array(&$this, 'notification_list'));
    }

    public static function instance() {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
            Register::carrier(self::$_instance);
            Register::recipient(self::$_instance->slug, new PushRecipient());
        }

        return self::$_instance;
    }

    public function notification_list_friendly($limit=25, $skip=0, $ifEmpty="") {
        return $this->notification_list([
            'limit' => $limit,
            'skip' => $skip
        ], $ifEmpty);
    }

    public function notification_list($atts, $content="") {
        $defaults = [
            'limit' => 25,
            'skip' => 0,
        ];

        $atts = shortcode_atts($defaults, $atts, "yibby_notification_list");

        $notifications = self::getNotifications($atts['limit'], $atts['skip'], get_current_user_id());

        if (empty($notifications) && !empty($content))
            return $content;
        return Yibby::render_view('notification-list', ['notifications' => $notifications], true);
    }

    /**
     * Used to register Carrier form fields
     * Uses $this->add_form_field();
     *
     * @return void
     */
    public function form_fields() {

        /*$this->add_form_field( new Field\InputField( [
            'label' => __( 'Subject', 'notification' ),
            'name'  => 'subject',
        ] ) );*/

        $body_field = new Field\CodeEditorField( [
            'label'      => __( 'Body', 'notification' ),
            'name'       => 'body',
            'resolvable' => true,
            'settings'   => [
                'mode'        => 'text',
                'lineNumbers' => false,
            ],
        ] );

        $this->add_form_field($body_field);

        $this->add_form_field( new Field\SelectField( [
            'label' => __( 'Icon', 'notification' ),
            'name'  => 'icon',
            'description' => 'Displayed in Message Center',
            'options' => [
                '' => __( 'None', 'notification' ),
                'bi-bell-fill' => __( 'Bell', 'notification' ),
                'bi-link-45deg' => __( 'Chain Link', 'notification' ),
                'bi-chat-left-fill' => __( 'Chat Quote Block', 'notification' ),
                'bi-envelope' => __( 'Envelope', 'notification' ),
                'bi-exclamation-circle-fill' => 'Exclamation Circle',
            ],
        ] ) );

        $this->add_form_field( new Field\SelectField( [
            'label' => __( 'Color', 'notification' ),
            'name'  => 'color',
            'description' => 'Displayed in Message Center',
            'options' => [
                'btn-light-green' => 'Green',
                'btn-light-orange' => 'Orange',
                'btn-light-red' => 'Red',
                'btn-light-purple' => 'Purple',
            ]
        ] ) );

        $this->add_form_field( new Field\CheckboxField( [
            'label'          => __( 'Only Add Notification In Message Center', 'notification' ),
            'name'           => 'skip_push',
            'checkbox_label' => __( 'Skip Push Notification', 'notification' ),
        ] ) );

        // Special field which renders all Carrier's recipients.
        // You may override name, slug and description here.
        $this->add_recipients_field( [
            'name' => 'Recipients',
            'slug' => 'recipients',
        ] );

    }

    /**
     * Sends the notification
     *
     * @param  Triggerable $trigger trigger object.
     * @return void
     */
    public function send( Triggerable $trigger ) {
        // Data contains the user data with rendered Merge Tags.
        $data = $this->data;

        // Parsed recipients are also available.
        $recipients = $data['parsed_Recipients'];

        foreach($recipients as $recipient) {
            if ($recipient == "author" && isset($trigger->author_id)) {
                $recipient = $trigger->author_id;
            } else if ($recipient == "admin") {
                $recipient = 1;
            }
            if (empty($recipient))
                continue;
            $post_id = 0;
            $href = "";
            if (isset($trigger->post_id)) {
                $post_id = $trigger->post_id;
            }
            if (isset($trigger->link)) {
                $href = $trigger->link;
            }
            // This is where you should connect with your service to send out the Notifiation.

            $notif = \Yibby\Models\Notification::fromCarrier($data, $post_id, $recipient, $href);

            wp_insert_comment($notif->asDatabaseEntry());

            if (!empty($data['skip_push']))
                continue;

            $this->sendPush($notif->subject, $notif->body, $recipient, $post_id);
        }

    }


    /**
     * @param string $subject
     * @param string $body
     * @param int $to_user_id - defaults to current user
     */
    public function sendPush($subject, $body, $to_user_id=0, $object_id=0) {
        if ($to_user_id == 0)
        {
            if (!is_user_logged_in())
                return;
            $to_user_id = get_current_user_id();
        }

        $deviceTokens = get_user_meta($to_user_id, self::$token_key, false);

        if (!$deviceTokens) {
            return;
        }

        if ((defined('IN_UNIT_TESTS') && IN_UNIT_TESTS) || (defined('APP_CONFIG') && APP_CONFIG == 'beta')) {
            return;
        }

        try {
            Yibby::error_log("Sending push $subject $body");
            $factory = (new Factory)->withServiceAccount(SettingsManager::get_option('firebase_credentials'));
            $messaging = $factory->createMessaging();

            $message = CloudMessage::fromArray([
                'notification' => Notification::create($subject, $body),
                'data' => [
                    'type' => $object_id
                ], // optional
            ]);

            $report = $messaging->sendMulticast($message, $deviceTokens);
            Yibby::error_log("Multicast send report", $report);
            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    Yibby::error_log("", $failure->error()->getMessage());
                }
            }
            $unknownTargets = $report->unknownTokens();

            foreach($unknownTargets as $invalid_token) {
                Yibby::error_log("unregistering $invalid_token $to_user_id");
                self::unregisterPush($invalid_token, $to_user_id);
            }
        } catch (MessagingException | FirebaseException $e) {
            Yibby::error_log("", $e);
        }
    }

    public static function hasUnreadNotifications($user_id=0) {
        $notifs = self::getNotifications(25, 0, $user_id, true);
        return !empty($notifs) && !$notifs[0]->read;
    }

    /**
     * @param int $limit
     * @param int $page
     * @param int $user_id
     * @param false $disableLastReadUpdate
     * @return \Yibby\Models\Notification[]
     */
    public static function getNotifications($limit=25, $page=0, $user_id=0, $disableLastReadUpdate=false) {
        if (empty($user_id))
            $user_id = get_current_user_id();

        $cache_key = 'notifications-' . $limit . '-' . $page . '-' . $user_id;
        $cached = wp_cache_get($cache_key);
        if (empty($cached)) {

            //set the arguments
            $args = array(
                'orderby' => 'date',
                'status' => 'approve',
                'order' => 'DESC',
                'type' => 'yibby-notification',
                'posts_per_page' => $limit,
                'offset' => $page,
                'user_id' => $user_id
            );
            // get the comments using the arguments
            $comments = get_comments($args);

            $last_read = self::getLastReadTime($user_id);
            if (!$disableLastReadUpdate && $page == 0) {
                //self::updateLastReadTime($user_id);
            }

            $cached = [];
            foreach ($comments as $notification) {
                $cached[] = \Yibby\Models\Notification::fromComment($notification, $last_read);
            }

            wp_cache_set($cache_key, $cached);
        }

        return $cached;
    }

    public static function getLastReadTime($user_id) {

        $last_read_date = get_user_meta($user_id, 'yibby-last-read-time', true);

        if (!empty($last_read_date))
            return new \DateTime($last_read_date);

        return (new \DateTime())->modify('-3 months');
    }

    public static function updateLastReadTime($user_id=0) {
        if (empty($user_id))
            $user_id = get_current_user_id();
        if (empty($user_id))
            return;
        $now = new \DateTime();
        update_user_meta($user_id, 'yibby-last-read-time', $now->format('c'));
    }

    public static function registerPush($token, $user_id) {
        if (!$user_id)
            return false;

        global $wpdb;
        $key = self::$token_key;
        $result = $wpdb->get_results($wpdb->prepare("insert into {$wpdb->prefix}usermeta (user_id, meta_key, meta_value)
select %d, '$key', %s from dual 
where not exists (select 1 from {$wpdb->prefix}usermeta m where m.user_id=%d and m.meta_key='$key' and m.meta_value=%s)
     ", $user_id, $token, $user_id, $token));

        $factory = (new Factory)->withServiceAccount(SettingsManager::get_option('firebase_credentials'));
        $messaging = $factory->createMessaging();
        if (is_user_in_role('seller', $user_id)) {
            $result = $messaging->subscribeToTopic('seller', $token);
        } else if (is_user_in_role('buyer', $user_id)) {
            $result = $messaging->subscribeToTopic('buyer', $token);
        }

        return $result;
    }

    public static function unregisterPush($token, $user_id) {
        delete_user_meta($user_id, self::$token_key, $token);
    }

}