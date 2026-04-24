<?php
/**
 * Settings model, defaults and sanitization for Barra do Governo Federal.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BG_Settings {

    /**
     * Default settings schema.
     */
    public static function defaults() {
        return array(
            'enabled'        => 1,

            'logo_url'       => 'https://barra.sistema.gov.br/v1/assets/govbr.webp',
            'logo_id'        => 0,
            'logo_alt'       => 'Logo GovBR',
            'logo_link'      => 'https://www.gov.br/pt-br',
            'logo_target'    => '_blank',
            'logo_height'    => 36,

            'bar_position'   => 'fixed',
            'bar_bg'         => '#ffffff',
            'bar_text'       => '#1351b4',
            'bar_hover_bg'   => '#dce8f5',
            'bar_hover_text' => '#0c326f',
            'bar_login_bg'   => '#1351b4',
            'bar_login_text' => '#ffffff',
            'bar_login_hover_bg' => '#0c326f',
            'max_width'      => 1400,

            'menu_enabled'   => 1,
            'menu_title'     => 'Acesso Rápido',
            'menu_items'     => array(
                array( 'label' => 'Órgãos do Governo', 'url' => 'https://www.gov.br/pt-br/orgaos-do-governo', 'target' => '_blank' ),
                array( 'label' => 'Acesso à Informação', 'url' => 'https://www.gov.br/acessoainformacao/pt-br', 'target' => '_blank' ),
                array( 'label' => 'Legislação', 'url' => 'http://www4.planalto.gov.br/legislacao', 'target' => '_blank' ),
                array( 'label' => 'Acessibilidade', 'url' => 'https://www.gov.br/governodigital/pt-br/acessibilidade-digital', 'target' => '_blank' ),
            ),

            'login_enabled'  => 1,
            'login_label'    => 'Entrar com gov.br',
            'login_url'      => 'https://sso.acesso.gov.br',
            'login_target'   => '_blank',
            'login_icon'     => 'fas fa-user',
        );
    }

    /**
     * Retrieve current settings merged with defaults.
     */
    public static function get() {
        $saved = get_option( BARRA_GOVERNO_OPTION, array() );
        if ( ! is_array( $saved ) ) {
            $saved = array();
        }
        return wp_parse_args( $saved, self::defaults() );
    }

    /**
     * Retrieve a single setting value.
     */
    public static function value( $key, $fallback = '' ) {
        $all = self::get();
        return isset( $all[ $key ] ) ? $all[ $key ] : $fallback;
    }

    /**
     * Run on plugin activation — seed defaults if absent.
     */
    public static function activate() {
        if ( false === get_option( BARRA_GOVERNO_OPTION ) ) {
            add_option( BARRA_GOVERNO_OPTION, self::defaults() );
        }
    }

    /**
     * Sanitize settings before persisting to DB.
     *
     * @param array $input Raw input from $_POST.
     * @return array
     */
    public static function sanitize( $input ) {
        $defaults = self::defaults();
        $clean    = array();

        $clean['enabled']       = ! empty( $input['enabled'] ) ? 1 : 0;
        $clean['menu_enabled']  = ! empty( $input['menu_enabled'] ) ? 1 : 0;
        $clean['login_enabled'] = ! empty( $input['login_enabled'] ) ? 1 : 0;

        $clean['logo_url']    = isset( $input['logo_url'] ) ? esc_url_raw( $input['logo_url'] ) : $defaults['logo_url'];
        $clean['logo_id']     = isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0;
        $clean['logo_alt']    = isset( $input['logo_alt'] ) ? sanitize_text_field( $input['logo_alt'] ) : $defaults['logo_alt'];
        $clean['logo_link']   = isset( $input['logo_link'] ) ? esc_url_raw( $input['logo_link'] ) : $defaults['logo_link'];
        $clean['logo_target'] = self::sanitize_target( isset( $input['logo_target'] ) ? $input['logo_target'] : '' );
        $clean['logo_height'] = isset( $input['logo_height'] ) ? max( 20, min( 120, absint( $input['logo_height'] ) ) ) : $defaults['logo_height'];

        $clean['bar_position']       = in_array( $input['bar_position'] ?? '', array( 'fixed', 'sticky', 'relative' ), true ) ? $input['bar_position'] : 'fixed';
        $clean['bar_bg']             = self::sanitize_color( $input['bar_bg'] ?? '', $defaults['bar_bg'] );
        $clean['bar_text']           = self::sanitize_color( $input['bar_text'] ?? '', $defaults['bar_text'] );
        $clean['bar_hover_bg']       = self::sanitize_color( $input['bar_hover_bg'] ?? '', $defaults['bar_hover_bg'] );
        $clean['bar_hover_text']     = self::sanitize_color( $input['bar_hover_text'] ?? '', $defaults['bar_hover_text'] );
        $clean['bar_login_bg']       = self::sanitize_color( $input['bar_login_bg'] ?? '', $defaults['bar_login_bg'] );
        $clean['bar_login_text']     = self::sanitize_color( $input['bar_login_text'] ?? '', $defaults['bar_login_text'] );
        $clean['bar_login_hover_bg'] = self::sanitize_color( $input['bar_login_hover_bg'] ?? '', $defaults['bar_login_hover_bg'] );
        $clean['max_width']          = isset( $input['max_width'] ) ? max( 600, min( 3000, absint( $input['max_width'] ) ) ) : $defaults['max_width'];

        $clean['menu_title'] = isset( $input['menu_title'] ) ? sanitize_text_field( $input['menu_title'] ) : $defaults['menu_title'];

        $items = array();
        if ( ! empty( $input['menu_items'] ) && is_array( $input['menu_items'] ) ) {
            foreach ( $input['menu_items'] as $item ) {
                $label = isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : '';
                $url   = isset( $item['url'] ) ? esc_url_raw( $item['url'] ) : '';
                if ( $label === '' || $url === '' ) {
                    continue;
                }
                $items[] = array(
                    'label'  => $label,
                    'url'    => $url,
                    'target' => self::sanitize_target( $item['target'] ?? '' ),
                );
            }
        }
        $clean['menu_items'] = $items;

        $clean['login_label']  = isset( $input['login_label'] ) ? sanitize_text_field( $input['login_label'] ) : $defaults['login_label'];
        $clean['login_url']    = isset( $input['login_url'] ) ? esc_url_raw( $input['login_url'] ) : $defaults['login_url'];
        $clean['login_target'] = self::sanitize_target( $input['login_target'] ?? '' );
        $clean['login_icon']   = isset( $input['login_icon'] ) ? sanitize_text_field( $input['login_icon'] ) : $defaults['login_icon'];

        return $clean;
    }

    private static function sanitize_color( $value, $fallback ) {
        $value = is_string( $value ) ? trim( $value ) : '';
        if ( preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $value ) ) {
            return $value;
        }
        return $fallback;
    }

    private static function sanitize_target( $value ) {
        return $value === '_self' ? '_self' : '_blank';
    }
}
