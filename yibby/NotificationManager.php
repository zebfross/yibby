<?php

namespace Yibby;

use BracketSpace\Notification\Interfaces\Triggerable;
use BracketSpace\Notification\Abstracts;
use BracketSpace\Notification\Defaults\Field;
use Yibby\Api\UsersApi;

/**
 * ExampleCarrier Carrier
 */
class NotificationManager extends Abstracts\Carrier {

    /**
     * Carrier icon, optional
     *
     * @var string SVG
     */
    public $icon = '<svg viewBox="0 100 5 100">
<rect xmlns="http://www.w3.org/2000/svg" data-v-fde0c5aa="" fill="#400B2C" x="0" y="0" width="300px" height="300px" class="logo-background-square"/>
<g xmlns="http://www.w3.org/2000/svg" data-v-fde0c5aa="" id="7aa24430-e4bd-46e8-9557-da097daabc0f" stroke="none" fill="#E8E8EE" transform="matrix(2.4638854611139753,0,0,2.4638854611139753,33.027204333026994,110.5778326221764)"><path d="M2.83 18.247l26.34-9.124L2.83 0zM29.17 32V13.753L2.83 22.877z"/></g>
</svg>';

    /**
     * Carrier constructor
     */
    public function __construct() {
        // Provide the slug and translatable name.
        parent::__construct( 'yibby-push', __( 'Yibby Push Notifications', 'textdomain' ) );
    }

    /**
     * Used to register Carrier form fields
     * Uses $this->add_form_field();
     *
     * @return void
     */
    public function form_fields() {

        $this->add_form_field( new Field\InputField( [
            'label' => __( 'Example field', 'notification' ),
            'name'  => 'fieldslug',
        ] ) );

        // Special field which renders all Carrier's recipients.
        // You may override name, slug and description here.
        $this->add_recipients_field( [
            'name' => 'Items',
            'slug' => 'items',
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
        $data['parsed_recipients'];

        // This is where you should connect with your service to send out the Notifiation.
    }

    public static function registerPush($token, $user_id) {
        if (!$user_id)
            return false;

        global $wpdb;
        $key = UsersApi::$key;
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
        delete_user_meta($user_id, UsersApi::$key, $token);
    }

}