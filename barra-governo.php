<?php
/**
 * Plugin Name: Barra do Governo Federal
 * Plugin URI:  https://www.gov.br
 * Description: Insere a barra padrão do Governo Federal brasileiro no topo de qualquer tema WordPress.
 * Version:     1.0.0
 * Author:      Governo Federal
 * License:     GPL-2.0-or-later
 * Text Domain: barra-governo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BARRA_GOVERNO_VERSION', '1.0.0' );
define( 'BARRA_GOVERNO_URL', plugin_dir_url( __FILE__ ) );

/**
 * Enqueue CSS and JS assets.
 */
function barra_governo_enqueue_assets() {
    wp_enqueue_style(
        'barra-governo-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
        array(),
        '5.15.4'
    );

    wp_enqueue_style(
        'barra-governo',
        BARRA_GOVERNO_URL . 'assets/css/barra-governo.css',
        array( 'barra-governo-fontawesome' ),
        BARRA_GOVERNO_VERSION
    );

    wp_enqueue_script(
        'barra-governo',
        BARRA_GOVERNO_URL . 'assets/js/barra-governo.js',
        array(),
        BARRA_GOVERNO_VERSION,
        false
    );
}
add_action( 'wp_enqueue_scripts', 'barra_governo_enqueue_assets' );

/**
 * Render the government bar HTML right after <body>.
 */
function barra_governo_render() {
    ?>
    <div id="barra-governo" role="banner" aria-label="Barra do Governo Federal">
        <div class="bg-container">
            <div class="bg-header">
                <div class="bg-logo">
                    <a href="https://www.gov.br/pt-br" target="_blank" rel="noopener noreferrer">
                        <img src="https://barra.sistema.gov.br/v1/assets/govbr.webp"
                             alt="Logo GovBR"
                             width="100"
                             height="28"
                             loading="eager">
                    </a>
                </div>

                <div class="bg-actions">
                    <div class="bg-links">
                        <button class="bg-btn bg-btn-circle bg-toggle-links"
                                type="button"
                                aria-label="Acesso Rápido"
                                aria-expanded="false"
                                aria-controls="bg-links-list">
                            <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
                        </button>
                        <ul id="bg-links-list" class="bg-links-list" role="list">
                            <li class="bg-links-title" role="presentation">Acesso Rápido</li>
                            <li><a href="https://www.gov.br/pt-br/orgaos-do-governo" target="_blank" rel="noopener noreferrer">Órgãos do Governo</a></li>
                            <li><a href="https://www.gov.br/acessoainformacao/pt-br" target="_blank" rel="noopener noreferrer">Acesso à Informação</a></li>
                            <li><a href="http://www4.planalto.gov.br/legislacao" target="_blank" rel="noopener noreferrer">Legislação</a></li>
                            <li><a href="https://www.gov.br/governodigital/pt-br/acessibilidade-digital" target="_blank" rel="noopener noreferrer">Acessibilidade</a></li>
                        </ul>
                    </div>

                    <span class="bg-divider" aria-hidden="true"></span>

                    <div class="bg-login">
                        <a href="https://sso.acesso.gov.br" target="_blank" rel="noopener noreferrer" class="bg-btn-login">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <span class="bg-login-text">Entrar com gov.br</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_body_open', 'barra_governo_render', 1 );

/**
 * Fallback: inject bar via wp_head if theme does not support wp_body_open.
 */
function barra_governo_fallback_script() {
    ?>
    <script>
    (function(){
        if(typeof wp_body_open_fired==='undefined'){
            document.addEventListener('DOMContentLoaded',function(){
                var bar=document.getElementById('barra-governo');
                if(!bar){
                    var body=document.body;
                    var tmp=document.createElement('div');
                    tmp.innerHTML=<?php
                        ob_start();
                        barra_governo_render();
                        $html = ob_get_clean();
                        echo wp_json_encode( $html );
                    ?>;
                    while(tmp.firstChild){body.insertBefore(tmp.firstChild,body.firstChild);}
                }
            });
        }
    })();
    </script>
    <?php
}

/**
 * Track if wp_body_open was fired.
 */
function barra_governo_mark_body_open() {
    echo '<script>var wp_body_open_fired=true;</script>';
}
add_action( 'wp_body_open', 'barra_governo_mark_body_open', 0 );
add_action( 'wp_head', 'barra_governo_fallback_script', 99 );

/**
 * Admin bar offset — push admin bar down when logged in.
 */
function barra_governo_admin_bar_offset() {
    if ( is_admin_bar_showing() ) {
        echo '<style>#wpadminbar{top:0!important}#barra-governo{margin-top:32px}@media screen and (max-width:782px){#barra-governo{margin-top:46px}}</style>';
    }
}
add_action( 'wp_head', 'barra_governo_admin_bar_offset' );
