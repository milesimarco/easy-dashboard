/*!
 * Easy Dashboard - admin scripts
 */
(function () {
    'use strict';

    var settings = window.easyDashboardSettings || {};

    /**
     * Build a gradient string using the same template as the PHP side.
     */
    function buildGradient(primary, secondary) {
        var template = settings.gradientTemplate || 'linear-gradient(135deg, %1$s 0%, %2$s 100%)';

        return template.replace('%1$s', primary).replace('%2$s', secondary);
    }

    /**
     * The command palette is only available when core registered its store.
     */
    function getCommandsDispatch() {
        if (!window.wp || !window.wp.data || typeof window.wp.data.dispatch !== 'function') {
            return null;
        }

        var store = window.wp.data.dispatch('core/commands');

        return store && typeof store.open === 'function' ? store : null;
    }

    function setupSearchButton() {
        var button = document.getElementById('ed-search-btn');

        if (!button) {
            return;
        }

        var commands = getCommandsDispatch();

        // Hide the button instead of shipping a control that does nothing.
        if (!commands) {
            button.parentNode.removeChild(button);
            return;
        }

        // Reuse the translated core strings at runtime.
        var label = document.getElementById('ed-search-btn-label');
        if (window.wp.i18n && typeof window.wp.i18n.__ === 'function') {
            var localizedLabel = window.wp.i18n.__('Search commands and settings');
            var localizedAria = window.wp.i18n.__('Search');

            if (label && localizedLabel) {
                label.textContent = localizedLabel;
            }

            if (localizedAria) {
                button.setAttribute('aria-label', localizedAria);
            }
        }

        button.addEventListener('click', function () {
            var store = getCommandsDispatch();

            if (store) {
                store.open();
            }
        });
    }

    function setupSettingsPanel() {
        var dashboardWrap = document.querySelector('.easy-dashboard-wrap');
        var settingsButton = document.getElementById('ed-settings-toggle');
        var settingsPanel = document.getElementById('ed-settings-panel');

        if (!settingsButton || !settingsPanel) {
            return;
        }

        var cancelButtons = settingsPanel.querySelectorAll('.ed-settings-cancel');
        var customColorsPanel = document.getElementById('ed-custom-colors');
        var colorInputs = settingsPanel.querySelectorAll('input[name="ed_color_scheme"]');
        var customSchemeInput = document.getElementById('color_custom');
        var customPrimaryInput = document.getElementById('ed_custom_primary');
        var customSecondaryInput = document.getElementById('ed_custom_secondary');

        function setPanelState(isOpen) {
            settingsPanel.classList.toggle('is-open', isOpen);
            settingsButton.classList.toggle('is-open', isOpen);
            settingsButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            settingsPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        function applyPreview(input) {
            if (!dashboardWrap || !input) {
                return;
            }

            dashboardWrap.style.setProperty('--ed-primary', input.getAttribute('data-primary'));
            dashboardWrap.style.setProperty('--ed-secondary', input.getAttribute('data-secondary'));
            dashboardWrap.style.setProperty('--ed-gradient', input.getAttribute('data-gradient'));
        }

        function syncCustomSchemeData() {
            if (!customSchemeInput || !customPrimaryInput || !customSecondaryInput) {
                return;
            }

            var primary = customPrimaryInput.value || settings.defaultPrimary;
            var secondary = customSecondaryInput.value || primary;
            var gradient = buildGradient(primary, secondary);

            customSchemeInput.setAttribute('data-primary', primary);
            customSchemeInput.setAttribute('data-secondary', secondary);
            customSchemeInput.setAttribute('data-gradient', gradient);

            var preview = customSchemeInput.parentNode.querySelector('.easy-dashboard-color-preview');
            if (preview) {
                preview.style.background = gradient;
            }
        }

        function setCustomColorsVisibility(isVisible) {
            if (customColorsPanel) {
                customColorsPanel.classList.toggle('is-visible', isVisible);
            }
        }

        settingsButton.addEventListener('click', function () {
            var isOpen = !settingsPanel.classList.contains('is-open');
            setPanelState(isOpen);

            if (isOpen) {
                var firstField = settingsPanel.querySelector('input,select,textarea');
                if (firstField) {
                    firstField.focus();
                }
            }
        });

        Array.prototype.forEach.call(cancelButtons, function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                setPanelState(false);
                settingsButton.focus();
            });
        });

        // Close the panel with Escape, like other WordPress inline panels.
        settingsPanel.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setPanelState(false);
                settingsButton.focus();
            }
        });

        syncCustomSchemeData();

        Array.prototype.forEach.call(colorInputs, function (input) {
            input.addEventListener('change', function () {
                setCustomColorsVisibility(input.value === 'custom');

                if (input.value === 'custom') {
                    syncCustomSchemeData();
                }

                applyPreview(input);
            });

            if (input.checked) {
                setCustomColorsVisibility(input.value === 'custom');
                applyPreview(input);
            }
        });

        [customPrimaryInput, customSecondaryInput].forEach(function (input) {
            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                syncCustomSchemeData();

                if (customSchemeInput) {
                    customSchemeInput.checked = true;
                    setCustomColorsVisibility(true);
                    applyPreview(customSchemeInput);
                }
            });
        });
    }

    function init() {
        setupSearchButton();
        setupSettingsPanel();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
