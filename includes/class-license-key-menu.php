<?php
class License_Menu {
    public function __construct() {
        // Add menu item and endpoint
        add_filter('woocommerce_account_menu_items', [$this, 'add_license_key_menu_item']);
        add_action('init', [$this, 'add_license_key_endpoint']);
        add_action('woocommerce_account_license-keys_endpoint', [$this, 'license_key_menu_content']);
    }

    public function add_license_key_menu_item($menu_links) {
        // Add the License Keys menu item at the desired position (third in this case)
        $license_key_item = array( 'license-keys' => __('License Keys', 'my-account-license-menu') );
        
        // First, remove the 'license-keys' item if it exists
        if (isset($menu_links['license-keys'])) {
            unset($menu_links['license-keys']);
        }

        // Now re-add it to the third position (after 'dashboard' and 'orders')
        $menu_links = array_slice($menu_links, 0, 2, true) + $license_key_item + array_slice($menu_links, 2, NULL, true);

        return $menu_links;
    }

    public function add_license_key_endpoint() {
        add_rewrite_endpoint('license-keys', EP_ROOT | EP_PAGES);
    }

    public function license_key_menu_content() {
        echo '<h2>' . __('Your License Keys', 'my-account-license-menu') . '</h2>';
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'actlkbi_license_keys';
        $user_id = get_current_user_id();
    
        // Fetch license keys for the current user
        $license_keys = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE customer_id = %d", $user_id));
    
        if ($license_keys) {
            echo '<style>
                    .license-key-table {
                        width: 90% !important; /* Adjust the width here, you can use px or % */
                        max-width: 1200px; /* Optional: add a max-width to avoid overflowing on large screens */
                        margin: 0 auto; /* Center the table on the page */
                        border-collapse: collapse;
                    }
                    .license-key-table th, .license-key-table td {
                        border: 1px solid #ddd;
                        padding: 12px 15px; /* Increased padding for better spacing */
                        text-align: left;
                    }
                    .license-key-table th {
                        background-color: #f4f4f4;
                        font-weight: bold;
                        color: #333;
                    }
                    .license-key-table tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .license-key-table tr:hover {
                        background-color: #f1f1f1;
                    }
                    .license-key-table caption {
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                </style>';
    
            echo '<table class="license-key-table woocommerce-table shop_table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . __('Product Name', 'my-account-license-menu') . '</th>';
            echo '<th>' . __('License Key', 'my-account-license-menu') . '</th>';
            echo '<th>' . __('Status', 'my-account-license-menu') . '</th>';
            echo '<th>' . __('Max Domains', 'my-account-license-menu') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($license_keys as $key) {
                echo '<tr>';
                echo '<td>' . esc_html($key->product_name) . '</td>';
                echo '<td>' . esc_html($key->license_key) . '</td>';
                echo '<td>' . esc_html($key->status) . '</td>';
                echo '<td>' . esc_html($key->max_domains) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>' . __('No license keys found.', 'my-account-license-menu') . '</p>';
        }
    }
    
    
}
