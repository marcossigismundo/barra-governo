<?php
/**
 * Main plugin orchestrator — instantiates frontend and admin pieces.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Barra_Governo {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        load_plugin_textdomain( 'barra-governo', false, dirname( plugin_basename( BARRA_GOVERNO_FILE ) ) . '/languages' );

        new BG_Frontend();

        if ( is_admin() ) {
            new BG_Admin();
        }
    }
}
