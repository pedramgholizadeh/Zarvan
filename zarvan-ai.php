<?php
/**
 * Plugin Name: زروان هوش مصنوعی | Zarvan AI
 * Plugin URI: https://example.com/zarvan-ai
 * Description: پلاگین تولید محتوای سئو شده با استفاده از وب‌سرویس هوش مصنوعی
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 * Text Domain: zarvan-ai
 * Domain Path: /languages
 * Requires PHP: 7.0
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Define plugin constants
 */
define('ZARVAN_AI_VERSION', '1.0.0');
define('ZARVAN_AI_DIR', plugin_dir_path(__FILE__));
define('ZARVAN_AI_URL', plugin_dir_url(__FILE__));

/**
 * Include required files
 */
require_once ZARVAN_AI_DIR . 'includes/class-zarvan-ai-activator.php';
require_once ZARVAN_AI_DIR . 'includes/class-zarvan-ai-deactivator.php';
require_once ZARVAN_AI_DIR . 'includes/class-zarvan-ai-settings.php';
require_once ZARVAN_AI_DIR . 'includes/class-zarvan-ai-content.php';
require_once ZARVAN_AI_DIR . 'includes/class-zarvan-ai-reports.php';
require_once ZARVAN_AI_DIR . 'includes/class-zarvan-ai-import-export.php';
require_once ZARVAN_AI_DIR . 'admin/zarvan-ai-admin.php';

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, ['Zarvan_AI_Activator', 'activate']);

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, ['Zarvan_AI_Deactivator', 'deactivate']);

/**
 * Initialize the plugin
 */
function zarvan_ai_init() {
    $plugin = new Zarvan_AI_Admin();
    $plugin->init();
}
add_action('plugins_loaded', 'zarvan_ai_init');
?>