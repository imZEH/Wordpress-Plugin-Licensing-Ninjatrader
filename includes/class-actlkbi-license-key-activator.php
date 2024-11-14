<?php
class License_Key_Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'actlkbi_license_keys';
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            variation_id bigint(20) NULL,
            product_name varchar(255) NOT NULL,
            product_slug varchar(150) NOT NULL,
            customer_id bigint(20) NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            platform varchar(100) NULL,
            license_key varchar(100) NOT NULL,
            machine_id varchar(255) NOT NULL,
            max_domains bigint(20) NOT NULL,
            domains LONGTEXT NULL,
            status varchar(50) DEFAULT 'active',
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            updated_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
?>
