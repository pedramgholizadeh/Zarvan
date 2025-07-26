<?php
/**
 * Handles content generation reports
 */
class Zarvan_AI_Reports {
    /**
     * Display reports page
     */
    public function display_reports() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zarvan_ai_reports';
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

        require_once ZARVAN_AI_DIR . 'admin/partials/reports.php';
    }
}
?>