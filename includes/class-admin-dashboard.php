<?php

class ACTLKBI_Admin_Dashboard {
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'create_admin_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_custom_admin_styles' ] ); // Enqueue custom styles
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

        add_submenu_page(
            'actlkbi-admin-dashboard',           // Parent slug (this is the plugin's main menu slug)
            'License Key Settings',          // Page title
            'Settings',                      // Submenu title
            'manage_options',                // Capability required
            'license-key-settings',          // Submenu slug
            [ __CLASS__, 'settings_page_html' ]  // Callback function to display the settings page
        );
    }

    // Register the settings
    public static function register_settings() {
        register_setting( 'license_key_settings_group', 'license_key_settings' );
        
        add_settings_section(
            'license_key_settings_section',
            'Email Settings',
            null,
            'license-key-settings'
        );
        
        add_settings_field(
            'license_email_from_name',
            'From Name',
            [ __CLASS__, 'license_email_from_name_field' ],
            'license-key-settings',
            'license_key_settings_section'
        );
        
        add_settings_field(
            'license_email_from_address',
            'From Address',
            [ __CLASS__, 'license_email_from_address_field' ],
            'license-key-settings',
            'license_key_settings_section'
        );
        
        add_settings_field(
            'license_email_message',
            'License Email Message',
            [ __CLASS__, 'license_email_message_field' ],
            'license-key-settings',
            'license_key_settings_section'
        );
    }
    
    public static function enqueue_admin_assets() {
        wp_enqueue_style( 'wclg-admin-styles', plugins_url( '../assets/css/admin-style.css', __FILE__ ) );
        wp_enqueue_script( 'wclg-admin-scripts', plugins_url( '../assets/js/license-scripts.js', __FILE__ ), ['jquery'], null, true );
        wp_enqueue_script( 'wclg-bot-scripts', plugins_url( '../assets/js/bot-scripts.js', __FILE__ ), ['jquery'], null, true );
        wp_enqueue_script( 'wclg-indicator-scripts', plugins_url( '../assets/js/indicator-scripts.js', __FILE__ ), ['jquery'], null, true );
        wp_enqueue_script( 'wclg-user-trade-data-scripts', plugins_url( '../assets/js/user-trade-script.js', __FILE__ ), ['jquery'], null, true );
        $gif_url = plugin_dir_url( __FILE__ ) . '../assets/img/spinner.gif';
    }

    // Display the field for the "From Name"
    public static function license_email_from_name_field() {
        $options = get_option( 'license_key_settings' );
        ?>
        <input type="text" name="license_key_settings[license_email_from_name]" value="<?php echo esc_attr( $options['license_email_from_name'] ?? 'Ace Trading Bots' ); ?>" />
        <?php
    }

    // Display the field for the "From Address"
    public static function license_email_from_address_field() {
        $options = get_option( 'license_key_settings' );
        ?>
        <input type="email" name="license_key_settings[license_email_from_address]" value="<?php echo esc_attr( $options['license_email_from_address'] ?? 'support@acetradingbots.com' ); ?>" />
        <?php
    }

    // Display the field for the "License Email Message"
    public static function license_email_message_field() {
        $options = get_option( 'license_key_settings' );
        ?>
        <textarea id="license_email_message" name="license_key_settings[license_email_message]" rows="5" cols="50"><?php echo esc_textarea( $options['license_email_message'] ?? 'Dear {customer_name},\n\nNew license has been created for you.\n\nLicense information is given below:\n\nProduct Name: {product_name}\nLicense Code: {license_key}\nLicense Type: Lifetime License' ); ?></textarea>
        <?php
    }

    // Display the settings page HTML
    public static function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>License Key Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'license_key_settings_group' );
                do_settings_sections( 'license-key-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

     // Enqueue custom styles for the settings page
     public static function enqueue_custom_admin_styles() {
        // Check if we are on the plugin settings page
        if ( isset( $_GET['page'] ) && 'license-key-settings' === $_GET['page'] ) {
            echo '<style>
                #license_email_message {
                    width: 100%;
                    max-width: 800px;
                    min-width: 500px;
                    height: 200px;
                }
            </style>';
        }
    }

    public static function display_admin_dashboard() {
        echo '<div class="wrap">';
        echo '<h2>Acer Trading LKBI</h2>';

        // Tab navigation
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="#tab-license-keys" class="lkbi-tab nav-tab nav-tab-active">License Keys</a>';
        echo '<a href="#tab-bot" class="lkbi-tab nav-tab">Bot</a>';
        echo '<a href="#tab-indicator" class="lkbi-tab nav-tab">Indicator</a>';
        echo '<a href="#tab-user-trade-data" class="lkbi-tab nav-tab">User Trade Data</a>';
        echo '</h2>';

        // Tab content
        echo '<div id="tab-license-keys" class="tab-content active">';
        include plugin_dir_path( __FILE__ ) . '../templates/license-keys-table.php';
        echo '</div>';

        echo '<div id="tab-bot" class="tab-content">';
        include plugin_dir_path( __FILE__ ) . '../templates/bot-table.php';
        echo '</div>';

        echo '<div id="tab-indicator" class="tab-content">';
        include plugin_dir_path( __FILE__ ) . '../templates/indicator-table.php';
        echo '</div>';

        echo '<div id="tab-user-trade-data" class="tab-content">';
        include plugin_dir_path( __FILE__ ) . '../templates/user-trade-table.php';
        echo '</div>';
        
        include plugin_dir_path( __FILE__ ) . '../templates/license-modal.php';
        include plugin_dir_path( __FILE__ ) . '../templates/bot-modal.php';
        include plugin_dir_path( __FILE__ ) . '../templates/indicator-modal.php';

        echo '</div>';
    }
}

?>
