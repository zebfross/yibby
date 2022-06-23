<?php

namespace Yibby;

class SettingsManager
{
    function __construct() {

        $cmb = new_cmb2_box( array(
            'id'           => 'yibby_options',
            //'title'        => esc_html__( 'Settings', 'redify' ),
            'object_types' => array( 'options-page' ),
            'option_key'      => Yibby::$slug, // The option key and admin menu page slug.
            'icon_url'        => 'dashicons-smartphone', // Menu icon. Only applicable if 'parent_slug' is left empty.
            'menu_title'      => 'Yibby', // Falls back to 'title' (above).
            //'parent_slug'     => Yibby::$slug, // Make options page a submenu item of the themes menu.
            'capability'      => 'manage_options', // Cap required to view options-page.
            'position'        => 20, // Menu position. Only applicable if 'parent_slug' is left empty.
            // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
            //'display_cb'      => array($this, 'display_option_tabs'), // Override the options-page form output (CMB2_Hookup::options_page_output()).
            'save_button'     => esc_html__( 'Save Settings', Yibby::$slug ), // The text for the options-page save button. Defaults to 'Save'.
            //'tab_group'    => 'yibby_options',
            'tab_title'    => 'General',
        ) );

        $cmb->add_field( array(
            'name' => 'Firebase Credentials',
            'desc' => 'contents of firebase_credentials.json',
            'default' => '',
            'id' => 'firebase_credentials',
            'type' => 'textarea'
        ) );
    }

    public static function get_option( $key = '', $default = false) {
        $option_key = Yibby::$slug;

        if ( function_exists( 'cmb2_get_option' ) ) {
            // Use cmb2_get_option as it passes through some key filters.
            return cmb2_get_option( $option_key, $key, $default );
        }

        // Fallback to get_option if CMB2 is not loaded yet.
        $opts = get_option( $option_key, $default );

        $val = $default;

        if ( 'all' == $key ) {
            $val = $opts;
        } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
            $val = $opts[ $key ];
        }

        return $val;
    }
}
