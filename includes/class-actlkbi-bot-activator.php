<?php
class Bot_Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'actlkbi_bots';
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            bot_name varchar(100) NOT NULL,
            platform varchar(100) NOT NULL,
            strategy varchar(100) NOT NULL,
            bot_variables JSON,
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
