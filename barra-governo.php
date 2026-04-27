<?php
/**
 * Plugin Name: Barra IBRAM
 * Plugin URI:  https://www.gov.br/ibram
 * Description: Barra institucional do IBRAM, 100% customizável. Funciona em dois modos: "hospedeiro" (o site central fornece a barra) e "cliente" (os demais sites consomem via uma única linha de script). Pensada para distribuição em larga escala nos sites do Instituto Brasileiro de Museus.
 * Version:     3.0.0
 * Author:      IBRAM — Instituto Brasileiro de Museus
 * License:     GPL-2.0-or-later
 * Text Domain: barra-ibram
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BARRA_GOVERNO_VERSION', '3.0.0' );
define( 'BARRA_GOVERNO_FILE', __FILE__ );
define( 'BARRA_GOVERNO_DIR', plugin_dir_path( __FILE__ ) );
define( 'BARRA_GOVERNO_URL', plugin_dir_url( __FILE__ ) );
define( 'BARRA_GOVERNO_OPTION', 'barra_governo_settings' );
define( 'BARRA_IBRAM_REST_NS', 'barra-ibram/v1' );

require_once BARRA_GOVERNO_DIR . 'includes/class-bg-settings.php';
require_once BARRA_GOVERNO_DIR . 'includes/class-bg-frontend.php';
require_once BARRA_GOVERNO_DIR . 'includes/class-bg-rest.php';
require_once BARRA_GOVERNO_DIR . 'includes/class-bg-admin.php';
require_once BARRA_GOVERNO_DIR . 'includes/class-barra-governo.php';

add_action( 'plugins_loaded', array( 'Barra_Governo', 'instance' ) );

register_activation_hook( __FILE__, array( 'BG_Settings', 'activate' ) );
