<?php
/**
 * Plugin Name: Easy Dashboard
 * Plugin URI: https://www.marcomilesi.com
 * Description: Refresh your WordPress dashboard with this new elegant, metro-based one.
 * Author: Marco Milesi
 * Author URI: https://marcomilesi.com
 * Version: 2.0.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: easy-dashboard
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Easy Dashboard Class
 */
class EasyDashboard {

    /**
     * Plugin version
     */
    const VERSION = '2.0.1';

    /**
     * Plugin slug
     */
    const SLUG = 'easy-dashboard';

    /**
     * Query argument used to reach the classic WordPress dashboard
     */
    const CLASSIC_ARG = 'ed_classic';

    /**
     * Gradient template shared by PHP and JavaScript
     */
    const GRADIENT_TEMPLATE = 'linear-gradient(135deg, %1$s 0%%, %2$s 100%%)';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('load-index.php', array($this, 'maybe_redirect_dashboard'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain(self::SLUG, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Register admin menu
     */
    public function register_admin_menu() {
        // Main dashboard page (hidden from menu)
        add_submenu_page(
            null,
            __('Dashboard', 'easy-dashboard'),
            __('Dashboard', 'easy-dashboard'),
            'read',
            'easy-dashboard',
            array($this, 'render_dashboard')
        );
    }

    /**
     * Redirect the classic dashboard to the Easy Dashboard welcome page.
     *
     * Skipped when the user explicitly asked for the classic dashboard, so the
     * default screen and its widgets always stay reachable.
     */
    public function maybe_redirect_dashboard() {
        if (isset($_GET[self::CLASSIC_ARG]) || !current_user_can('read')) {
            return;
        }

        /**
         * Filters whether the classic dashboard should be replaced.
         *
         * @param bool $should_redirect Whether to redirect to Easy Dashboard.
         */
        if (!apply_filters('easy_dashboard_should_redirect', true)) {
            return;
        }

        wp_safe_redirect(admin_url('admin.php?page=' . self::SLUG));
        exit;
    }

    /**
     * URL of the classic WordPress dashboard, bypassing the redirect
     */
    private function get_classic_dashboard_url() {
        return add_query_arg(self::CLASSIC_ARG, '1', admin_url('index.php'));
    }

    /**
     * Build a gradient from two colors, matching the JavaScript preview
     */
    private function build_gradient($primary, $secondary) {
        return sprintf(self::GRADIENT_TEMPLATE, $primary, $secondary);
    }

    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_admin_assets($hook) {
        if ('admin_page_easy-dashboard' !== $hook) {
            return;
        }

        // Ensure Dashicons are available on our custom admin page
        wp_enqueue_style('dashicons');

        wp_enqueue_style(
            self::SLUG . '-admin',
            plugin_dir_url(__FILE__) . 'assets/admin-style.css',
            array(),
            self::VERSION
        );

        // Only the active color scheme is dynamic, the rest lives in the stylesheet.
        wp_add_inline_style(self::SLUG . '-admin', $this->get_color_scheme_css());

        wp_enqueue_script(
            self::SLUG . '-admin',
            plugin_dir_url(__FILE__) . 'assets/admin.js',
            array('wp-i18n'),
            self::VERSION,
            true
        );

        wp_localize_script(
            self::SLUG . '-admin',
            'easyDashboardSettings',
            array(
                'gradientTemplate' => sprintf(self::GRADIENT_TEMPLATE, '%1$s', '%2$s'),
                'defaultPrimary'   => '#1d4ed8',
                'defaultSecondary' => '#0f172a',
            )
        );
    }

    /**
     * Inline CSS variables for the selected color scheme
     */
    private function get_color_scheme_css() {
        $colors = $this->get_color_scheme_vars(get_option('ed_color_scheme', 'dark'));

        return sprintf(
            '.easy-dashboard-wrap{--ed-primary:%1$s;--ed-secondary:%2$s;--ed-gradient:%3$s;}',
            $colors['primary'],
            $colors['secondary'],
            $colors['gradient']
        );
    }

    /**
     * Render the dashboard
     */
    public function render_dashboard() {
        if (!current_user_can('read')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'easy-dashboard'));
        }

        $current_user = wp_get_current_user();
        $display_name = $this->get_user_display_name($current_user);
        $tagline = get_option('ed_tagline');
        $site_icon = get_site_icon_url(128);

        if (!$site_icon) {
            $site_icon = includes_url('images/w-logo-blue.png');
        }

        echo '<div class="easy-dashboard-wrap">';
        echo '<div class="easy-dashboard-header">';
        echo '<div class="easy-dashboard-header-top">';
        echo '<div class="easy-dashboard-brand">';
        echo '<div class="easy-dashboard-brand-mark">';
        echo '<img src="' . esc_url($site_icon) . '" alt="' . esc_attr__('Site Logo', 'easy-dashboard') . '">';
        echo '</div>';
        echo '<div class="easy-dashboard-brand-copy">';
        echo '<p class="easy-dashboard-eyebrow">' . esc_html(get_bloginfo('name')) . '</p>';
        echo '<h1>' . sprintf(esc_html__('Hello, %s!', 'easy-dashboard'), esc_html($display_name)) . '</h1>';

        if (!empty($tagline)) {
            echo '<p class="easy-dashboard-tagline">' . esc_html($tagline) . '</p>';
        }

        echo '</div>';
        echo '</div>';
        echo '<div class="easy-dashboard-header-actions">';
        echo '<button type="button" class="easy-dashboard-search-btn" id="ed-search-btn" aria-label="' . esc_attr__('Search') . '">';
        echo '<span class="dashicons dashicons-search" aria-hidden="true"></span>';
        echo '<span id="ed-search-btn-label">' . esc_html__('Search commands and settings') . '</span>';
        echo '<span class="ed-kbd">Ctrl K</span>';
        echo '</button>';

        if (current_user_can('manage_options')) {
            echo '<div class="easy-dashboard-subactions">';
            echo '<button type="button" class="ed-settings-toggle" id="ed-settings-toggle" aria-label="' . esc_attr__('Edit dashboard settings', 'easy-dashboard') . '" title="' . esc_attr__('Edit dashboard settings', 'easy-dashboard') . '" aria-expanded="false" aria-controls="ed-settings-panel">';
            echo '<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>';
            echo '</button>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';

        if (current_user_can('manage_options')) {
            settings_errors('easy_dashboard_options');
            echo '<div class="ed-settings-panel" id="ed-settings-panel" aria-hidden="true">';
            echo '<div class="ed-settings-panel-header">';
            echo '<div class="ed-settings-panel-copy">';
            echo '<h2>' . esc_html__('Dashboard Settings', 'easy-dashboard') . '</h2>';
            echo '<p>' . esc_html__('Update the welcome message and visual theme without leaving this page.', 'easy-dashboard') . '</p>';
            echo '</div>';
            echo '<div class="ed-settings-actions">';
            echo '<a href="#" class="ed-settings-cancel">' . esc_html__('Close', 'easy-dashboard') . '</a>';
            echo '</div>';
            echo '</div>';
            echo '<div class="ed-settings-body">';
            $this->render_settings_section();
            echo '</div>';
            echo '</div>';
        }

        $this->render_updates_banner();

        global $menu;
        if (!is_array($menu)) {
            echo '</div>';
            return;
        }

        echo '<div class="easy-dashboard-grid">';
        foreach ($menu as $menu_item) {
            if (!is_array($menu_item) || !$this->user_can_access_menu_item($menu_item) || !$this->should_render_menu_item($menu_item)) {
                continue;
            }

            if ($this->is_custom_post_type_menu($menu_item[2])) {
                $this->render_menu_box($menu_item, false);
            }
        }
        echo '</div>';

        echo '<div class="easy-dashboard-small-grid">';
        $this->render_classic_dashboard_box();

        foreach ($menu as $menu_item) {
            if (!is_array($menu_item) || !$this->user_can_access_menu_item($menu_item) || !$this->should_render_menu_item($menu_item)) {
                continue;
            }

            if (!$this->is_custom_post_type_menu($menu_item[2]) && !$this->is_separator($menu_item[2])) {
                $this->render_menu_box($menu_item, true);
            }
        }
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Render the tile linking back to the classic WordPress dashboard.
     *
     * Kept as the first tile of the secondary grid so its position stays the
     * same no matter how many menu items other plugins add.
     */
    private function render_classic_dashboard_box() {
        printf(
            '<a href="%s" class="easy-dashboard-box small ed-classic-box" title="%s">
                <div class="dashicons easy-dashboard-icon dashicons-admin-home" aria-hidden="true"></div>
                <p class="easy-dashboard-label">%s</p>
            </a>',
            esc_url($this->get_classic_dashboard_url()),
            esc_attr__('Open the classic WordPress dashboard', 'easy-dashboard'),
            // Core string, so the tile is labelled in the current locale.
            esc_html__('Dashboard')
        );
    }

    /**
     * Render a banner with pending update counts, visible to admins only
     */
    private function render_updates_banner() {
        if (!current_user_can('update_plugins') && !current_user_can('update_themes') && !current_user_can('update_core')) {
            return;
        }

        $items = array();

        // Core
        $core_updates = get_site_transient('update_core');
        if (isset($core_updates->updates) && is_array($core_updates->updates)) {
            foreach ($core_updates->updates as $update) {
                if (isset($update->response) && $update->response === 'upgrade') {
                    $items[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url(admin_url('update-core.php')),
                        esc_html__('WordPress core', 'easy-dashboard')
                    );
                    break;
                }
            }
        }

        // Plugins
        if (current_user_can('update_plugins')) {
            $plugin_updates = get_site_transient('update_plugins');
            $plugin_count = isset($plugin_updates->response) ? count($plugin_updates->response) : 0;
            if ($plugin_count > 0) {
                $items[] = sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(admin_url('plugins.php?plugin_status=upgrade')),
                    sprintf(
                        _n('%d plugin', '%d plugins', $plugin_count, 'easy-dashboard'),
                        $plugin_count
                    )
                );
            }
        }

        // Themes
        if (current_user_can('update_themes')) {
            $theme_updates = get_site_transient('update_themes');
            $theme_count = isset($theme_updates->response) ? count($theme_updates->response) : 0;
            if ($theme_count > 0) {
                $items[] = sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(admin_url('themes.php')),
                    sprintf(
                        _n('%d theme', '%d themes', $theme_count, 'easy-dashboard'),
                        $theme_count
                    )
                );
            }
        }

        if (empty($items)) {
            return;
        }

        printf(
            '<div class="notice notice-warning inline" style="margin-bottom:20px;">
                <p><span class="dashicons dashicons-update" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"></span>%s %s</p>
            </div>',
            esc_html__('Pending updates:', 'easy-dashboard'),
            implode(', ', $items)
        );
    }

    /**
     * Get user display name safely
     */
    private function get_user_display_name($user) {
        if (!empty($user->display_name)) {
            return $user->display_name;
        }
        
        if (!empty($user->first_name)) {
            return $user->first_name;
        }
        
        return $user->user_login;
    }
    
    /**
     * Check if user can access menu item
     */
    private function user_can_access_menu_item($menu_item) {
        return isset($menu_item[1]) && current_user_can($menu_item[1]);
    }
    
    /**
     * Check if menu item is a separator
     */
    private function is_separator($slug) {
        return strpos($slug, 'separator') !== false;
    }
    
    /**
     * Check if menu item is for custom post type
     */
    private function is_custom_post_type_menu($slug) {
        return ($slug === 'edit.php' || $slug === 'upload.php' || strpos($slug, 'edit.php?post_type=') === 0);
    }
    
    /**
     * Render menu box
     */
    private function render_menu_box($menu_item, $is_small = false) {
        $url = $this->format_menu_link($menu_item[2]);
        $raw_icon = isset($menu_item[6]) ? $menu_item[6] : '';
        $label = $this->get_menu_label($menu_item);
        $size_class = $is_small ? ' small' : '';
        $quick_actions = $this->get_menu_quick_actions($menu_item[2]);

        // Use the WP Dashboard dashicon as the default fallback for missing icons
        $dashicon_class = 'dashicons-dashboard';

        if (is_string($raw_icon) && $raw_icon !== '') {
            // Try to find a dashicons class in the string
            if (preg_match('/(dashicons[-_\w]+)/', $raw_icon, $m)) {
                $dashicon_class = $m[1];
            } else {
                // Treat the raw value as a possible class name (clean it)
                $clean = preg_replace('/[^A-Za-z0-9_\- ]+/', '', $raw_icon);
                if (trim($clean) !== '') {
                    $dashicon_class = trim($clean);
                }
            }
        }

        // Check if dashicon_class contains data:image base64 and reset to dashboard icon
        if (strpos($dashicon_class, 'base64') !== false) {
            $dashicon_class = 'dashicons-dashboard';
        }

        if (!empty($quick_actions)) {
            printf(
                '<div class="easy-dashboard-box%s has-actions">
                    <a href="%s" class="easy-dashboard-box-main">
                        <div class="dashicons easy-dashboard-icon %s" aria-hidden="true"></div>
                        <p class="easy-dashboard-label">%s</p>
                    </a>
                    <div class="easy-dashboard-actions">%s</div>
                </div>',
                esc_attr($size_class),
                esc_url(admin_url($url)),
                esc_attr($dashicon_class),
                esc_html($label),
                $this->render_quick_actions_html($quick_actions)
            );

            return;
        }

        printf(
            '<a href="%s" class="easy-dashboard-box%s">
                <div class="dashicons easy-dashboard-icon %s" aria-hidden="true"></div>
                <p class="easy-dashboard-label">%s</p>
            </a>',
            esc_url(admin_url($url)),
            esc_attr($size_class),
            esc_attr($dashicon_class),
            esc_html($label)
        );
    }
    
    /**
     * Get menu label
     */
    private function get_menu_label($menu_item) {
        $slug = $menu_item[2];

        // Every other label comes from the WordPress menu itself, so it is
        // already translated for the current locale.

        // For custom post types, prefer the post type's plural name
        if ($this->is_custom_post_type_menu($slug)) {
            $post_type = $this->get_post_type_from_slug($slug);
            $post_type_object = get_post_type_object($post_type);

            if ($post_type_object && isset($post_type_object->labels->name)) {
                return $post_type_object->labels->name;
            }
        }

        // Update and moderation counters are appended to the label as markup
        // (<span class="awaiting-mod">, <span class="update-plugins">, ...) and
        // their text is localized, so drop the markup instead of the words.
        $label = preg_replace('/<span[\s\S]*$/i', '', $menu_item[0]);
        $label = wp_strip_all_tags($label);

        // Some menu items wrap the whole title in markup: keep their text.
        if (trim($label) === '') {
            $label = wp_strip_all_tags($menu_item[0]);
        }

        // Safety net for menu items that append a plain count with no markup.
        $label = preg_replace('/\s*\d+$/', '', $label);

        // Trim whitespace and return
        return trim($label);
    }
    
    /**
     * Format menu link
     */
    private function format_menu_link($slug) {
        if (strpos($slug, '.php') !== false) {
            return $slug;
        }
        
        return 'admin.php?page=' . $slug;
    }

    /**
     * Skip items that should not be shown as tiles
     */
    private function should_render_menu_item($menu_item) {
        return isset($menu_item[2]) && $menu_item[2] !== 'index.php';
    }

    /**
     * Get quick actions for content-oriented menu tiles
     */
    private function get_menu_quick_actions($slug) {
        if ($slug === 'upload.php') {
            $attachment_type = get_post_type_object('attachment');

            if (!$attachment_type) {
                return array();
            }

            return array(
                array(
                    'label' => $this->get_post_type_add_action_label($attachment_type, __('Add Media File')),
                    'url' => admin_url('media-new.php'),
                    'icon' => 'dashicons-upload'
                )
            );
        }

        if (!$this->is_custom_post_type_menu($slug) || $slug === 'upload.php') {
            return array();
        }

        $post_type = $this->get_post_type_from_slug($slug);
        $post_type_object = get_post_type_object($post_type);

        if (!$post_type_object) {
            return array();
        }

        $actions = array();

        $can_create = isset($post_type_object->cap->create_posts)
            ? current_user_can($post_type_object->cap->create_posts)
            : current_user_can($post_type_object->cap->edit_posts);

        if ($can_create) {
            $actions[] = array(
                'label' => $this->get_post_type_add_action_label($post_type_object, __('Add new', 'easy-dashboard')),
                'url' => admin_url($this->get_add_new_link_for_post_type($post_type)),
                'icon' => 'dashicons-plus-alt2'
            );
        }

        return $actions;
    }

    /**
     * Build quick action HTML for a tile
     */
    private function render_quick_actions_html($actions) {
        $html = '';

        foreach ($actions as $action) {
            if (!isset($action['label'], $action['url'])) {
                continue;
            }

            $icon = isset($action['icon']) ? preg_replace('/[^A-Za-z0-9_\- ]+/', '', $action['icon']) : 'dashicons-arrow-right-alt2';

            $html .= sprintf(
                '<a href="%s" class="easy-dashboard-action"><span class="dashicons %s" aria-hidden="true"></span><span>%s</span></a>',
                esc_url($action['url']),
                esc_attr(trim($icon)),
                esc_html($action['label'])
            );
        }

        return $html;
    }

    /**
     * Get a native localized post type label when available
     */
    private function get_post_type_action_label($post_type_object, $label_key, $fallback) {
        if (
            isset($post_type_object->labels) &&
            isset($post_type_object->labels->{$label_key}) &&
            !empty($post_type_object->labels->{$label_key})
        ) {
            return $post_type_object->labels->{$label_key};
        }

        return $fallback;
    }

    /**
     * Get the native localized add label for a post type
     */
    private function get_post_type_add_action_label($post_type_object, $fallback) {
        if (
            isset($post_type_object->labels) &&
            isset($post_type_object->labels->add_new_item) &&
            !empty($post_type_object->labels->add_new_item)
        ) {
            return $post_type_object->labels->add_new_item;
        }

        if (
            isset($post_type_object->labels) &&
            isset($post_type_object->labels->add_new) &&
            !empty($post_type_object->labels->add_new)
        ) {
            return $post_type_object->labels->add_new;
        }

        return $fallback;
    }

    /**
     * Resolve the add-new admin link for a post type
     */
    private function get_add_new_link_for_post_type($post_type) {
        if ($post_type === 'post') {
            return 'post-new.php';
        }

        return 'post-new.php?post_type=' . $post_type;
    }
    
    /**
     * Get post type from menu slug
     */
    private function get_post_type_from_slug($slug) {
        switch ($slug) {
            case 'edit.php':
                return 'post';
            case 'upload.php':
                return 'attachment';
            default:
                return str_replace('edit.php?post_type=', '', $slug);
        }
    }
    
    /**
     * Render settings section
     */
    private function render_settings_section() {
        $current_scheme = get_option('ed_color_scheme', 'dark');
        $custom_primary = $this->get_custom_color('ed_custom_primary', '#1d4ed8');
        $custom_secondary = $this->get_custom_color('ed_custom_secondary', '#0f172a');
        $color_schemes = array(
            'dark'   => __('Dark', 'easy-dashboard'),
            'midnight' => __('Midnight', 'easy-dashboard'),
            'deepblue' => __('Deep Blue', 'easy-dashboard'),
            'forest' => __('Forest', 'easy-dashboard'),
            'purple' => __('Purple', 'easy-dashboard'),
            'blue'   => __('Blue', 'easy-dashboard'),
            'green'  => __('Green', 'easy-dashboard'),
            'orange' => __('Orange', 'easy-dashboard'),
            'red'    => __('Red', 'easy-dashboard'),
            'custom' => __('Custom', 'easy-dashboard'),
        );

        echo '<form method="post" action="options.php">';
        settings_fields('easy_dashboard_options');
        do_settings_sections('easy_dashboard_options');

        echo '<table class="form-table">';
        echo '<tr valign="top">';
        echo '<th scope="row">' . esc_html__('Dashboard Tagline', 'easy-dashboard') . '</th>';
        echo '<td>';
        echo '<input type="text" name="ed_tagline" value="' . esc_attr(get_option('ed_tagline', '')) . '" class="regular-text" />';
        echo '<p class="easy-dashboard-field-description">' . esc_html__('Short message shown below the greeting.', 'easy-dashboard') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr valign="top">';
        echo '<th scope="row">' . esc_html__('Color Scheme', 'easy-dashboard') . '</th>';
        echo '<td>';
        echo '<fieldset>';
        echo '<div class="easy-dashboard-color-options">';
        foreach ($color_schemes as $scheme_key => $scheme_name) {
            if ($scheme_key === 'custom') {
                $colors = $this->build_color_scheme($custom_primary, $custom_secondary);
            } else {
                $colors = $this->get_color_scheme_vars($scheme_key);
            }

            $custom_class = $scheme_key === 'custom' ? ' is-custom' : '';
            echo '<div class="easy-dashboard-color-option' . esc_attr($custom_class) . '">';
            echo '<input type="radio" id="color_' . esc_attr($scheme_key) . '" name="ed_color_scheme" value="' . esc_attr($scheme_key) . '" data-primary="' . esc_attr($colors['primary']) . '" data-secondary="' . esc_attr($colors['secondary']) . '" data-gradient="' . esc_attr($colors['gradient']) . '"' . checked($current_scheme, $scheme_key, false) . ' />';
            echo '<label for="color_' . esc_attr($scheme_key) . '">';
            echo '<span class="easy-dashboard-color-preview" style="background:' . esc_attr($colors['gradient']) . ';"></span>';
            echo '<span>' . esc_html($scheme_name) . '</span>';
            echo '</label>';
            echo '</div>';
        }
        echo '</div>';
        $custom_visible_class = $current_scheme === 'custom' ? ' is-visible' : '';
        echo '<div class="easy-dashboard-custom-colors' . esc_attr($custom_visible_class) . '" id="ed-custom-colors">';
        echo '<div class="easy-dashboard-custom-colors-grid">';
        echo '<div class="easy-dashboard-color-field">';
        echo '<label for="ed_custom_primary">' . esc_html__('Start color', 'easy-dashboard') . '</label>';
        echo '<input type="color" id="ed_custom_primary" name="ed_custom_primary" value="' . esc_attr($custom_primary) . '" />';
        echo '</div>';
        echo '<div class="easy-dashboard-color-field">';
        echo '<label for="ed_custom_secondary">' . esc_html__('End color', 'easy-dashboard') . '</label>';
        echo '<input type="color" id="ed_custom_secondary" name="ed_custom_secondary" value="' . esc_attr($custom_secondary) . '" />';
        echo '</div>';
        echo '</div>';
        echo '<p class="easy-dashboard-field-description">' . esc_html__('Use the same value in both fields for a solid color, or two different values for a gradient.', 'easy-dashboard') . '</p>';
        echo '</div>';
        echo '</fieldset>';
        echo '<p class="easy-dashboard-field-description">' . esc_html__('Choose the gradient used in the welcome header and tiles.', 'easy-dashboard') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';

        echo '<div class="ed-settings-actions">';
        submit_button(__('Save changes', 'easy-dashboard'), 'primary', 'submit', false);
        echo '<a href="#" class="ed-settings-cancel">' . esc_html__('Cancel', 'easy-dashboard') . '</a>';
        echo '</div>';
        echo '</form>';
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'easy_dashboard_options',
            'ed_tagline',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        
        register_setting(
            'easy_dashboard_options',
            'ed_color_scheme',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'dark'
            )
        );

        register_setting(
            'easy_dashboard_options',
            'ed_custom_primary',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default' => '#1d4ed8'
            )
        );

