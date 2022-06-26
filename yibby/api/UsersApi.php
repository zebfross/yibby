<?php

namespace Yibby\Api;

use Kreait\Firebase\Factory;
use Yibby\NotificationManager;
use Yibby\Yibby;

class UsersApi extends BaseApi
{

    public function registerPushNotifications(\WP_REST_Request $request) {
        if (!is_user_logged_in()) {
            return $this->error_unauthenticated();
        }

        $token = get_var('push_token');
        if (!$token)
            $token = $request->get_param('push_token');
        if (!$token) {
            Yibby::error_log("invalid token");
            return $this->error_malformed("Invalid token");
        }

        NotificationManager::registerPush($token, get_current_user_id());
    }

    public function unregisterToken(\WP_REST_Request $request) {
        if (!is_user_logged_in()) {
            return $this->error_unauthenticated();
        }

        $token = get_var('push_token');
        if (!$token)
            $token = $request->get_param('push_token');
        if (!$token) {
            return $this->error_malformed("Invalid token");
        }

        NotificationManager::unregisterPush($token, get_current_user_id());
    }

    public function registerRoutes()
    {
        $this->registerRoute('push/register', 'registerPushNotifications', 'POST');
        $this->registerRoute('push/unregister', 'unregisterToken', 'POST');
    }
}

