<?php
/**
 * Plugin Name: Barra do Governo Federal
 * Plugin URI:  https://www.gov.br
 * Description: Barra institucional do Governo Federal, 100% customizável: troque a logomarca, configure menus, cores e botão de login. Responsiva e compatível com qualquer tema WordPress.
 * Version:     2.0.0
 * Author:      Governo Federal
 * License:     GPL-2.0-or-later
 * Text Domain: barra-governo
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BARRA_GOVERNO_VERSION', '2.0.0' );
define( 'BARRA_GOVERNO_FILE', __FILE__ );
define( 'BARRA_GOVERNO_DIR', plugin_dir_path( __FILE__ ) );
define( 'BARRA_GOVERNO_URL', plugin_dir_url( __FILE__ ) );
define( 'BARRA_GOVERNO_OPTION', 'barra_governo_settings' );

require_once BARRA_GOVERNO_DIR . 'includes/class-bg-settings.php';
require_once BARRA_GOVERNO_DIR . 'includes/class-bg-frontend.php';
require_once BARRA_GOVERNO_DIR . 'includes/class-bg-admin.php';
require_once BARRA_GOVERNO_DIR . 'includes/class-barra-governo.php';

add_action( 'plugins_loaded', array( 'Barra_Governo', 'instance' ) );

register_activation_hook( __FILE__, array( 'BG_Settings', 'activate' ) );
