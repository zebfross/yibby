<?php

namespace Yibby\Api;

class BaseApi
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRoutes'));
    }

    protected function registerRoute($route, $function, $method = "GET", $permission = '__return_true')
    {
        register_rest_route('yibby/v1', $route, array(
            'methods' => $method,
            'callback' => array($this, $function),
            'permission_callback' => $permission
        ));
    }

    public function registerRoutes()
    {
    }

    protected function error_not_found() {
        return new \WP_Error('Not Found', '', array('status' => 404));
    }

    protected function error_unauthorized() {
        return new \WP_Error('Unauthorized', '', array('status' => 403));
    }

    protected function error_unauthenticated() {
        return new \WP_Error('Unauthenticated', '', array('status' => 403));
    }

    protected function error_malformed($message="Input is incorrect") {
        return new \WP_Error($message, '', array('status' => 400));
    }
}
