<?php

/*
Plugin Name: Yibby
Plugin URI: http://www.wpcarelab.com
Description: Make your site compatible with mobile apps for authentication and theming
Version: 1.0
Author: Zeb Fross
Author URI: http://www.zebfross.com
License: GPL2
License URI: https://opensource.org/licenses/gpl-2.0.php
*/

namespace Yibby;

class Yibby {

    private function get_var($name, $default = null)
    {
        if (array_key_exists($name, $_GET))
            return $_GET[$name];
        if (array_key_exists($name, $_POST))
            return $_POST[$name];
        return $default;
    }

    public function render_template($file, $data=[], $return = false) {
        return $this->render_file("templates/" . $file, $data, $return);
    }

    public function render_view($file, $data=[], $return=false) {
        return $this->render_file("views/" . $file, $data, $return);
    }

    private function render_file($file, $data = [], $return = false)
    {
        if (!is_array($data))
            $data = (array)$data;

        extract($data);

        ob_start();
        $theme = $file . ".php";
        include($theme); // PHP will be processed
        $output = ob_get_contents();
        @ob_end_clean();
        if ($return)
            return $output;
        print $output;
    }

    static function init_tables() {
    }

    static function remove_tables() {
    }

    public function render_admin_view()
    {
        $this->render_view('header');
    }

    public function register_menu() {
        //add_submenu_page("options-general.php" /*parent_slug*/, "WPCareLab" /*page title*/, "WPCareLab" /*menu title*/, "edit_posts" /*capability*/,  "wpcarelab" /*menu slug*/, array($this, "render_admin_view") /*function*/);
    }

    public function __construct()
    {
        register_activation_hook(__FILE__, 'Yibby\Yibby::init_tables');
        register_uninstall_hook(__FILE__, 'Yibby\Yibby::remove_tables');
        add_action('admin_menu', array($this, 'register_menu'));

        add_filter('jwt_auth_token_before_dispatch', function($data, $user) {
            if (is_wp_error($user))
                return $data;
            assert($user instanceof \WP_User);
            $data['id'] = $user->ID;
            $data['expires'] = time() + 55 * 60;
            if (count($user->roles) > 0)
                $data['role'] = $user->roles[0];
            else
                $data['role'] = "";
            $data['cookie'] = wp_generate_auth_cookie($user->ID, time() + 24 * 60 * 60, 'logged_in');

            return $data;
        }, 10, 2);

        add_filter('jwt_auth_expire', function() {
            return time() + (4 * 60 * 60 /*4 hours*/);
        });

        // set current user based on provided cookie in case of mobile app requests
        add_filter('determine_current_user', function($user) {
            if (empty($_GET['app_token']))
                return $user;

            return wp_validate_auth_cookie($_GET['app_token'], 'logged_in');
        });
    }

    public static function generate_app() {

        $boxes = \CMB2_Boxes::get_all();

        foreach($boxes as $box) {
            if (!str_starts_with($box->cmb_id, 'admin-')) {

                echo $box->cmb_id . PHP_EOL;
            }
        }
    }

}

new Yibby();
