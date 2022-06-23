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

require_once __DIR__ . "/FieldTemplates.php";

class Yibby {

    public static $slug = "yibby";

    private function get_var($name, $default = null)
    {
        if (array_key_exists($name, $_GET))
            return $_GET[$name];
        if (array_key_exists($name, $_POST))
            return $_POST[$name];
        return $default;
    }

    public static function render_template($file, $data=[], $return = false) {
        return self::render_file("templates/" . $file, $data, $return);
    }

    public static function render_view($file, $data=[], $return=false) {
        return self::render_file("views/" . $file, $data, $return);
    }

    private static function render_file($file, $data = [], $return = false)
    {
        if (!is_array($data))
            $data = (array)$data;

        extract($data);

        ob_start();
        $path = 'templates/' . $file;
        //if (file_exists($path))
            include($path); // PHP will be processed
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

    private static function error_log($message) {
        echo "ERROR: " . $message;
    }
    
    private static function friendlyName($name) {
        return ucwords(str_replace("-", " ", $name));
    }

    private static function upperCaseName($name) {
        return str_replace(" ", "", self::friendlyName($name));
    }

    private static function writeFile($inpath, $data, $outpath, $suffix, $overwrite=false) {
        if (!$overwrite && file_exists($outpath . $suffix))
            return;
        $output = self::render_file($inpath . $suffix, $data, true);
        mkdir(dirname($outpath), 0755, true);
        file_put_contents($outpath . $suffix, $output);
    }

    public static function get_field_display($field) {

        $display = apply_filters('yibby_cmb2_field_display_' . $field['type'], '', $field);
        if (empty($display)) {
            self::error_log("Unknown field type for " . print_r($field, true));
        }

        return $display;
    }

    /**
     * @param $template
     * @param $form \CMB2
     * @param $path
     */
    public static function generate_page($template, $form, $path) {
        $name = $form->cmb_id;
        $inpath = 'pages/' . $template;
        $outpath = path_join($path, 'pages/' . $name . '/' . $name);
        $componentIn = 'pages/component/' . $template;
        $componentOut = path_join($path, 'pages/' . $name . '/form/' . $name);

        $data = [
            'name' => $name,
            'upperName' => self::upperCaseName($name),
            'friendlyName' => self::friendlyName($name),
            'fields_html' => "",
            'groupFields' => []
        ];

        foreach($form->prop('fields') as $field) {
            if (str_contains($field['id'], '-'))
                self::error_log("invalid field name!!! " . $field['id']);
            if ($field['type'] == 'group')
                $data['groupFields'][] = $field;
            $data['fields_html'] .= self::get_field_display($field);
        }

        // generate page
        self::writeFile($inpath, $data, $outpath, '.module.ts');
        self::writeFile($inpath, $data, $outpath, '.page.html', true);
        self::writeFile($inpath, $data, $outpath, '.page.scss');
        self::writeFile($inpath, $data, $outpath, '.page.spec.ts');
        self::writeFile($inpath, $data, $outpath, '.page.ts');
        self::writeFile($inpath, $data, $outpath, '-routing.module.ts');

        echo "{
    path: '$name',
    loadChildren: () => import('./pages/$name/$name.module').then( m => m.{$data['upperName']}PageModule)
  },
  ";
    }

    public static function generate_service($template, $name, $path) {
        $pathrel = 'services/' . $name . '.service.';
        $inpath = 'services/' . $template . '.service.';
        $outpath = path_join($path, $pathrel);
        $data = ['name' => $name, 'upperName' => self::upperCaseName($name)];
        self::writeFile($inpath, $data, $outpath, 'ts');
        self::writeFile($inpath, $data, $outpath, 'spec.ts');
    }

    /**
     * @param $prop \ReflectionProperty
     * @return array
     */
    private static function toTsType($prop) {

        $data = [
            'type' => ($prop->hasType() ? $prop->getType()->getName() : "string"),
            'default' => $prop->getDefaultValue()
        ];

        if ($data['type'] == 'string') {
            $data['default'] = "''";
        }

        return $data;
    }

    public static function generate_model($path, $model) {
        $data = [
            'name' => '',
            'upperName' => '',
            'object' => $model,
            'props' => []
        ];

        try {
            $reflect = new \ReflectionClass($model);
            $data['upperName'] = basename($reflect->getName());
            $data['name'] = strtolower($data['upperName']);
        } catch (ReflectionException $e) {
            return;
        }

        foreach($model as $key => $value) {
            try {
                $prop = new \ReflectionProperty($model, $key);
                if (!$prop->isStatic())
                    $data['props'][$prop->getName()] = self::toTsType($prop);
            } catch (ReflectionException $e) {

            }
        }

        $inpath = 'models/model';
        $outpath = path_join($path, 'models/' . $data['name']);
        self::writeFile($inpath, $data, $outpath, '.ts');
    }

    public static function generate_app($path) {
        new FieldTemplates();

        $boxes = \CMB2_Boxes::get_all();

        self::generate_service('general', 'wordpress', $path);

        foreach($boxes as $box) {
            if (!str_starts_with($box->cmb_id, 'admin-')) {
                self::generate_page('page', $box, $path);
            }
        }
    }

}

new Yibby();
