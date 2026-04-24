<?php
/**
 * Admin settings page for Barra do Governo Federal.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BG_Admin {

    const PAGE_SLUG  = 'barra-governo';
    const GROUP_SLUG = 'barra_governo_group';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( BARRA_GOVERNO_FILE ), array( $this, 'action_links' ) );
    }

    public function action_links( $links ) {
        $url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Configurações', 'barra-governo' ) . '</a>' );
        return $links;
    }

    public function register_menu() {
        add_menu_page(
            __( 'Barra do Governo', 'barra-governo' ),
            __( 'Barra do Governo', 'barra-governo' ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_page' ),
            'dashicons-flag',
            80
        );
    }

    public function register_settings() {
        register_setting(
            self::GROUP_SLUG,
            BARRA_GOVERNO_OPTION,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( 'BG_Settings', 'sanitize' ),
                'default'           => BG_Settings::defaults(),
            )
        );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( (string) $hook, self::PAGE_SLUG ) === false ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_style(
            'barra-governo-admin',
            BARRA_GOVERNO_URL . 'assets/css/admin.css',
            array( 'wp-color-picker' ),
            BARRA_GOVERNO_VERSION
        );

        wp_enqueue_script(
            'barra-governo-admin',
            BARRA_GOVERNO_URL . 'assets/js/admin.js',
            array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
            BARRA_GOVERNO_VERSION,
            true
        );

        wp_localize_script(
            'barra-governo-admin',
            'BG_Admin_i18n',
            array(
                'chooseLogo' => __( 'Selecionar logomarca', 'barra-governo' ),
                'useLogo'    => __( 'Usar esta imagem', 'barra-governo' ),
                'remove'     => __( 'Remover', 'barra-governo' ),
            )
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $s = BG_Settings::get();
        ?>
        <div class="wrap bg-admin-wrap">
            <header class="bg-admin-header">
                <div class="bg-admin-brand">
                    <span class="dashicons dashicons-flag" aria-hidden="true"></span>
                    <div>
                        <h1><?php esc_html_e( 'Barra do Governo Federal', 'barra-governo' ); ?></h1>
                        <p class="bg-admin-tag"><?php esc_html_e( 'Personalize a barra institucional exibida no topo do site.', 'barra-governo' ); ?></p>
                    </div>
                </div>
                <div class="bg-admin-actions">
                    <label class="bg-switch">
                        <input type="checkbox" id="bg-enable-toggle" form="bg-settings-form" name="<?php echo esc_attr( BARRA_GOVERNO_OPTION ); ?>[enabled]" value="1" <?php checked( $s['enabled'], 1 ); ?>>
                        <span class="bg-switch-slider"></span>
                        <span class="bg-switch-label"><?php esc_html_e( 'Barra ativa', 'barra-governo' ); ?></span>
                    </label>
                </div>
            </header>

            <?php settings_errors(); ?>

            <div class="bg-admin-layout">
                <nav class="bg-admin-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Seções de configuração', 'barra-governo' ); ?>">
                    <button type="button" class="bg-tab is-active" data-tab="general" role="tab" aria-selected="true">
                        <span class="dashicons dashicons-admin-customizer"></span>
                        <?php esc_html_e( 'Geral', 'barra-governo' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="logo" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-format-image"></span>
                        <?php esc_html_e( 'Logomarca', 'barra-governo' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="menu" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-menu"></span>
                        <?php esc_html_e( 'Menu', 'barra-governo' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="login" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php esc_html_e( 'Login', 'barra-governo' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="style" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-art"></span>
                        <?php esc_html_e( 'Aparência', 'barra-governo' ); ?>
                    </button>
                </nav>

                <form id="bg-settings-form" method="post" action="options.php" class="bg-admin-form">
                    <?php settings_fields( self::GROUP_SLUG ); ?>

                    <section class="bg-panel is-active" data-panel="general">
                        <?php $this->render_general_panel( $s ); ?>
                    </section>

                    <section class="bg-panel" data-panel="logo">
                        <?php $this->render_logo_panel( $s ); ?>
                    </section>

                    <section class="bg-panel" data-panel="menu">
                        <?php $this->render_menu_panel( $s ); ?>
                    </section>

                    <section class="bg-panel" data-panel="login">
                        <?php $this->render_login_panel( $s ); ?>
                    </section>

                    <section class="bg-panel" data-panel="style">
                        <?php $this->render_style_panel( $s ); ?>
                    </section>

                    <footer class="bg-admin-footer">
                        <?php submit_button( __( 'Salvar alterações', 'barra-governo' ), 'primary bg-save-btn', 'submit', false ); ?>
                        <span class="bg-footer-info"><?php
                            /* translators: %s: plugin version */
                            printf( esc_html__( 'Versão %s · Compatível com qualquer tema WordPress', 'barra-governo' ), esc_html( BARRA_GOVERNO_VERSION ) );
                        ?></span>
                    </footer>
                </form>

                <aside class="bg-admin-preview" aria-label="<?php esc_attr_e( 'Prévia', 'barra-governo' ); ?>">
                    <h2><?php esc_html_e( 'Prévia', 'barra-governo' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Visualização aproximada de como a barra será renderizada no topo do site.', 'barra-governo' ); ?></p>
                    <div id="bg-preview-root" class="bg-preview-root">
                        <?php $this->render_preview( $s ); ?>
                    </div>
                </aside>
            </div>
        </div>

        <dialog id="bg-help-modal" class="bg-modal">
            <div class="bg-modal-inner">
                <header class="bg-modal-header">
                    <h2 id="bg-modal-title"><?php esc_html_e( 'Ajuda', 'barra-governo' ); ?></h2>
                    <button type="button" class="bg-modal-close" aria-label="<?php esc_attr_e( 'Fechar', 'barra-governo' ); ?>">&times;</button>
                </header>
                <div id="bg-modal-body" class="bg-modal-body"></div>
                <footer class="bg-modal-footer">
                    <button type="button" class="button button-primary bg-modal-close-btn"><?php esc_html_e( 'Entendi', 'barra-governo' ); ?></button>
                </footer>
            </div>
        </dialog>

        <template id="bg-menu-item-tpl">
            <?php $this->render_menu_item_row( array( 'label' => '', 'url' => '', 'target' => '_blank' ), '__INDEX__' ); ?>
        </template>
        <?php
    }

    /* ------------------------------------------------------------------ *
     * Panels
     * ------------------------------------------------------------------ */

    private function render_general_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Configurações gerais', 'barra-governo' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Controle o comportamento da barra no topo do seu site.', 'barra-governo' ); ?></p>

        <div class="bg-field">
            <label for="bg-bar-position"><?php esc_html_e( 'Posicionamento', 'barra-governo' ); ?></label>
            <?php $this->help_btn( 'position' ); ?>
            <select name="<?php echo esc_attr( $option ); ?>[bar_position]" id="bg-bar-position" data-preview="bar_position">
                <option value="fixed" <?php selected( $s['bar_position'], 'fixed' ); ?>><?php esc_html_e( 'Fixa no topo (sempre visível)', 'barra-governo' ); ?></option>
                <option value="sticky" <?php selected( $s['bar_position'], 'sticky' ); ?>><?php esc_html_e( 'Sticky (gruda ao rolar)', 'barra-governo' ); ?></option>
                <option value="relative" <?php selected( $s['bar_position'], 'relative' ); ?>><?php esc_html_e( 'Estática (rola com a página)', 'barra-governo' ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'Recomendado: Fixa. A barra sempre será renderizada no topo do site.', 'barra-governo' ); ?></p>
        </div>

        <div class="bg-field">
            <label for="bg-max-width"><?php esc_html_e( 'Largura máxima do conteúdo (px)', 'barra-governo' ); ?></label>
            <?php $this->help_btn( 'maxwidth' ); ?>
            <input type="number" min="600" max="3000" step="10" name="<?php echo esc_attr( $option ); ?>[max_width]" id="bg-max-width" value="<?php echo esc_attr( $s['max_width'] ); ?>" data-preview="max_width">
            <p class="description"><?php esc_html_e( 'Define o limite horizontal do conteúdo interno para acompanhar o grid do tema.', 'barra-governo' ); ?></p>
        </div>
        <?php
    }

    private function render_logo_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Logomarca', 'barra-governo' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Envie uma imagem personalizada ou mantenha a logomarca padrão gov.br.', 'barra-governo' ); ?></p>

        <div class="bg-field bg-field-logo">
            <label><?php esc_html_e( 'Imagem da logomarca', 'barra-governo' ); ?></label>
            <?php $this->help_btn( 'logo' ); ?>
            <div class="bg-logo-uploader">
                <div class="bg-logo-preview" id="bg-logo-preview">
                    <?php if ( ! empty( $s['logo_url'] ) ) : ?>
                        <img src="<?php echo esc_url( $s['logo_url'] ); ?>" alt="">
                    <?php endif; ?>
                </div>
                <div class="bg-logo-btns">
                    <button type="button" class="button button-secondary" id="bg-logo-select"><?php esc_html_e( 'Selecionar da biblioteca', 'barra-governo' ); ?></button>
                    <button type="button" class="button-link bg-logo-remove" id="bg-logo-remove"><?php esc_html_e( 'Restaurar padrão', 'barra-governo' ); ?></button>
                </div>
                <input type="hidden" name="<?php echo esc_attr( $option ); ?>[logo_url]" id="bg-logo-url" value="<?php echo esc_attr( $s['logo_url'] ); ?>" data-preview="logo_url">
                <input type="hidden" name="<?php echo esc_attr( $option ); ?>[logo_id]" id="bg-logo-id" value="<?php echo esc_attr( $s['logo_id'] ); ?>">
            </div>
        </div>

        <div class="bg-field">
            <label for="bg-logo-alt"><?php esc_html_e( 'Texto alternativo (alt)', 'barra-governo' ); ?></label>
            <?php $this->help_btn( 'alt' ); ?>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[logo_alt]" id="bg-logo-alt" value="<?php echo esc_attr( $s['logo_alt'] ); ?>">
            <p class="description"><?php esc_html_e( 'Descreva brevemente a imagem para leitores de tela e acessibilidade.', 'barra-governo' ); ?></p>
        </div>

        <div class="bg-field-row">
            <div class="bg-field">
                <label for="bg-logo-link"><?php esc_html_e( 'URL ao clicar na logo', 'barra-governo' ); ?></label>
                <input type="url" name="<?php echo esc_attr( $option ); ?>[logo_link]" id="bg-logo-link" value="<?php echo esc_attr( $s['logo_link'] ); ?>" placeholder="https://">
            </div>
            <div class="bg-field">
                <label for="bg-logo-target"><?php esc_html_e( 'Abrir link em', 'barra-governo' ); ?></label>
                <select name="<?php echo esc_attr( $option ); ?>[logo_target]" id="bg-logo-target">
                    <option value="_blank" <?php selected( $s['logo_target'], '_blank' ); ?>><?php esc_html_e( 'Nova aba', 'barra-governo' ); ?></option>
                    <option value="_self" <?php selected( $s['logo_target'], '_self' ); ?>><?php esc_html_e( 'Mesma aba', 'barra-governo' ); ?></option>
                </select>
            </div>
            <div class="bg-field">
                <label for="bg-logo-height"><?php esc_html_e( 'Altura (px)', 'barra-governo' ); ?></label>
                <?php $this->help_btn( 'logoheight' ); ?>
                <input type="number" min="20" max="120" step="1" name="<?php echo esc_attr( $option ); ?>[logo_height]" id="bg-logo-height" value="<?php echo esc_attr( $s['logo_height'] ); ?>" data-preview="logo_height">
            </div>
        </div>
        <?php
    }

    private function render_menu_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Menu de acesso rápido', 'barra-governo' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Adicione, reordene ou remova os links exibidos na barra. Em telas pequenas os links aparecem em um menu suspenso acessível.', 'barra-governo' ); ?></p>

        <div class="bg-field bg-field-inline">
            <label class="bg-switch bg-switch-inline">
                <input type="checkbox" name="<?php echo esc_attr( $option ); ?>[menu_enabled]" value="1" <?php checked( $s['menu_enabled'], 1 ); ?>>
                <span class="bg-switch-slider"></span>
                <span class="bg-switch-label"><?php esc_html_e( 'Exibir menu de acesso rápido', 'barra-governo' ); ?></span>
            </label>
            <?php $this->help_btn( 'menu' ); ?>
        </div>

        <div class="bg-field">
            <label for="bg-menu-title"><?php esc_html_e( 'Título do menu (mobile)', 'barra-governo' ); ?></label>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[menu_title]" id="bg-menu-title" value="<?php echo esc_attr( $s['menu_title'] ); ?>">
            <p class="description"><?php esc_html_e( 'Texto exibido como cabeçalho do menu suspenso em dispositivos móveis.', 'barra-governo' ); ?></p>
        </div>

        <div class="bg-field">
            <label><?php esc_html_e( 'Itens do menu', 'barra-governo' ); ?></label>
            <?php $this->help_btn( 'menuitems' ); ?>
            <div id="bg-menu-items" class="bg-repeater">
                <?php
                if ( ! empty( $s['menu_items'] ) ) {
                    foreach ( $s['menu_items'] as $i => $item ) {
                        $this->render_menu_item_row( $item, $i );
                    }
                }
                ?>
            </div>
            <button type="button" class="button button-secondary" id="bg-add-menu-item">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e( 'Adicionar item', 'barra-governo' ); ?>
            </button>
        </div>
        <?php
    }

    private function render_menu_item_row( $item, $index ) {
        $option = BARRA_GOVERNO_OPTION;
        $label  = isset( $item['label'] ) ? $item['label'] : '';
        $url    = isset( $item['url'] ) ? $item['url'] : '';
        $target = isset( $item['target'] ) ? $item['target'] : '_blank';
        ?>
        <div class="bg-repeater-row">
            <span class="bg-repeater-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Arraste para reordenar', 'barra-governo' ); ?>"></span>
            <div class="bg-repeater-fields">
                <input type="text" name="<?php echo esc_attr( $option ); ?>[menu_items][<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Rótulo', 'barra-governo' ); ?>">
                <input type="url" name="<?php echo esc_attr( $option ); ?>[menu_items][<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
                <select name="<?php echo esc_attr( $option ); ?>[menu_items][<?php echo esc_attr( $index ); ?>][target]">
                    <option value="_blank" <?php selected( $target, '_blank' ); ?>><?php esc_html_e( 'Nova aba', 'barra-governo' ); ?></option>
                    <option value="_self" <?php selected( $target, '_self' ); ?>><?php esc_html_e( 'Mesma aba', 'barra-governo' ); ?></option>
                </select>
            </div>
            <button type="button" class="button-link bg-repeater-remove" title="<?php esc_attr_e( 'Remover item', 'barra-governo' ); ?>">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <?php
    }

    private function render_login_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Botão de login', 'barra-governo' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Configure o botão destacado à direita da barra, normalmente utilizado para o login único gov.br.', 'barra-governo' ); ?></p>

        <div class="bg-field bg-field-inline">
            <label class="bg-switch bg-switch-inline">
                <input type="checkbox" name="<?php echo esc_attr( $option ); ?>[login_enabled]" value="1" <?php checked( $s['login_enabled'], 1 ); ?>>
                <span class="bg-switch-slider"></span>
                <span class="bg-switch-label"><?php esc_html_e( 'Exibir botão de login', 'barra-governo' ); ?></span>
            </label>
            <?php $this->help_btn( 'login' ); ?>
        </div>

        <div class="bg-field">
            <label for="bg-login-label"><?php esc_html_e( 'Rótulo do botão', 'barra-governo' ); ?></label>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[login_label]" id="bg-login-label" value="<?php echo esc_attr( $s['login_label'] ); ?>">
        </div>

        <div class="bg-field-row">
            <div class="bg-field">
                <label for="bg-login-url"><?php esc_html_e( 'URL do login', 'barra-governo' ); ?></label>
                <input type="url" name="<?php echo esc_attr( $option ); ?>[login_url]" id="bg-login-url" value="<?php echo esc_attr( $s['login_url'] ); ?>" placeholder="https://">
            </div>
            <div class="bg-field">
                <label for="bg-login-target"><?php esc_html_e( 'Abrir link em', 'barra-governo' ); ?></label>
                <select name="<?php echo esc_attr( $option ); ?>[login_target]" id="bg-login-target">
                    <option value="_blank" <?php selected( $s['login_target'], '_blank' ); ?>><?php esc_html_e( 'Nova aba', 'barra-governo' ); ?></option>
                    <option value="_self" <?php selected( $s['login_target'], '_self' ); ?>><?php esc_html_e( 'Mesma aba', 'barra-governo' ); ?></option>
                </select>
            </div>
        </div>

        <div class="bg-field">
            <label for="bg-login-icon"><?php esc_html_e( 'Ícone (classe Font Awesome)', 'barra-governo' ); ?></label>
            <?php $this->help_btn( 'icon' ); ?>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[login_icon]" id="bg-login-icon" value="<?php echo esc_attr( $s['login_icon'] ); ?>" placeholder="fas fa-user">
            <p class="description"><?php
                /* translators: %s: Font Awesome URL */
                printf(
                    wp_kses( __( 'Consulte a <a href="%s" target="_blank" rel="noopener">lista oficial de ícones Font Awesome 5</a>. Deixe em branco para ocultar.', 'barra-governo' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ),
                    'https://fontawesome.com/v5/search?o=r&m=free'
                );
            ?></p>
        </div>
        <?php
    }

    private function render_style_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        $fields = array(
            'bar_bg'             => __( 'Fundo da barra', 'barra-governo' ),
            'bar_text'           => __( 'Cor dos textos e ícones', 'barra-governo' ),
            'bar_hover_bg'       => __( 'Fundo dos links ao passar o mouse', 'barra-governo' ),
            'bar_hover_text'     => __( 'Texto dos links ao passar o mouse', 'barra-governo' ),
            'bar_login_bg'       => __( 'Fundo do botão de login', 'barra-governo' ),
            'bar_login_text'     => __( 'Texto do botão de login', 'barra-governo' ),
            'bar_login_hover_bg' => __( 'Fundo do botão de login (hover)', 'barra-governo' ),
        );
        ?>
        <h2><?php esc_html_e( 'Aparência e cores', 'barra-governo' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Personalize a paleta da barra. Por padrão, as cores seguem o Design System gov.br.', 'barra-governo' ); ?></p>

        <div class="bg-color-grid">
            <?php foreach ( $fields as $key => $label ) : ?>
                <div class="bg-field">
                    <label for="bg-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
                    <input type="text" class="bg-color-field" name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]" id="bg-<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>" data-preview="<?php echo esc_attr( $key ); ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-field">
            <button type="button" class="button" id="bg-reset-colors"><?php esc_html_e( 'Restaurar cores padrão gov.br', 'barra-governo' ); ?></button>
            <?php $this->help_btn( 'colors' ); ?>
        </div>
        <?php
    }

    /* ------------------------------------------------------------------ *
     * Helpers
     * ------------------------------------------------------------------ */

    private function render_preview( $s ) {
        ?>
        <div id="bg-preview-bar" class="bg-preview-bar" style="
            --bg-bar-bg:<?php echo esc_attr( $s['bar_bg'] ); ?>;
            --bg-bar-text:<?php echo esc_attr( $s['bar_text'] ); ?>;
            --bg-bar-hover-bg:<?php echo esc_attr( $s['bar_hover_bg'] ); ?>;
            --bg-bar-hover-text:<?php echo esc_attr( $s['bar_hover_text'] ); ?>;
            --bg-login-bg:<?php echo esc_attr( $s['bar_login_bg'] ); ?>;
            --bg-login-text:<?php echo esc_attr( $s['bar_login_text'] ); ?>;
            --bg-login-hover-bg:<?php echo esc_attr( $s['bar_login_hover_bg'] ); ?>;
            --bg-logo-height:<?php echo esc_attr( $s['logo_height'] ); ?>px;
            --bg-max-width:<?php echo esc_attr( $s['max_width'] ); ?>px;
        ">
            <div class="bgp-inner">
                <div class="bgp-logo"><img id="bg-preview-logo" src="<?php echo esc_url( $s['logo_url'] ); ?>" alt=""></div>
                <div class="bgp-actions">
                    <span class="bgp-link">Link 1</span>
                    <span class="bgp-link">Link 2</span>
                    <span class="bgp-divider"></span>
                    <span class="bgp-login"><?php echo esc_html( $s['login_label'] ); ?></span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render a help (?) button bound to a topic in the modal content map.
     */
    private function help_btn( $topic ) {
        ?>
        <button type="button" class="bg-help-btn" data-help="<?php echo esc_attr( $topic ); ?>" aria-label="<?php esc_attr_e( 'Ajuda sobre esta opção', 'barra-governo' ); ?>">
            <span class="dashicons dashicons-editor-help"></span>
        </button>
        <?php
    }
}
