<?php
/**
 * Admin settings page for Barra do Governo Federal.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BG_Admin {

    const PAGE_SLUG  = 'barra-ibram';
    const GROUP_SLUG = 'barra_governo_group';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( BARRA_GOVERNO_FILE ), array( $this, 'action_links' ) );
    }

    public function action_links( $links ) {
        $url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Configurações', 'barra-ibram' ) . '</a>' );
        return $links;
    }

    public function register_menu() {
        add_menu_page(
            __( 'Barra IBRAM', 'barra-ibram' ),
            __( 'Barra IBRAM', 'barra-ibram' ),
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
                'chooseLogo' => __( 'Selecionar logomarca', 'barra-ibram' ),
                'useLogo'    => __( 'Usar esta imagem', 'barra-ibram' ),
                'remove'     => __( 'Remover', 'barra-ibram' ),
                'copied'     => __( 'Copiado!', 'barra-ibram' ),
                'copy'       => __( 'Copiar', 'barra-ibram' ),
                'embedUrl'   => rest_url( BARRA_IBRAM_REST_NS . '/embed.js' ),
                'configUrl'  => rest_url( BARRA_IBRAM_REST_NS . '/config' ),
                'siteUrl'    => home_url(),
            )
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $s        = BG_Settings::get();
        $mode     = $s['mode'] === 'client' ? 'client' : 'host';
        $embed_url = rest_url( BARRA_IBRAM_REST_NS . '/embed.js' );
        ?>
        <div class="wrap bg-admin-wrap" data-bg-mode="<?php echo esc_attr( $mode ); ?>">
            <header class="bg-admin-header">
                <div class="bg-admin-brand">
                    <span class="dashicons dashicons-flag" aria-hidden="true"></span>
                    <div>
                        <h1><?php esc_html_e( 'Barra IBRAM', 'barra-ibram' ); ?></h1>
                        <p class="bg-admin-tag"><?php esc_html_e( 'Barra institucional do Instituto Brasileiro de Museus, distribuível para toda a rede de sites.', 'barra-ibram' ); ?></p>
                    </div>
                </div>
                <div class="bg-admin-actions">
                    <span class="bg-mode-pill bg-mode-host"><span class="dashicons dashicons-admin-site-alt3"></span> <?php esc_html_e( 'Modo hospedeiro', 'barra-ibram' ); ?></span>
                    <span class="bg-mode-pill bg-mode-client"><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Modo cliente', 'barra-ibram' ); ?></span>
                    <label class="bg-switch">
                        <input type="checkbox" id="bg-enable-toggle" form="bg-settings-form" name="<?php echo esc_attr( BARRA_GOVERNO_OPTION ); ?>[enabled]" value="1" <?php checked( $s['enabled'], 1 ); ?>>
                        <span class="bg-switch-slider"></span>
                        <span class="bg-switch-label"><?php esc_html_e( 'Barra ativa', 'barra-ibram' ); ?></span>
                    </label>
                </div>
            </header>

            <?php settings_errors(); ?>

            <div class="bg-admin-layout">
                <nav class="bg-admin-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Seções de configuração', 'barra-ibram' ); ?>">
                    <button type="button" class="bg-tab is-active" data-tab="distribution" role="tab" aria-selected="true">
                        <span class="dashicons dashicons-share"></span>
                        <?php esc_html_e( 'Distribuição', 'barra-ibram' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="general" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-admin-customizer"></span>
                        <?php esc_html_e( 'Geral', 'barra-ibram' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="logo" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-format-image"></span>
                        <?php esc_html_e( 'Logomarca', 'barra-ibram' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="menu" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-menu"></span>
                        <?php esc_html_e( 'Menu', 'barra-ibram' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="login" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php esc_html_e( 'Login', 'barra-ibram' ); ?>
                    </button>
                    <button type="button" class="bg-tab" data-tab="style" role="tab" aria-selected="false">
                        <span class="dashicons dashicons-art"></span>
                        <?php esc_html_e( 'Aparência', 'barra-ibram' ); ?>
                    </button>
                </nav>

                <form id="bg-settings-form" method="post" action="options.php" class="bg-admin-form">
                    <?php settings_fields( self::GROUP_SLUG ); ?>

                    <section class="bg-panel is-active" data-panel="distribution">
                        <?php $this->render_distribution_panel( $s, $embed_url ); ?>
                    </section>

                    <section class="bg-panel" data-panel="general">
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
                        <?php submit_button( __( 'Salvar alterações', 'barra-ibram' ), 'primary bg-save-btn', 'submit', false ); ?>
                        <span class="bg-footer-info"><?php
                            /* translators: %s: plugin version */
                            printf( esc_html__( 'Versão %s · Compatível com qualquer tema WordPress', 'barra-ibram' ), esc_html( BARRA_GOVERNO_VERSION ) );
                        ?></span>
                    </footer>
                </form>

                <aside class="bg-admin-preview" aria-label="<?php esc_attr_e( 'Prévia', 'barra-ibram' ); ?>">
                    <h2><?php esc_html_e( 'Prévia', 'barra-ibram' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Visualização aproximada de como a barra será renderizada no topo do site.', 'barra-ibram' ); ?></p>
                    <div id="bg-preview-root" class="bg-preview-root">
                        <?php $this->render_preview( $s ); ?>
                    </div>
                </aside>
            </div>
        </div>

        <dialog id="bg-help-modal" class="bg-modal">
            <div class="bg-modal-inner">
                <header class="bg-modal-header">
                    <h2 id="bg-modal-title"><?php esc_html_e( 'Ajuda', 'barra-ibram' ); ?></h2>
                    <button type="button" class="bg-modal-close" aria-label="<?php esc_attr_e( 'Fechar', 'barra-ibram' ); ?>">&times;</button>
                </header>
                <div id="bg-modal-body" class="bg-modal-body"></div>
                <footer class="bg-modal-footer">
                    <button type="button" class="button button-primary bg-modal-close-btn"><?php esc_html_e( 'Entendi', 'barra-ibram' ); ?></button>
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

    private function render_distribution_panel( $s, $embed_url ) {
        $option       = BARRA_GOVERNO_OPTION;
        $mode         = $s['mode'] === 'client' ? 'client' : 'host';
        $snippet_html = '<script src="' . esc_attr( $embed_url ) . '" async></script>';
        $snippet_wp   = "add_action( 'wp_head', function() {\n    echo '<script src=\"" . esc_attr( $embed_url ) . "\" async></script>';\n} );";
        ?>
        <h2><?php esc_html_e( 'Distribuição em rede', 'barra-ibram' ); ?></h2>
        <p class="bg-panel-desc">
            <?php esc_html_e( 'Instale o plugin uma única vez em um site central (hospedeiro) e faça com que a barra apareça automaticamente nos demais sites da rede IBRAM com uma única linha de script.', 'barra-ibram' ); ?>
        </p>

        <div class="bg-flow">
            <div class="bg-flow-step">
                <div class="bg-flow-icon"><span class="dashicons dashicons-admin-site-alt3"></span></div>
                <div>
                    <strong><?php esc_html_e( '1. Hospedeiro', 'barra-ibram' ); ?></strong>
                    <p><?php esc_html_e( 'Um único site central guarda a configuração oficial (logo, menu, cores).', 'barra-ibram' ); ?></p>
                </div>
            </div>
            <div class="bg-flow-arrow">→</div>
            <div class="bg-flow-step">
                <div class="bg-flow-icon"><span class="dashicons dashicons-rest-api"></span></div>
                <div>
                    <strong><?php esc_html_e( '2. Endpoint embed.js', 'barra-ibram' ); ?></strong>
                    <p><?php esc_html_e( 'O plugin expõe um script JS pronto para uso em qualquer site.', 'barra-ibram' ); ?></p>
                </div>
            </div>
            <div class="bg-flow-arrow">→</div>
            <div class="bg-flow-step">
                <div class="bg-flow-icon"><span class="dashicons dashicons-networking"></span></div>
                <div>
                    <strong><?php esc_html_e( '3. Clientes (60 sites)', 'barra-ibram' ); ?></strong>
                    <p><?php esc_html_e( 'Cada site consome o script e ganha a barra no topo automaticamente.', 'barra-ibram' ); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-field">
            <label><?php esc_html_e( 'Qual é o papel deste site na rede?', 'barra-ibram' ); ?></label>
            <?php $this->help_btn( 'mode' ); ?>
            <div class="bg-radio-cards">
                <label class="bg-radio-card">
                    <input type="radio" name="<?php echo esc_attr( $option ); ?>[mode]" value="host" <?php checked( $mode, 'host' ); ?>>
                    <span class="bg-radio-card-inner">
                        <span class="dashicons dashicons-admin-site-alt3"></span>
                        <strong><?php esc_html_e( 'Hospedeiro (sede)', 'barra-ibram' ); ?></strong>
                        <span><?php esc_html_e( 'Este site centraliza a configuração e fornece a barra para os demais. Instale neste modo em apenas UM site.', 'barra-ibram' ); ?></span>
                    </span>
                </label>
                <label class="bg-radio-card">
                    <input type="radio" name="<?php echo esc_attr( $option ); ?>[mode]" value="client" <?php checked( $mode, 'client' ); ?>>
                    <span class="bg-radio-card-inner">
                        <span class="dashicons dashicons-download"></span>
                        <strong><?php esc_html_e( 'Cliente (consome da sede)', 'barra-ibram' ); ?></strong>
                        <span><?php esc_html_e( 'Use este modo nos 60 sites satélites. Basta informar a URL do embed.js do hospedeiro.', 'barra-ibram' ); ?></span>
                    </span>
                </label>
            </div>
        </div>

        <!-- HOST MODE PANELS -->
        <div class="bg-mode-host-only">
            <div class="bg-field">
                <label><?php esc_html_e( 'URL pública do embed (gerada por este site)', 'barra-ibram' ); ?></label>
                <?php $this->help_btn( 'embedurl' ); ?>
                <div class="bg-copy-field">
                    <input type="text" readonly value="<?php echo esc_attr( $embed_url ); ?>" id="bg-embed-url">
                    <button type="button" class="button bg-copy-btn" data-copy-target="#bg-embed-url">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php esc_html_e( 'Copiar', 'barra-ibram' ); ?>
                    </button>
                </div>
                <p class="description">
                    <?php esc_html_e( 'Esta é a URL que os 60 sites IBRAM devem consumir. O conteúdo é servido com CORS aberto e cache de 5 minutos — qualquer alteração nas configurações propaga automaticamente.', 'barra-ibram' ); ?>
                </p>
            </div>

            <h3 class="bg-subheading"><?php esc_html_e( 'Como instalar nos 60 sites', 'barra-ibram' ); ?></h3>

            <div class="bg-snippet-block">
                <div class="bg-snippet-head">
                    <span class="dashicons dashicons-wordpress-alt"></span>
                    <strong><?php esc_html_e( 'Opção A — Sites WordPress IBRAM (recomendada)', 'barra-ibram' ); ?></strong>
                    <?php $this->help_btn( 'optionwp' ); ?>
                </div>
                <ol class="bg-snippet-steps">
                    <li><?php esc_html_e( 'Instale este mesmo plugin "Barra IBRAM" no site satélite.', 'barra-ibram' ); ?></li>
                    <li><?php esc_html_e( 'Em Distribuição, selecione "Cliente (consome da sede)".', 'barra-ibram' ); ?></li>
                    <li><?php
                        /* translators: %s: embed URL */
                        printf( esc_html__( 'Cole a URL acima no campo "URL do hospedeiro" e salve. Em poucos segundos a barra aparecerá no topo do site — com o mesmo logo, menu e cores configurados aqui.', 'barra-ibram' ), esc_html( $embed_url ) );
                    ?></li>
                </ol>
            </div>

            <div class="bg-snippet-block">
                <div class="bg-snippet-head">
                    <span class="dashicons dashicons-editor-code"></span>
                    <strong><?php esc_html_e( 'Opção B — Qualquer site (HTML puro, Drupal, estático etc.)', 'barra-ibram' ); ?></strong>
                    <?php $this->help_btn( 'optionhtml' ); ?>
                </div>
                <p class="description"><?php esc_html_e( 'Cole a linha abaixo antes do fechamento da tag </head> ou logo após a tag <body>:', 'barra-ibram' ); ?></p>
                <div class="bg-code-copy">
                    <pre><code id="bg-snippet-html"><?php echo esc_html( $snippet_html ); ?></code></pre>
                    <button type="button" class="button bg-copy-btn" data-copy-target="#bg-snippet-html">
                        <span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Copiar', 'barra-ibram' ); ?>
                    </button>
                </div>
            </div>

            <div class="bg-snippet-block">
                <div class="bg-snippet-head">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <strong><?php esc_html_e( 'Opção C — Snippet PHP (tema WordPress sem este plugin)', 'barra-ibram' ); ?></strong>
                    <?php $this->help_btn( 'optionphp' ); ?>
                </div>
                <p class="description"><?php esc_html_e( 'Adicione ao functions.php do tema, ou a um plugin de snippets:', 'barra-ibram' ); ?></p>
                <div class="bg-code-copy">
                    <pre><code id="bg-snippet-wp"><?php echo esc_html( $snippet_wp ); ?></code></pre>
                    <button type="button" class="button bg-copy-btn" data-copy-target="#bg-snippet-wp">
                        <span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Copiar', 'barra-ibram' ); ?>
                    </button>
                </div>
            </div>

            <div class="bg-callout">
                <span class="dashicons dashicons-lightbulb"></span>
                <div>
                    <strong><?php esc_html_e( 'Dica:', 'barra-ibram' ); ?></strong>
                    <?php esc_html_e( 'Qualquer mudança feita aqui (logo, links, cores) é propagada automaticamente para os 60 sites em até 5 minutos (tempo de cache do navegador). Para forçar atualização imediata, limpe o cache do navegador ou do CDN nos clientes.', 'barra-ibram' ); ?>
                </div>
            </div>
        </div>

        <!-- CLIENT MODE PANELS -->
        <div class="bg-mode-client-only">
            <div class="bg-field">
                <label for="bg-remote-url"><?php esc_html_e( 'URL do hospedeiro (embed.js da sede)', 'barra-ibram' ); ?></label>
                <?php $this->help_btn( 'remoteurl' ); ?>
                <input type="url"
                       id="bg-remote-url"
                       name="<?php echo esc_attr( $option ); ?>[remote_url]"
                       value="<?php echo esc_attr( $s['remote_url'] ); ?>"
                       placeholder="https://central.ibram.gov.br/wp-json/barra-ibram/v1/embed.js">
                <p class="description">
                    <?php esc_html_e( 'Cole aqui a URL fornecida pela sede. A barra passará a ser exibida automaticamente no topo deste site, sem necessidade de manter configurações locais.', 'barra-ibram' ); ?>
                </p>
            </div>

            <div class="bg-callout bg-callout-info">
                <span class="dashicons dashicons-info"></span>
                <div>
                    <strong><?php esc_html_e( 'No modo cliente', 'barra-ibram' ); ?></strong>
                    <p><?php esc_html_e( 'As abas "Logomarca", "Menu", "Login" e "Aparência" não têm efeito — toda a configuração vem da sede. Mantenha apenas a URL do hospedeiro preenchida.', 'barra-ibram' ); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_general_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Configurações gerais', 'barra-ibram' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Controle o comportamento da barra no topo do seu site.', 'barra-ibram' ); ?></p>

        <div class="bg-field">
            <label for="bg-bar-position"><?php esc_html_e( 'Posicionamento', 'barra-ibram' ); ?></label>
            <?php $this->help_btn( 'position' ); ?>
            <select name="<?php echo esc_attr( $option ); ?>[bar_position]" id="bg-bar-position" data-preview="bar_position">
                <option value="fixed" <?php selected( $s['bar_position'], 'fixed' ); ?>><?php esc_html_e( 'Fixa no topo (sempre visível)', 'barra-ibram' ); ?></option>
                <option value="sticky" <?php selected( $s['bar_position'], 'sticky' ); ?>><?php esc_html_e( 'Sticky (gruda ao rolar)', 'barra-ibram' ); ?></option>
                <option value="relative" <?php selected( $s['bar_position'], 'relative' ); ?>><?php esc_html_e( 'Estática (rola com a página)', 'barra-ibram' ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'Recomendado: Fixa. A barra sempre será renderizada no topo do site.', 'barra-ibram' ); ?></p>
        </div>

        <div class="bg-field">
            <label for="bg-max-width"><?php esc_html_e( 'Largura máxima do conteúdo (px)', 'barra-ibram' ); ?></label>
            <?php $this->help_btn( 'maxwidth' ); ?>
            <input type="number" min="600" max="3000" step="10" name="<?php echo esc_attr( $option ); ?>[max_width]" id="bg-max-width" value="<?php echo esc_attr( $s['max_width'] ); ?>" data-preview="max_width">
            <p class="description"><?php esc_html_e( 'Define o limite horizontal do conteúdo interno para acompanhar o grid do tema.', 'barra-ibram' ); ?></p>
        </div>
        <?php
    }

    private function render_logo_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Logomarca', 'barra-ibram' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Envie uma imagem personalizada ou mantenha a logomarca padrão gov.br.', 'barra-ibram' ); ?></p>

        <div class="bg-field bg-field-logo">
            <label><?php esc_html_e( 'Imagem da logomarca', 'barra-ibram' ); ?></label>
            <?php $this->help_btn( 'logo' ); ?>
            <div class="bg-logo-uploader">
                <div class="bg-logo-preview" id="bg-logo-preview">
                    <?php if ( ! empty( $s['logo_url'] ) ) : ?>
                        <img src="<?php echo esc_url( $s['logo_url'] ); ?>" alt="">
                    <?php endif; ?>
                </div>
                <div class="bg-logo-btns">
                    <button type="button" class="button button-secondary" id="bg-logo-select"><?php esc_html_e( 'Selecionar da biblioteca', 'barra-ibram' ); ?></button>
                    <button type="button" class="button-link bg-logo-remove" id="bg-logo-remove"><?php esc_html_e( 'Restaurar padrão', 'barra-ibram' ); ?></button>
                </div>
                <input type="hidden" name="<?php echo esc_attr( $option ); ?>[logo_url]" id="bg-logo-url" value="<?php echo esc_attr( $s['logo_url'] ); ?>" data-preview="logo_url">
                <input type="hidden" name="<?php echo esc_attr( $option ); ?>[logo_id]" id="bg-logo-id" value="<?php echo esc_attr( $s['logo_id'] ); ?>">
            </div>
        </div>

        <div class="bg-field">
            <label for="bg-logo-alt"><?php esc_html_e( 'Texto alternativo (alt)', 'barra-ibram' ); ?></label>
            <?php $this->help_btn( 'alt' ); ?>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[logo_alt]" id="bg-logo-alt" value="<?php echo esc_attr( $s['logo_alt'] ); ?>">
            <p class="description"><?php esc_html_e( 'Descreva brevemente a imagem para leitores de tela e acessibilidade.', 'barra-ibram' ); ?></p>
        </div>

        <div class="bg-field-row">
            <div class="bg-field">
                <label for="bg-logo-link"><?php esc_html_e( 'URL ao clicar na logo', 'barra-ibram' ); ?></label>
                <input type="url" name="<?php echo esc_attr( $option ); ?>[logo_link]" id="bg-logo-link" value="<?php echo esc_attr( $s['logo_link'] ); ?>" placeholder="https://">
            </div>
            <div class="bg-field">
                <label for="bg-logo-target"><?php esc_html_e( 'Abrir link em', 'barra-ibram' ); ?></label>
                <select name="<?php echo esc_attr( $option ); ?>[logo_target]" id="bg-logo-target">
                    <option value="_blank" <?php selected( $s['logo_target'], '_blank' ); ?>><?php esc_html_e( 'Nova aba', 'barra-ibram' ); ?></option>
                    <option value="_self" <?php selected( $s['logo_target'], '_self' ); ?>><?php esc_html_e( 'Mesma aba', 'barra-ibram' ); ?></option>
                </select>
            </div>
            <div class="bg-field">
                <label for="bg-logo-height"><?php esc_html_e( 'Altura (px)', 'barra-ibram' ); ?></label>
                <?php $this->help_btn( 'logoheight' ); ?>
                <input type="number" min="20" max="120" step="1" name="<?php echo esc_attr( $option ); ?>[logo_height]" id="bg-logo-height" value="<?php echo esc_attr( $s['logo_height'] ); ?>" data-preview="logo_height">
            </div>
        </div>
        <?php
    }

    private function render_menu_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Menu de acesso rápido', 'barra-ibram' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Adicione, reordene ou remova os links exibidos na barra. Em telas pequenas os links aparecem em um menu suspenso acessível.', 'barra-ibram' ); ?></p>

        <div class="bg-field bg-field-inline">
            <label class="bg-switch bg-switch-inline">
                <input type="checkbox" name="<?php echo esc_attr( $option ); ?>[menu_enabled]" value="1" <?php checked( $s['menu_enabled'], 1 ); ?>>
                <span class="bg-switch-slider"></span>
                <span class="bg-switch-label"><?php esc_html_e( 'Exibir menu de acesso rápido', 'barra-ibram' ); ?></span>
            </label>
            <?php $this->help_btn( 'menu' ); ?>
        </div>

        <div class="bg-field">
            <label for="bg-menu-title"><?php esc_html_e( 'Título do menu (mobile)', 'barra-ibram' ); ?></label>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[menu_title]" id="bg-menu-title" value="<?php echo esc_attr( $s['menu_title'] ); ?>">
            <p class="description"><?php esc_html_e( 'Texto exibido como cabeçalho do menu suspenso em dispositivos móveis.', 'barra-ibram' ); ?></p>
        </div>

        <div class="bg-field">
            <label><?php esc_html_e( 'Itens do menu', 'barra-ibram' ); ?></label>
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
                <?php esc_html_e( 'Adicionar item', 'barra-ibram' ); ?>
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
            <span class="bg-repeater-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Arraste para reordenar', 'barra-ibram' ); ?>"></span>
            <div class="bg-repeater-fields">
                <input type="text" name="<?php echo esc_attr( $option ); ?>[menu_items][<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Rótulo', 'barra-ibram' ); ?>">
                <input type="url" name="<?php echo esc_attr( $option ); ?>[menu_items][<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
                <select name="<?php echo esc_attr( $option ); ?>[menu_items][<?php echo esc_attr( $index ); ?>][target]">
                    <option value="_blank" <?php selected( $target, '_blank' ); ?>><?php esc_html_e( 'Nova aba', 'barra-ibram' ); ?></option>
                    <option value="_self" <?php selected( $target, '_self' ); ?>><?php esc_html_e( 'Mesma aba', 'barra-ibram' ); ?></option>
                </select>
            </div>
            <button type="button" class="button-link bg-repeater-remove" title="<?php esc_attr_e( 'Remover item', 'barra-ibram' ); ?>">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <?php
    }

    private function render_login_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        ?>
        <h2><?php esc_html_e( 'Botão de login', 'barra-ibram' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Configure o botão destacado à direita da barra, normalmente utilizado para o login único gov.br.', 'barra-ibram' ); ?></p>

        <div class="bg-field bg-field-inline">
            <label class="bg-switch bg-switch-inline">
                <input type="checkbox" name="<?php echo esc_attr( $option ); ?>[login_enabled]" value="1" <?php checked( $s['login_enabled'], 1 ); ?>>
                <span class="bg-switch-slider"></span>
                <span class="bg-switch-label"><?php esc_html_e( 'Exibir botão de login', 'barra-ibram' ); ?></span>
            </label>
            <?php $this->help_btn( 'login' ); ?>
        </div>

        <div class="bg-field">
            <label for="bg-login-label"><?php esc_html_e( 'Rótulo do botão', 'barra-ibram' ); ?></label>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[login_label]" id="bg-login-label" value="<?php echo esc_attr( $s['login_label'] ); ?>">
        </div>

        <div class="bg-field-row">
            <div class="bg-field">
                <label for="bg-login-url"><?php esc_html_e( 'URL do login', 'barra-ibram' ); ?></label>
                <input type="url" name="<?php echo esc_attr( $option ); ?>[login_url]" id="bg-login-url" value="<?php echo esc_attr( $s['login_url'] ); ?>" placeholder="https://">
            </div>
            <div class="bg-field">
                <label for="bg-login-target"><?php esc_html_e( 'Abrir link em', 'barra-ibram' ); ?></label>
                <select name="<?php echo esc_attr( $option ); ?>[login_target]" id="bg-login-target">
                    <option value="_blank" <?php selected( $s['login_target'], '_blank' ); ?>><?php esc_html_e( 'Nova aba', 'barra-ibram' ); ?></option>
                    <option value="_self" <?php selected( $s['login_target'], '_self' ); ?>><?php esc_html_e( 'Mesma aba', 'barra-ibram' ); ?></option>
                </select>
            </div>
        </div>

        <div class="bg-field">
            <label for="bg-login-icon"><?php esc_html_e( 'Ícone (classe Font Awesome)', 'barra-ibram' ); ?></label>
            <?php $this->help_btn( 'icon' ); ?>
            <input type="text" name="<?php echo esc_attr( $option ); ?>[login_icon]" id="bg-login-icon" value="<?php echo esc_attr( $s['login_icon'] ); ?>" placeholder="fas fa-user">
            <p class="description"><?php
                /* translators: %s: Font Awesome URL */
                printf(
                    wp_kses( __( 'Consulte a <a href="%s" target="_blank" rel="noopener">lista oficial de ícones Font Awesome 5</a>. Deixe em branco para ocultar.', 'barra-ibram' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ),
                    'https://fontawesome.com/v5/search?o=r&m=free'
                );
            ?></p>
        </div>
        <?php
    }

    private function render_style_panel( $s ) {
        $option = BARRA_GOVERNO_OPTION;
        $fields = array(
            'bar_bg'             => __( 'Fundo da barra', 'barra-ibram' ),
            'bar_text'           => __( 'Cor dos textos e ícones', 'barra-ibram' ),
            'bar_hover_bg'       => __( 'Fundo dos links ao passar o mouse', 'barra-ibram' ),
            'bar_hover_text'     => __( 'Texto dos links ao passar o mouse', 'barra-ibram' ),
            'bar_login_bg'       => __( 'Fundo do botão de login', 'barra-ibram' ),
            'bar_login_text'     => __( 'Texto do botão de login', 'barra-ibram' ),
            'bar_login_hover_bg' => __( 'Fundo do botão de login (hover)', 'barra-ibram' ),
        );
        ?>
        <h2><?php esc_html_e( 'Aparência e cores', 'barra-ibram' ); ?></h2>
        <p class="bg-panel-desc"><?php esc_html_e( 'Personalize a paleta da barra. Por padrão, as cores seguem o Design System gov.br.', 'barra-ibram' ); ?></p>

        <div class="bg-color-grid">
            <?php foreach ( $fields as $key => $label ) : ?>
                <div class="bg-field">
                    <label for="bg-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
                    <input type="text" class="bg-color-field" name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]" id="bg-<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>" data-preview="<?php echo esc_attr( $key ); ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-field">
            <button type="button" class="button" id="bg-reset-colors"><?php esc_html_e( 'Restaurar cores padrão gov.br', 'barra-ibram' ); ?></button>
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
        <button type="button" class="bg-help-btn" data-help="<?php echo esc_attr( $topic ); ?>" aria-label="<?php esc_attr_e( 'Ajuda sobre esta opção', 'barra-ibram' ); ?>">
            <span class="dashicons dashicons-editor-help"></span>
        </button>
        <?php
    }
}
