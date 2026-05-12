<?php
/**
 * Plugin Name: Easy Dashboard
 * Plugin URI: https://www.marcomilesi.com
 * Description: Refresh your WordPress dashboard with this new elegant, metro-based one.
 * Author: Marco Milesi
 * Author URI: https://marcomilesi.com
 * Version: 1.7
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
    const VERSION = '1.3.1';
    
    /**
     * Plugin slug
     */
    const SLUG = 'easy-dashboard';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain(self::SLUG, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Register admin menu and handle dashboard redirect
     */
    public function register_admin_menu() {
        // Main dashboard page (hidden from menu)
        add_submenu_page(
            '', 
            __('Dashboard', 'easy-dashboard'), 
            __('Dashboard', 'easy-dashboard'), 
            'read', 
            'easy-dashboard', 
            array($this, 'render_dashboard')
        );
        
        // Settings page under Settings menu
        add_options_page(
            __('Easy Dashboard Settings', 'easy-dashboard'),
            __('Easy Dashboard', 'easy-dashboard'),
            'manage_options',
            'easy-dashboard-settings',
            array($this, 'render_settings_page')
        );
        
        // Redirect from default dashboard to custom dashboard
        global $pagenow;
        if ('index.php' === $pagenow && current_user_can('read')) {
            wp_safe_redirect(admin_url('admin.php?page=easy-dashboard'));
            exit;
        }
    }
    
    /**
     * Render the settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'easy-dashboard'));
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Easy Dashboard Settings', 'easy-dashboard') . '</h1>';
        $this->render_settings_section();
        echo '</div>';
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        if ('admin_page_easy-dashboard' !== $hook && 'settings_page_easy-dashboard-settings' !== $hook) {
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

        // Inline styles as fallback if CSS file doesn't exist
        $this->add_inline_styles();
    }
    
    /**
     * Add inline styles
     */
    private function add_inline_styles() {
        $color_scheme = get_option('ed_color_scheme', 'dark');
        $colors = $this->get_color_scheme_vars($color_scheme);

        $css = '
        .easy-dashboard-wrap {
            margin: 0 -20px 0 0;
            padding: 20px;
            min-height: calc(100vh - 160px);
            max-width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        .easy-dashboard-header {
            background: ' . $colors['gradient'] . ';
            color: white;
            padding: 40px 30px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 100%;
            box-sizing: border-box;
        }

        .easy-dashboard-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: 300;
            color: white;
            word-wrap: break-word;
        }

        /* Increase logo size */
        .easy-dashboard-header img {
            height: 80px !important;
            max-width: 220px !important;
            margin-left: 30px;
        }

        .easy-dashboard-tagline {
            font-size: 1.2em;
            opacity: 0.9;
            margin: 0;
            word-wrap: break-word;
        }

        .easy-dashboard-grid,
        .easy-dashboard-small-grid {
            display: grid;
            gap: 10px; /* Reduced gap */
            margin-bottom: 10px;
            max-width: 100%;
            overflow-x: hidden;
            padding: 15px 5px; /* Reduced padding */
        }

        .easy-dashboard-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .easy-dashboard-small-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 8px; /* Reduced gap */
        }

        .easy-dashboard-box {
            background: white;
            border-radius: 5px;
            padding: 20px 12px; /* Reduced padding */
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 1px solid #e1e1e1;
            position: relative;
            overflow: hidden;
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
        }

        .easy-dashboard-box:hover {
            transform: translateY(-5px);
            color: #333;
            text-decoration: none;
        }

        .easy-dashboard-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, ' . $colors['primary'] . ', ' . $colors['secondary'] . ');
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .easy-dashboard-box:hover::before {
            transform: scaleX(1);
        }

        .easy-dashboard-icon {
            font-size: 48px !important;
            width: auto !important;
            height: auto !important;
            margin-bottom: 15px;
            color: ' . $colors['primary'] . ';
            transition: color 0.3s ease;
            display: inline-block;
        }

        /* Image icons (when a menu item provides an image URL) */
        img.<a href="http://playground.local/wp-admin/admin.php?page=telegram_main" class="easy-dashboard-box small">
                    <img src="" class="easy-dashboard-icon" alt="Telegram">
                    <p class="easy-dashboard-label">Telegram</p>
                </a> {
            max-width: 48px;
            max-height: 48px;
            width: 48px;
            height: 48px;
            display: block;
            margin: 0 auto 15px;
            object-fit: contain;
        }

        .easy-dashboard-box:hover .easy-dashboard-icon {
            color: ' . $colors['secondary'] . ';
        }

        .easy-dashboard-label {
            font-size: 14px;
            font-weight: 500;
            margin: 0;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .easy-dashboard-box.small {
            padding: 14px 8px; /* Reduced padding */
        }

        .easy-dashboard-box.small .easy-dashboard-icon {
            font-size: 32px !important;
            margin-bottom: 10px;
        }

        .easy-dashboard-box.small img.easy-dashboard-icon {
            width: 32px;
            height: 32px;
            margin-bottom: 10px;
        }

        .easy-dashboard-box.small .easy-dashboard-label {
            font-size: 12px;
        }
        
        .easy-dashboard-settings {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e1e1e1;
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .easy-dashboard-settings summary {
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            padding: 10px 0;
            color: #333;
        }
        
        .easy-dashboard-settings details[open] summary {
            border-bottom: 1px solid #e1e1e1;
            margin-bottom: 20px;
        }
        
        .easy-dashboard-color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #ddd;
        }
        
        .easy-dashboard-color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .easy-dashboard-color-option {
            display: flex;
            align-items: center;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        
        .easy-dashboard-color-option input[type="radio"] {
            margin-right: 5px;
        }
        
        /* Prevent horizontal scrolling */
        #wpcontent {
            overflow-x: hidden;
        }
        
        .wrap {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        @media (max-width: 1200px) {
            .easy-dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .easy-dashboard-wrap {
                margin: 0 -10px 0 0;
                padding: 15px;
            }
            
            .easy-dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .easy-dashboard-small-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 10px;
            }
            
            .easy-dashboard-header {
                padding: 25px 20px;
            }
            
            .easy-dashboard-header h1 {
                font-size: 2em;
            }
            
            .easy-dashboard-tagline {
                font-size: 1em;
            }
            
            .easy-dashboard-color-options {
                flex-direction: column;
                gap: 5px;
            }
        }
        
        @media (max-width: 480px) {
            .easy-dashboard-grid,
            .easy-dashboard-small-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }
            
            .easy-dashboard-box {
                padding: 20px 15px;
            }
            
            .easy-dashboard-icon {
                font-size: 36px !important;
            }
            
            .easy-dashboard-box.small .easy-dashboard-icon {
                font-size: 28px !important;
            }
        }
        ';
    // Attach our inline styles to the plugin stylesheet handle so they reliably load
    wp_add_inline_style(self::SLUG . '-admin', $css);
    }
    
    /**
     * Render the dashboard
     */
    public function render_dashboard() {
        // Security check
        if (!current_user_can('read')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'easy-dashboard'));
        }
        
        $current_user = wp_get_current_user();
        $display_name = $this->get_user_display_name($current_user);
        $tagline = get_option('ed_tagline');

        // Get site icon or fallback to WP logo
        $site_icon = get_site_icon_url(64);
        if (!$site_icon) {
            $site_icon = includes_url('images/w-logo-blue.png');
        }

        echo '<div class="easy-dashboard-wrap">';
        echo '<div class="easy-dashboard-header">';
        printf(
            '<h1 style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                <span>%s</span>
                <img src="%s" alt="Site Logo" style="height: 50px; max-width: 150px; margin-left: 20px;">
            </h1>',
            sprintf(esc_html__('Hello, %s!', 'easy-dashboard'), esc_html($display_name)),
            esc_url($site_icon)
        );
        echo '<p class="easy-dashboard-tagline">' . esc_html($tagline) . '</p>';
        echo '</div>';
        
        // Get admin menu
        global $menu;
        if (!is_array($menu)) {
            return;
        }
        
        // Display custom post type boxes
        echo '<div class="easy-dashboard-grid">';
        foreach ($menu as $menu_item) {
            if (!is_array($menu_item) || !$this->user_can_access_menu_item($menu_item)) {
                continue;
            }
            
            if ($this->is_custom_post_type_menu($menu_item[2])) {
                $this->render_menu_box($menu_item, false);
            }
        }
        echo '</div>';
        
        // Display other menu items
        echo '<div class="easy-dashboard-small-grid">';
        foreach ($menu as $menu_item) {
            if (!is_array($menu_item) || !$this->user_can_access_menu_item($menu_item)) {
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
        return ($slug === 'edit.php' || $slug === 'upload.php' || strpos($slug, 'post_type') !== false);
    }
    
    /**
     * Render menu box
     */
    private function render_menu_box($menu_item, $is_small = false) {
        $url = $this->format_menu_link($menu_item[2]);
        $raw_icon = isset($menu_item[6]) ? $menu_item[6] : '';
        $label = $this->get_menu_label($menu_item);
        $size_class = $is_small ? ' small' : '';

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

        // Always use dashicons class
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

        // Keep friendly fixed labels for Comments and Plugins
        if ($slug === 'edit-comments.php') {
            return __('Comments', 'easy-dashboard');
        }

        if ($slug === 'plugins.php') {
            return __('Plugins', 'easy-dashboard');
        }

        // For custom post types, prefer the post type's plural name
        if ($this->is_custom_post_type_menu($slug)) {
            $post_type = $this->get_post_type_from_slug($slug);
            $post_type_object = get_post_type_object($post_type);

            if ($post_type_object && isset($post_type_object->labels->name)) {
                return $post_type_object->labels->name;
            }
        }

        // Default: strip tags and remove notification counts/trailing numbers
        $label = wp_strip_all_tags($menu_item[0]);

        // Remove patterns like "11 notifications" or "11 notification" (case-insensitive)
        $label = preg_replace('/\s*\d+\s*notifications?$/i', '', $label);

        // Remove any trailing number left (e.g. "Yoast SEO 11")
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
        );

        echo '<form method="post" action="options.php">';
        settings_fields('easy_dashboard_options');
        do_settings_sections('easy_dashboard_options');

        echo '<table class="form-table">';
        echo '<tr valign="top">';
        echo '<th scope="row">' . esc_html__('Dashboard Tagline', 'easy-dashboard') . '</th>';
        echo '<td><input type="text" name="ed_tagline" value="' . esc_attr(get_option('ed_tagline', '')) . '" class="regular-text" /></td>';
        echo '</tr>';

        echo '<tr valign="top">';
        echo '<th scope="row">' . esc_html__('Color Scheme', 'easy-dashboard') . '</th>';
        echo '<td>';
        echo '<fieldset>';
        foreach ($color_schemes as $scheme_key => $scheme_name) {
            $colors = $this->get_color_scheme_vars($scheme_key);
            echo '<br><label style="margin-right:18px;">';
            echo '<input type="radio" id="color_' . esc_attr($scheme_key) . '" name="ed_color_scheme" value="' . esc_attr($scheme_key) . '"' . checked($current_scheme, $scheme_key, false) . ' />';
            echo '<span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:' . esc_attr($colors['gradient']) . ';margin-right:6px;vertical-align:middle;border:1px solid #222;"></span>';
            echo esc_html($scheme_name);
            echo '</label>';
        }
        echo '</fieldset>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';

        submit_button();
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
        $schemes = array(
            'dark' => array(
                'primary' => '#23272f',
                'secondary' => '#111318',
                'gradient' => 'linear-gradient(135deg, #23272f 0%, #111318 100%)'
            ),
            'midnight' => array(
                'primary' => '#232526',
                'secondary' => '#414345',
                'gradient' => 'linear-gradient(135deg, #232526 0%, #414345 100%)'
            ),
            'deepblue' => array(
                'primary' => '#355C7D',
                'secondary' => '#6C5B7B',
                'gradient' => 'linear-gradient(135deg, #355C7D 0%, #6C5B7B 100%)'
            ),
            'forest' => array(
                'primary' => '#11998e',
                'secondary' => '#38ef7d',
                'gradient' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
            ),
            'purple' => array(
                'primary' => '#8e54e9',
                'secondary' => '#4776e6',
                'gradient' => 'linear-gradient(135deg, #8e54e9 0%, #4776e6 100%)'
            ),
            'blue' => array(
                'primary' => '#396afc',
                'secondary' => '#2948ff',
                'gradient' => 'linear-gradient(135deg, #396afc 0%, #2948ff 100%)'
            ),
            'green' => array(
                'primary' => '#56ab2f',
                'secondary' => '#a8e063',
                'gradient' => 'linear-gradient(135deg, #56ab2f 0%, #a8e063 100%)'
            ),
            'orange' => array(
                'primary' => '#ff8008',
                'secondary' => '#ffc837',
                'gradient' => 'linear-gradient(135deg, #ff8008 0%, #ffc837 100%)'
            ),
            'red' => array(
                'primary' => '#ff5858',
                'secondary' => '#f857a6',
                'gradient' => 'linear-gradient(135deg, #ff5858 0%, #f857a6 100%)'
            ),
            // Additional more colorful schemes
            'aqua' => array(
                'primary' => '#43cea2',
                'secondary' => '#185a9d',
                'gradient' => 'linear-gradient(135deg, #43cea2 0%, #185a9d 100%)'
            ),
            'sunset' => array(
                'primary' => '#ff9966',
                'secondary' => '#ff5e62',
                'gradient' => 'linear-gradient(135deg, #ff9966 0%, #ff5e62 100%)'
            ),
            'pink' => array(
                'primary' => '#ff6a88',
                'secondary' => '#ff99ac',
                'gradient' => 'linear-gradient(135deg, #ff6a88 0%, #ff99ac 100%)'
            ),
            'teal' => array(
                'primary' => '#136a8a',
                'secondary' => '#267871',
                'gradient' => 'linear-gradient(135deg, #136a8a 0%, #267871 100%)'
            ),
            'gold' => array(
                'primary' => '#f7971e',
                'secondary' => '#ffd200',
                'gradient' => 'linear-gradient(135deg, #f7971e 0%, #ffd200 100%)'
            ),
        );
        return isset($schemes[$scheme]) ? $schemes[$scheme] : $schemes['dark'];
    }
}

// Initialize the plugin
new EasyDashboard();