        register_setting(
            'easy_dashboard_options',
            'ed_custom_secondary',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default' => '#0f172a'
            )
        );
        
        add_settings_section(
            'easy_dashboard_main',
            __('Dashboard Settings', 'easy-dashboard'),
            array($this, 'settings_section_callback'),
            'easy_dashboard_options'
        );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . esc_html__('Customize the appearance and behavior of your Easy Dashboard.', 'easy-dashboard') . '</p>';
    }
    
    /**
     * Get color scheme CSS variables
     */
    private function get_color_scheme_vars($scheme = 'dark') {
        if ($scheme === 'custom') {
            return $this->build_color_scheme(
                $this->get_custom_color('ed_custom_primary', '#1d4ed8'),
                $this->get_custom_color('ed_custom_secondary', '#0f172a')
            );
        }

        $schemes = $this->get_color_schemes();

        if (!isset($schemes[$scheme])) {
            $scheme = 'dark';
        }

        return $this->build_color_scheme($schemes[$scheme][0], $schemes[$scheme][1]);
    }

    /**
     * Read a stored custom color, falling back when the option is empty.
     *
     * sanitize_hex_color() stores an empty value for invalid input, so the
     * option can exist while holding nothing usable.
     */
    private function get_custom_color($option, $fallback) {
        $color = get_option($option, $fallback);

        return $color ? $color : $fallback;
    }

    /**
     * Build the color variables for a start/end color pair
     */
    private function build_color_scheme($primary, $secondary) {
        return array(
            'primary'   => $primary,
            'secondary' => $secondary,
            'gradient'  => $this->build_gradient($primary, $secondary),
        );
    }

    /**
     * Built-in color schemes as [start color, end color] pairs
     */
    private function get_color_schemes() {
        return array(
            'dark'     => array('#23272f', '#111318'),
            'midnight' => array('#232526', '#414345'),
            'deepblue' => array('#355C7D', '#6C5B7B'),
            'forest'   => array('#11998e', '#38ef7d'),
            'purple'   => array('#8e54e9', '#4776e6'),
            'blue'     => array('#396afc', '#2948ff'),
            'green'    => array('#56ab2f', '#a8e063'),
            'orange'   => array('#ff8008', '#ffc837'),
            'red'      => array('#ff5858', '#f857a6'),
            'aqua'     => array('#43cea2', '#185a9d'),
            'sunset'   => array('#ff9966', '#ff5e62'),
            'pink'     => array('#ff6a88', '#ff99ac'),
            'teal'     => array('#136a8a', '#267871'),
            'gold'     => array('#f7971e', '#ffd200'),
        );
    }
}

// Initialize the plugin
new EasyDashboard();
