<?php
/**
 * REST API endpoint that serves the self-contained embed script for
 * distribution across the IBRAM network of sites.
 *
 * The endpoint /wp-json/barra-ibram/v1/embed.js returns a JavaScript
 * payload that — when included via a single <script src="..."> tag on
 * any remote site — injects the bar at the top of that site using
 * absolute URLs back to this central installation.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BG_Rest {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route(
            BARRA_IBRAM_REST_NS,
            '/embed.js',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'embed_js' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            BARRA_IBRAM_REST_NS,
            '/config',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'config_json' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * JSON representation of current settings — useful for audits and
     * custom integrations. Returns only public-facing fields.
     */
    public function config_json() {
        $s = BG_Settings::get();
        unset( $s['mode'], $s['remote_url'], $s['logo_id'] );
        $response = new WP_REST_Response( $s );
        $response->header( 'Access-Control-Allow-Origin', '*' );
        $response->header( 'Cache-Control', 'public, max-age=300' );
        return $response;
    }

    /**
     * Build a single JavaScript file that:
     *  1. Registers FontAwesome + the central's stylesheet (absolute URLs)
     *  2. Injects CSS variables (palette + layout) in a <style>
     *  3. Creates the bar DOM and prepends it to <body>
     *  4. Wires mobile menu + dynamic body offset for fixed positioning
     *
     * All strings are escaped via wp_json_encode so the payload is safe.
     */
    public function embed_js( WP_REST_Request $request ) {
        $s = BG_Settings::get();

        // When the central is in client mode, refuse to serve — the central
        // must be the source of truth.
        if ( empty( $s['enabled'] ) ) {
            status_header( 200 );
            header( 'Content-Type: application/javascript; charset=utf-8' );
            header( 'Access-Control-Allow-Origin: *' );
            header( 'Cache-Control: public, max-age=60' );
            echo "/* Barra IBRAM: barra desativada no hospedeiro. */\n";
            exit;
        }

        $frontend = new BG_Frontend();
        ob_start();
        $frontend->render();
        $html = trim( ob_get_clean() );

        $payload = array(
            'version'  => BARRA_GOVERNO_VERSION,
            'html'     => $html,
            'position' => $s['bar_position'],
            'vars'     => $this->build_vars_css( $s ),
            'css'      => array(
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
                BARRA_GOVERNO_URL . 'assets/css/barra-governo.css?v=' . BARRA_GOVERNO_VERSION,
            ),
        );

        status_header( 200 );
        header( 'Content-Type: application/javascript; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: GET, OPTIONS' );
        header( 'Cache-Control: public, max-age=300' ); // 5 minutes
        header( 'X-Barra-Ibram-Version: ' . BARRA_GOVERNO_VERSION );

        echo $this->embed_template( $payload );
        exit;
    }

    /**
     * Renders the inline CSS variable block used by the stylesheet.
     * Matches BG_Frontend::inline_vars to keep behaviour identical.
     */
    private function build_vars_css( $s ) {
        $position_css = 'position:fixed;top:0;left:0;right:0;';
        if ( $s['bar_position'] === 'sticky' ) {
            $position_css = 'position:sticky;top:0;';
        } elseif ( $s['bar_position'] === 'relative' ) {
            $position_css = 'position:relative;';
        }

        $css  = '#barra-governo{';
        $css .= '--bg-bar-bg:' . $s['bar_bg'] . ';';
        $css .= '--bg-bar-text:' . $s['bar_text'] . ';';
        $css .= '--bg-bar-hover-bg:' . $s['bar_hover_bg'] . ';';
        $css .= '--bg-bar-hover-text:' . $s['bar_hover_text'] . ';';
        $css .= '--bg-login-bg:' . $s['bar_login_bg'] . ';';
        $css .= '--bg-login-text:' . $s['bar_login_text'] . ';';
        $css .= '--bg-login-hover-bg:' . $s['bar_login_hover_bg'] . ';';
        $css .= '--bg-max-width:' . absint( $s['max_width'] ) . 'px;';
        $css .= '--bg-logo-height:' . absint( $s['logo_height'] ) . 'px;';
        $css .= $position_css;
        $css .= 'z-index:99999;width:100%;}';
        return $css;
    }

    /**
     * The IIFE template that wraps the payload. Designed to be idempotent
     * (safe to include twice) and to work on any HTML document — WordPress,
     * Drupal, static HTML, etc.
     */
    private function embed_template( $payload ) {
        $json = wp_json_encode( $payload );
        return <<<JS
/*! Barra IBRAM v{$payload['version']} — embed script */
(function(){
    if (window.__BarraIbramLoaded) return;
    window.__BarraIbramLoaded = true;

    var data = {$json};

    function injectCssLinks() {
        data.css.forEach(function(url){
            if (document.querySelector('link[data-barra-ibram][href="'+url+'"]')) return;
            var l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = url;
            l.setAttribute('data-barra-ibram','1');
            document.head.appendChild(l);
        });
    }

    function injectVars() {
        var s = document.createElement('style');
        s.id = 'barra-governo-vars';
        s.setAttribute('data-barra-ibram','1');
        s.textContent = data.vars;
        document.head.appendChild(s);
    }

    function injectBar() {
        if (document.getElementById('barra-governo')) return;
        var wrap = document.createElement('div');
        wrap.innerHTML = data.html;
        while (wrap.firstChild) {
            document.body.insertBefore(wrap.firstChild, document.body.firstChild);
        }
    }

    function wireMobileMenu(bar){
        var toggleBtn = bar.querySelector('.bg-toggle-links');
        var linksList = bar.querySelector('.bg-links-list');
        if (!toggleBtn || !linksList) return;

        toggleBtn.addEventListener('click', function(e){
            e.stopPropagation();
            var expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
            toggleBtn.setAttribute('aria-expanded', String(!expanded));
            linksList.classList.toggle('bg-open');
        });
        document.addEventListener('click', function(e){
            if (!toggleBtn.contains(e.target) && !linksList.contains(e.target)) {
                toggleBtn.setAttribute('aria-expanded', 'false');
                linksList.classList.remove('bg-open');
            }
        });
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') {
                toggleBtn.setAttribute('aria-expanded', 'false');
                linksList.classList.remove('bg-open');
            }
        });
    }

    function wireFixedOffset(bar){
        if (data.position !== 'fixed') return;
        var html = document.documentElement;
        html.classList.add('bg-has-fixed-bar');
        var apply = function(){
            var h = bar.getBoundingClientRect().height;
            if (h > 0) html.style.setProperty('--bg-bar-height', h + 'px');
        };
        apply();
        if (window.ResizeObserver) {
            var ro = new ResizeObserver(apply);
            ro.observe(bar);
        } else {
            window.addEventListener('resize', apply);
        }
        window.addEventListener('load', apply);
    }

    function boot(){
        injectCssLinks();
        injectVars();
        injectBar();
        var bar = document.getElementById('barra-governo');
        if (!bar) return;
        wireMobileMenu(bar);
        wireFixedOffset(bar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
JS;
    }
}
