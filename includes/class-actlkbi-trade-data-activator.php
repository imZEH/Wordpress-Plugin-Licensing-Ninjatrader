<?php

class User_Trade_Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'actlkbi_user_trade_data';
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) NOT NULL,
            variation_id bigint(20) NOT NULL,
            symbol varchar(100) NOT NULL,
            open_timestamp datetime NULL,
            close_timestamp datetime NULL,
            position varchar(50) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            side varchar(50) NOT NULL,
            pnl DECIMAL(10, 2) NOT NULL,
            quantity mediumint(9) NOT NULL,
            commission DECIMAL(10, 2) NULL,
            fees DECIMAL(10, 2) NULL,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            updated_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

 