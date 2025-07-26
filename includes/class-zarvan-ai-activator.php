<?php
/**
 * Handles plugin activation tasks
 */
class Zarvan_AI_Activator {
    /**
     * Run during plugin activation
     */
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zarvan_ai_reports';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_title varchar(255) NOT NULL,
            created_at datetime NOT NULL,
            word_count int NOT NULL,
            user_id bigint(20) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Set default options
        if (!get_option('zarvan_ai_settings')) {
            update_option('zarvan_ai_settings', [
                'business_name' => '',
                'business_type' => '',
                'business_description' => '',
                'api_provider' => 'liara',
                'api_endpoint' => '',
                'api_key' => '',
                'content_prompt' => '',
                'daily_limit' => 0
            ]);
        }
    }
}
?>