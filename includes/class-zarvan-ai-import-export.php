<?php
/**
 * Handles settings import/export
 */
class Zarvan_AI_Import_Export {
    /**
     * Export settings to JSON
     */
    public function export_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }

        $settings = get_option('zarvan_ai_settings');
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="zarvan-ai-settings.json"');
        echo json_encode($settings);
        exit;
    }

    /**
     * Import settings from JSON
     */
    public function import_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }

        if (isset($_FILES['import_file']) && $_FILES['import_file']['type'] === 'application/json') {
            $content = file_get_contents($_FILES['import_file']['tmp_name']);
            $settings = json_decode($content, true);
            if ($settings) {
                update_option('zarvan_ai_settings', $settings);
                wp_redirect(admin_url('admin.php?page=zarvan-ai-import-export&success=1'));
                exit;
            }
        }
        wp_die('خطا در آپلود فایل');
    }
}
?>