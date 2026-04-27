<?php
/**
 * Frontend rendering and asset loading for Barra do Governo Federal.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BG_Frontend {

    public function __construct() {
        $mode = BG_Settings::value( 'mode', 'host' );

        if ( $mode === 'client' ) {
            // Client mode: consume the bar from a remote central installation.
            add_action( 'wp_head', array( $this, 'render_client_script' ), 1 );
            return;
        }

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_body_open', array( $this, 'render' ), 1 );
        add_action( 'wp_body_open', array( $this, 'mark_body_open' ), 0 );
        add_action( 'wp_head', array( $this, 'fallback_script' ), 99 );
        add_action( 'wp_head', array( $this, 'inline_vars' ), 5 );
        add_action( 'wp_head', array( $this, 'admin_bar_offset' ) );
    }

    /**
     * Client mode output: a single async <script> tag pointing at the
     * central host's embed endpoint. Everything else (CSS, HTML, JS)
     * comes from the remote side.
     */
    public function render_client_script() {
        if ( ! $this->enabled() ) {
            return;
        }
        $remote = BG_Settings::value( 'remote_url', '' );
        if ( empty( $remote ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo "\n<!-- Barra IBRAM: modo cliente ativo, mas a URL do hospedeiro não foi configurada. -->\n";
            }
            return;
        }
        echo "\n<script src=\"" . esc_url( $remote ) . "\" async data-barra-ibram-client=\"1\"></script>\n";
    }

    /**
     * Bail-out helper: is the bar enabled?
     */
    private function enabled() {
        return (int) BG_Settings::value( 'enabled', 1 ) === 1;
    }

    public function enqueue_assets() {
        if ( ! $this->enabled() ) {
            return;
        }

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
            true
        );
    }

    /**
     * Inject CSS custom properties derived from settings — keeps the stylesheet
     * untouched while letting admins fully control the palette.
     */
    public function inline_vars() {
        if ( ! $this->enabled() ) {
            return;
        }
        $s = BG_Settings::get();

        $position_css = 'position:fixed;top:0;left:0;right:0;';
        if ( $s['bar_position'] === 'sticky' ) {
            $position_css = 'position:sticky;top:0;';
        } elseif ( $s['bar_position'] === 'relative' ) {
            $position_css = 'position:relative;';
        }

        echo '<style id="barra-governo-vars">';
        echo '#barra-governo{';
        echo '--bg-bar-bg:' . esc_attr( $s['bar_bg'] ) . ';';
        echo '--bg-bar-text:' . esc_attr( $s['bar_text'] ) . ';';
        echo '--bg-bar-hover-bg:' . esc_attr( $s['bar_hover_bg'] ) . ';';
        echo '--bg-bar-hover-text:' . esc_attr( $s['bar_hover_text'] ) . ';';
        echo '--bg-login-bg:' . esc_attr( $s['bar_login_bg'] ) . ';';
        echo '--bg-login-text:' . esc_attr( $s['bar_login_text'] ) . ';';
        echo '--bg-login-hover-bg:' . esc_attr( $s['bar_login_hover_bg'] ) . ';';
        echo '--bg-max-width:' . absint( $s['max_width'] ) . 'px;';
        echo '--bg-logo-height:' . absint( $s['logo_height'] ) . 'px;';
        echo $position_css;
        echo 'z-index:99999;width:100%;}';
        echo '</style>';
    }

    /**
     * Render bar HTML.
     */
    public function render() {
        if ( ! $this->enabled() ) {
            return;
        }

        $s = BG_Settings::get();
        ?>
        <div id="barra-governo" data-bg-position="<?php echo esc_attr( $s['bar_position'] ); ?>" role="banner" aria-label="Barra do Governo Federal">
            <div class="bg-container">
                <div class="bg-header">
                    <div class="bg-logo">
                        <?php if ( ! empty( $s['logo_link'] ) ) : ?>
                            <a href="<?php echo esc_url( $s['logo_link'] ); ?>" target="<?php echo esc_attr( $s['logo_target'] ); ?>" rel="noopener noreferrer">
                        <?php endif; ?>
                            <img src="<?php echo esc_url( $s['logo_url'] ); ?>"
                                 alt="<?php echo esc_attr( $s['logo_alt'] ); ?>"
                                 loading="eager">
                        <?php if ( ! empty( $s['logo_link'] ) ) : ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="bg-actions">
                        <?php if ( ! empty( $s['menu_enabled'] ) && ! empty( $s['menu_items'] ) ) : ?>
                            <div class="bg-links">
                                <button class="bg-btn bg-btn-circle bg-toggle-links"
                                        type="button"
                                        aria-label="<?php echo esc_attr( $s['menu_title'] ); ?>"
                                        aria-expanded="false"
                                        aria-controls="bg-links-list">
                                    <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
                                </button>
                                <ul id="bg-links-list" class="bg-links-list" role="list">
                                    <li class="bg-links-title" role="presentation"><?php echo esc_html( $s['menu_title'] ); ?></li>
                                    <?php foreach ( $s['menu_items'] as $item ) : ?>
                                        <li><a href="<?php echo esc_url( $item['url'] ); ?>"
                                               target="<?php echo esc_attr( $item['target'] ); ?>"
                                               rel="noopener noreferrer"><?php echo esc_html( $item['label'] ); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $s['menu_enabled'] ) && ! empty( $s['menu_items'] ) && ! empty( $s['login_enabled'] ) ) : ?>
                            <span class="bg-divider" aria-hidden="true"></span>
                        <?php endif; ?>

                        <?php if ( ! empty( $s['login_enabled'] ) ) : ?>
                            <div class="bg-login">
                                <a href="<?php echo esc_url( $s['login_url'] ); ?>"
                                   target="<?php echo esc_attr( $s['login_target'] ); ?>"
                                   rel="noopener noreferrer"
                                   class="bg-btn-login">
                                    <?php if ( ! empty( $s['login_icon'] ) ) : ?>
                                        <i class="<?php echo esc_attr( $s['login_icon'] ); ?>" aria-hidden="true"></i>
                                    <?php endif; ?>
                                    <span class="bg-login-text"><?php echo esc_html( $s['login_label'] ); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Fallback: if theme does not fire wp_body_open, inject the bar as first
     * child of <body> via DOM after load.
     */
    public function fallback_script() {
        if ( ! $this->enabled() ) {
            return;
        }
        ?>
        <script>
        (function(){
            if(typeof window.bgBodyOpenFired==='undefined'){
                document.addEventListener('DOMContentLoaded',function(){
                    if(document.getElementById('barra-governo'))return;
                    var tmp=document.createElement('div');
                    tmp.innerHTML=<?php
                        ob_start();
                        $this->render();
                        echo wp_json_encode( ob_get_clean() );
                    ?>;
                    while(tmp.firstChild){document.body.insertBefore(tmp.firstChild,document.body.firstChild);}
                });
            }
        })();
        </script>
        <?php
    }

    public function mark_body_open() {
        echo '<script>window.bgBodyOpenFired=true;</script>';
    }

    /**
     * Offset the bar for the WP admin toolbar when the user is logged in.
     * Only relevant when bar is fixed.
     */
    public function admin_bar_offset() {
        if ( ! $this->enabled() || ! is_admin_bar_showing() ) {
            return;
        }
        $position = BG_Settings::value( 'bar_position', 'fixed' );
        if ( $position !== 'fixed' && $position !== 'sticky' ) {
            return;
        }
        ?>
        <style id="barra-governo-adminbar-offset">
            #wpadminbar{position:fixed!important}
            #barra-governo{top:32px!important}
            @media screen and (max-width:782px){#barra-governo{top:46px!important}}
        </style>
        <?php
    }
}
