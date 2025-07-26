<?php
/**
 * Admin interface for Zarvan AI plugin
 */
class Zarvan_AI_Admin {
    /**
     * Initialize admin features
     */
    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [new Zarvan_AI_Settings(), 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_zarvan_ai_generate', [new Zarvan_AI_Content(), 'generate_content']);
        add_action('wp_ajax_zarvan_ai_save_form_data', [new Zarvan_AI_Content(), 'save_form_data']);
        add_action('wp_ajax_zarvan_ai_get_form_data', [new Zarvan_AI_Content(), 'get_form_data']);
        add_action('wp_ajax_zarvan_ai_export', [new Zarvan_AI_Import_Export(), 'export_settings']);
        add_action('wp_ajax_zarvan_ai_import', [new Zarvan_AI_Import_Export(), 'import_settings']);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'زروان Zarvan',
            'زروان Zarvan',
            'manage_options',
            'zarvan-ai',
            [$this, 'settings_page'],
            ZARVAN_AI_URL . 'assets/images/logo.png',
            4
        );

        add_submenu_page(
            'zarvan-ai',
            'تنظیمات',
            'تنظیمات',
            'manage_options',
            'zarvan-ai',
            [$this, 'settings_page']
        );

        add_submenu_page(
            'zarvan-ai',
            'گزارش‌گیری',
            'گزارش‌گیری',
            'edit_posts',
            'zarvan-ai-reports',
            [new Zarvan_AI_Reports(), 'display_reports']
        );

        add_submenu_page(
            'zarvan-ai',
            'برون‌بری / درون‌ریزی',
            'برون‌بری / درون‌ریزی',
            'manage_options',
            'zarvan-ai-import-export',
            [$this, 'import_export_page']
        );
    }

    /**
     * Settings page callback
     */
    public function settings_page() {
        require_once ZARVAN_AI_DIR . 'admin/partials/settings.php';
    }

    /**
     * Import/Export page callback
     */
    public function import_export_page() {
        require_once ZARVAN_AI_DIR . 'admin/partials/import-export.php';
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Load scripts on Zarvan AI pages and post/page editing screens
        if (strpos($hook, 'zarvan-ai') !== false || in_array($hook, ['post.php', 'post-new.php'])) {
            // Enqueue styles
            wp_enqueue_style('zarvan-ai-admin', ZARVAN_AI_URL . 'assets/css/admin.css', [], ZARVAN_AI_VERSION);
            wp_enqueue_script('jquery');

            // Enqueue SweetAlert
            wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], '11.0', true);

            // Enqueue admin script with jQuery dependency
            wp_enqueue_script('zarvan-ai-admin', ZARVAN_AI_URL . 'assets/js/admin.js', ['jquery', 'sweetalert'], ZARVAN_AI_VERSION, true);

            // Localize script
            wp_localize_script('zarvan-ai-admin', 'zarvanAi', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zarvan_ai_nonce')
            ]);
        }
    }
}
?>