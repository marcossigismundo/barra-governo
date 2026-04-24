/**
 * Barra do Governo Federal — Admin UI
 *
 * Responsible for:
 *  - Tab switching
 *  - Help modal content (per topic)
 *  - Logo media uploader
 *  - Menu items repeater (add/remove/sort)
 *  - Live preview of colors, logo, login label
 *  - Color pickers and reset-to-defaults
 */
(function ($) {
    'use strict';

    /* Help modal content — one entry per topic key referenced in PHP */
    var HELP = {
        position: {
            title: 'Posicionamento da barra',
            body:
                '<p>A barra sempre será renderizada <strong>no topo do site</strong>. O posicionamento controla o comportamento ao rolar a página:</p>' +
                '<ul>' +
                '<li><strong>Fixa:</strong> permanece visível enquanto o usuário rola. O conteúdo do tema é empurrado para baixo automaticamente.</li>' +
                '<li><strong>Sticky:</strong> começa na posição normal e gruda ao chegar no topo.</li>' +
                '<li><strong>Estática:</strong> rola junto com o conteúdo (não permanece visível).</li>' +
                '</ul>' +
                '<p>A opção <em>Fixa</em> é recomendada para seguir o padrão gov.br.</p>'
        },
        maxwidth: {
            title: 'Largura máxima do conteúdo',
            body:
                '<p>Define o limite horizontal do conteúdo interno da barra (logo, menu e botão). A barra em si sempre ocupa 100% da largura da tela.</p>' +
                '<p>Use o mesmo valor usado pelo container do seu tema para manter o alinhamento visual. Exemplo: <code>1200</code>, <code>1400</code>.</p>'
        },
        logo: {
            title: 'Logomarca',
            body:
                '<p>Substitua a logomarca padrão do gov.br por uma imagem personalizada — ideal para órgãos que possuem identidade visual própria.</p>' +
                '<ul>' +
                '<li>Formatos recomendados: <code>.svg</code>, <code>.png</code> ou <code>.webp</code> com fundo transparente.</li>' +
                '<li>Altura recomendada: entre <code>28px</code> e <code>48px</code>.</li>' +
                '<li>Use <strong>Restaurar padrão</strong> para voltar à logo oficial do Governo Federal.</li>' +
                '</ul>'
        },
        alt: {
            title: 'Texto alternativo (alt)',
            body:
                '<p>É uma descrição curta que leitores de tela falam para pessoas com deficiência visual. Também é exibido quando a imagem não carrega.</p>' +
                '<p>Exemplos bons: <code>Logo do Ministério da Saúde</code>, <code>Governo Federal — Brasil</code>.</p>'
        },
        logoheight: {
            title: 'Altura da logomarca',
            body:
                '<p>Controla a altura exibida da imagem em pixels. A largura é ajustada automaticamente mantendo a proporção original.</p>' +
                '<p>Em dispositivos móveis a altura é reduzida automaticamente para caber melhor na tela.</p>'
        },
        menu: {
            title: 'Menu de acesso rápido',
            body:
                '<p>Exibe uma lista horizontal de links ao lado da logomarca. Em telas pequenas, os links são agrupados em um menu suspenso acessível.</p>' +
                '<p>Desative se o seu site não precisa de links institucionais adicionais na barra.</p>'
        },
        menuitems: {
            title: 'Itens do menu',
            body:
                '<p>Arraste os itens pelo ícone <strong>☰</strong> para reordená-los. O rótulo é o texto exibido e a URL é o destino do link.</p>' +
                '<p>Itens com rótulo ou URL em branco são descartados ao salvar.</p>' +
                '<ul>' +
                '<li><strong>Nova aba:</strong> recomendado para links externos.</li>' +
                '<li><strong>Mesma aba:</strong> use para páginas internas do seu site.</li>' +
                '</ul>'
        },
        login: {
            title: 'Botão de login',
            body:
                '<p>O botão à direita é o destaque da barra — por padrão, leva ao login único gov.br (<code>https://sso.acesso.gov.br</code>).</p>' +
                '<p>Você pode apontar para qualquer URL de autenticação, inclusive a tela de login do próprio WordPress.</p>'
        },
        icon: {
            title: 'Ícone do botão',
            body:
                '<p>A barra utiliza <strong>Font Awesome 5</strong>. Para mudar o ícone, informe a classe correspondente.</p>' +
                '<p>Exemplos comuns:</p>' +
                '<ul>' +
                '<li><code>fas fa-user</code> — usuário (padrão)</li>' +
                '<li><code>fas fa-lock</code> — cadeado</li>' +
                '<li><code>fas fa-sign-in-alt</code> — entrar</li>' +
                '<li><code>fas fa-id-card</code> — identificação</li>' +
                '</ul>' +
                '<p>Deixe em branco para ocultar o ícone.</p>'
        },
        colors: {
            title: 'Paleta de cores',
            body:
                '<p>Use cores que garantam <strong>contraste adequado</strong> (WCAG AA: 4.5:1 para textos). As cores padrão seguem o Design System gov.br.</p>' +
                '<p>O botão <em>Restaurar cores padrão</em> volta todos os campos à paleta oficial.</p>'
        }
    };

    /* ---------------------------------------------------------------- */
    /* Tabs                                                              */
    /* ---------------------------------------------------------------- */
    function initTabs() {
        var $tabs   = $('.bg-tab');
        var $panels = $('.bg-panel');

        $tabs.on('click', function () {
            var key = $(this).data('tab');
            $tabs.removeClass('is-active').attr('aria-selected', 'false');
            $(this).addClass('is-active').attr('aria-selected', 'true');
            $panels.removeClass('is-active');
            $panels.filter('[data-panel="' + key + '"]').addClass('is-active');
        });
    }

    /* ---------------------------------------------------------------- */
    /* Help modal                                                        */
    /* ---------------------------------------------------------------- */
    function initModal() {
        var modal = document.getElementById('bg-help-modal');
        if (!modal) return;

        var titleEl = modal.querySelector('#bg-modal-title');
        var bodyEl  = modal.querySelector('#bg-modal-body');

        function open(topic) {
            var content = HELP[topic];
            if (!content) {
                content = { title: 'Ajuda', body: '<p>Sem informação adicional para esta opção.</p>' };
            }
            titleEl.textContent = content.title;
            bodyEl.innerHTML    = content.body;
            if (typeof modal.showModal === 'function') {
                modal.showModal();
            } else {
                modal.setAttribute('open', '');
            }
        }

        function close() {
            if (typeof modal.close === 'function') {
                modal.close();
            } else {
                modal.removeAttribute('open');
            }
        }

        $(document).on('click', '.bg-help-btn', function (e) {
            e.preventDefault();
            open($(this).data('help'));
        });

        $(modal).on('click', '.bg-modal-close, .bg-modal-close-btn', function () {
            close();
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) close();
        });
    }

    /* ---------------------------------------------------------------- */
    /* Logo uploader                                                     */
    /* ---------------------------------------------------------------- */
    function initLogoUploader() {
        var $btn = $('#bg-logo-select');
        var $remove = $('#bg-logo-remove');
        var $urlField = $('#bg-logo-url');
        var $idField = $('#bg-logo-id');
        var $preview = $('#bg-logo-preview');

        if (!$btn.length || typeof wp === 'undefined' || !wp.media) return;

        var frame;
        $btn.on('click', function (e) {
            e.preventDefault();
            if (!frame) {
                frame = wp.media({
                    title: (window.BG_Admin_i18n && BG_Admin_i18n.chooseLogo) || 'Selecionar logomarca',
                    button: { text: (window.BG_Admin_i18n && BG_Admin_i18n.useLogo) || 'Usar esta imagem' },
                    library: { type: 'image' },
                    multiple: false
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $urlField.val(attachment.url).trigger('change');
                    $idField.val(attachment.id);
                    $preview.html('<img src="' + attachment.url + '" alt="">');
                    updatePreview();
                });
            }
            frame.open();
        });

        $remove.on('click', function (e) {
            e.preventDefault();
            $urlField.val('https://barra.sistema.gov.br/v1/assets/govbr.webp').trigger('change');
            $idField.val(0);
            $preview.html('<img src="https://barra.sistema.gov.br/v1/assets/govbr.webp" alt="">');
            updatePreview();
        });
    }

    /* ---------------------------------------------------------------- */
    /* Menu repeater                                                     */
    /* ---------------------------------------------------------------- */
    function initRepeater() {
        var $list = $('#bg-menu-items');
        var $tpl  = $('#bg-menu-item-tpl');
        var $add  = $('#bg-add-menu-item');

        if (!$list.length) return;

        // sortable
        if ($.fn.sortable) {
            $list.sortable({
                handle: '.bg-repeater-handle',
                placeholder: 'ui-sortable-placeholder',
                forcePlaceholderSize: true,
                update: reindex
            });
        }

        $add.on('click', function (e) {
            e.preventDefault();
            var idx  = $list.children().length;
            var html = $tpl.html().replace(/__INDEX__/g, String(idx));
            $list.append(html);
        });

        $list.on('click', '.bg-repeater-remove', function (e) {
            e.preventDefault();
            $(this).closest('.bg-repeater-row').remove();
            reindex();
        });

        function reindex() {
            $list.children('.bg-repeater-row').each(function (i) {
                $(this).find('[name]').each(function () {
                    var name = $(this).attr('name');
                    if (!name) return;
                    $(this).attr('name', name.replace(/\[menu_items\]\[\d+\]/, '[menu_items][' + i + ']'));
                });
            });
        }
    }

    /* ---------------------------------------------------------------- */
    /* Color pickers + live preview                                      */
    /* ---------------------------------------------------------------- */
    var DEFAULT_COLORS = {
        bar_bg:             '#ffffff',
        bar_text:           '#1351b4',
        bar_hover_bg:       '#dce8f5',
        bar_hover_text:     '#0c326f',
        bar_login_bg:       '#1351b4',
        bar_login_text:     '#ffffff',
        bar_login_hover_bg: '#0c326f'
    };

    function debounce(fn, wait) {
        var t;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    function initColorPickers() {
        if ($.fn.wpColorPicker) {
            $('.bg-color-field').wpColorPicker({
                change: debounce(function () { updatePreview(); }, 80),
                clear:  function () { updatePreview(); }
            });
        }

        $('#bg-reset-colors').on('click', function (e) {
            e.preventDefault();
            Object.keys(DEFAULT_COLORS).forEach(function (k) {
                var $f = $('#bg-' + k);
                if ($f.length) {
                    if ($.fn.wpColorPicker) {
                        $f.wpColorPicker('color', DEFAULT_COLORS[k]);
                    } else {
                        $f.val(DEFAULT_COLORS[k]).trigger('change');
                    }
                }
            });
            updatePreview();
        });
    }

    /* ---------------------------------------------------------------- */
    /* Live preview                                                      */
    /* ---------------------------------------------------------------- */
    function updatePreview() {
        var $bar = $('#bg-preview-bar');
        if (!$bar.length) return;

        var varMap = {
            bar_bg:             '--bg-bar-bg',
            bar_text:           '--bg-bar-text',
            bar_hover_bg:       '--bg-bar-hover-bg',
            bar_hover_text:     '--bg-bar-hover-text',
            bar_login_bg:       '--bg-login-bg',
            bar_login_text:     '--bg-login-text',
            bar_login_hover_bg: '--bg-login-hover-bg'
        };

        Object.keys(varMap).forEach(function (key) {
            var v = $('#bg-' + key).val();
            if (v) $bar[0].style.setProperty(varMap[key], v);
        });

        var logoUrl = $('#bg-logo-url').val();
        if (logoUrl) $('#bg-preview-logo').attr('src', logoUrl);

        var h = parseInt($('#bg-logo-height').val(), 10);
        if (!isNaN(h)) $bar[0].style.setProperty('--bg-logo-height', h + 'px');

        var w = parseInt($('#bg-max-width').val(), 10);
        if (!isNaN(w)) $bar[0].style.setProperty('--bg-max-width', w + 'px');

        var loginLabel = $('#bg-login-label').val();
        if (loginLabel) $bar.find('.bgp-login').text(loginLabel);
    }

    function initPreviewBindings() {
        $(document).on('input change', '[data-preview]', updatePreview);
    }

    /* ---------------------------------------------------------------- */
    /* Boot                                                              */
    /* ---------------------------------------------------------------- */
    $(function () {
        initTabs();
        initModal();
        initLogoUploader();
        initRepeater();
        initColorPickers();
        initPreviewBindings();
    });

})(jQuery);
