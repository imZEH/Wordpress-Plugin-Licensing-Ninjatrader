<?php
/*
Plugin Name: Acer Trading LKBI
Description: Acer Trading plugin that supports generation of license key and configuration for Bots and indicator.
Version: 1.0.4
*/

if (!defined('ABSPATH')) {
    die('Forbidden');
}


// Define plugin directory
define('ACTLKBI_PLUGIN_DIR', plugin_dir_path(__FILE__));


// Include required files
require_once ACTLKBI_PLUGIN_DIR . 'includes/class-actlkbi-license-key-activator.php';
require_once ACTLKBI_PLUGIN_DIR . 'includes/class-actlkbi-bot-activator.php';
require_once ACTLKBI_PLUGIN_DIR . 'includes/class-actlkbi-indicator-activator.php';
require_once ACTLKBI_PLUGIN_DIR . 'includes/class-actlkbi-trade-data-activator.php';

require_once ACTLKBI_PLUGIN_DIR . 'includes/class-license-key-handler.php';
require_once ACTLKBI_PLUGIN_DIR . 'includes/class-license-key-menu.php';
require_once ACTLKBI_PLUGIN_DIR . 'includes/class-admin-dashboard.php';

require_once ACTLKBI_PLUGIN_DIR . 'api/license-api.php';
require_once ACTLKBI_PLUGIN_DIR . 'api/bot-api.php';
require_once ACTLKBI_PLUGIN_DIR . 'api/indicator-api.php';
require_once ACTLKBI_PLUGIN_DIR . 'api/user-trade-data-api.php';

// Activation hook
register_activation_hook(__FILE__, ['License_Key_Activator', 'activate']);
register_activation_hook(__FILE__, ['Bot_Activator', 'activate']);
register_activation_hook(__FILE__, ['Indicator_Activator', 'activate']);
register_activation_hook(__FILE__, ['User_Trade_Activator', 'activate']);

// Check for updates on admin_init
add_action('admin_init', ['License_Key_Activator', 'check_update']);

// Initialize license key handler and display classes
add_action('plugins_loaded', function() {
    License_Key_Handler::init();
    ACTLKBI_Admin_Dashboard::init();
    new License_Menu();
});

?>
