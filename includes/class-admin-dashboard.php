<?php

class ACTLKBI_Admin_Dashboard {
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'create_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    public static function create_admin_menu() {
        add_menu_page(
            'Acer Trading LKBI',
            'Acer Trading LKBI',
            'manage_options',
            'actlkbi-admin-dashboard',
            [ __CLASS__, 'display_admin_dashboard' ],
            'dashicons-admin-network',
            6
        );
    }
    
    public static function enqueue_admin_assets() {
        wp_enqueue_style( 'wclg-admin-styles', plugins_url( '../assets/css/admin-style.css', __FILE__ ) );
        wp_enqueue_script( 'wclg-admin-scripts', plugins_url( '../assets/js/admin-scripts.js', __FILE__ ), ['jquery'], null, true );
        $gif_url = plugin_dir_url( __FILE__ ) . '../assets/img/spinner.gif';
        wp_localize_script('my_admin_script', 'myPluginAjax', [
            'nonce' => wp_create_nonce('my_plugin_nonce'), // Create nonce for security
            'api_url' => esc_url(rest_url('v1/get_licenses/')) // REST API URL
        ]);
    }

    public static function display_admin_dashboard() {
        echo '<div class="wrap">';
        echo '<h2>Acer Trading LKBI</h2>';

        // Tab navigation
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="#tab-license-keys" class="nav-tab nav-tab-active">License Keys</a>';
        echo '<a href="#tab-bot" class="nav-tab">Bot</a>';
        echo '<a href="#tab-indicator" class="nav-tab">Indicator</a>';
        echo '</h2>';

        // Tab content
        echo '<div id="tab-license-keys" class="tab-content active">';
        include plugin_dir_path( __FILE__ ) . '../templates/license-keys-table.php';
        echo '</div>';

        echo '<div id="tab-bot" class="tab-content">';
        echo '<p>Bot content goes here...</p>';
        echo '</div>';

        echo '<div id="tab-indicator" class="tab-content">';
        echo '<p>Indicator content goes here...</p>';
        echo '</div>';
        
        include plugin_dir_path( __FILE__ ) . '../templates/license-modal.php';

        echo '</div>';
    }
}

?>
