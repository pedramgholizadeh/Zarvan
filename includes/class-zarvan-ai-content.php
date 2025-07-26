<?php
/**
 * Handles content generation via AI web service
 */
class Zarvan_AI_Content {
    /**
     * Add content generation hooks
     */
    public function __construct() {
        add_action('edit_form_after_title', [$this, 'add_generate_button'], 10, 1);
        add_action('wp_ajax_zarvan_ai_generate', [$this, 'generate_content']);
        add_action('wp_ajax_zarvan_ai_save_form_data', [$this, 'save_form_data']);
        add_action('wp_ajax_zarvan_ai_get_form_data', [$this, 'get_form_data']);
    }

    /**
     * Add content generation button to post/page editor
     */
    public function add_generate_button($post) {
        global $post_type;
        error_log('Zarvan AI: add_generate_button called for post type ' . $post_type . ', post ID ' . $post->ID);

        // Prevent duplicate buttons
        static $button_added = false;
        if ($button_added) {
            error_log('Zarvan AI: Button already added, skipping');
            return;
        }

        if (in_array($post_type, ['post', 'page'])) {
            echo '<button type="button" class="button button-primary" id="zarvan-ai-generate">تولید محتوا با زروان</button>';
            $button_added = true;
            error_log('Zarvan AI: Generate button added');
        }
    }

    /**
     * Handle AJAX content generation
     */
    public function generate_content() {
        error_log('Zarvan AI: generate_content called with data: ' . print_r($_POST, true));
        check_ajax_referer('zarvan_ai_nonce', 'nonce');
        error_log('Zarvan AI: Nonce verified');

        $settings = get_option('zarvan_ai_settings', []);
        if (!is_array($settings)) {
            error_log('Zarvan AI: Settings is not an array');
            wp_send_json_error(['message' => 'خطا: تنظیمات پلاگین نامعتبر است']);
        }

        if (empty($settings['business_name']) || empty($settings['business_type']) || empty($settings['business_description'])) {
            error_log('Zarvan AI: Incomplete business settings');
            wp_send_json_error(['message' => 'لطفاً ابتدا تنظیمات پلاگین را ثبت کنید.']);
        }

        // Check daily limit for editors
        if (current_user_can('edit_posts') && !current_user_can('manage_options')) {
            $limit = absint($settings['daily_limit']);
            if ($limit > 0) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'zarvan_ai_reports';
                $today = date('Y-m-d');
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND DATE(created_at) = %s",
                    get_current_user_id(),
                    $today
                ));

                if ($count >= $limit) {
                    error_log('Zarvan AI: Daily limit exceeded for user ' . get_current_user_id());
                    wp_send_json_error(['message' => 'محدودیت روزانه تولید محتوا رسیده است.']);
                }
            }
        }

        $prompt = !empty($settings['content_prompt']) ? $settings['content_prompt'] : file_get_contents(ZARVAN_AI_DIR . 'prompt/init.txt');
        $prompt .= "\n\nخروجی نهایی: \nلطفا فقط یک پاسخ JSON به شکل زیر ارسال کن، بدون هیچ توضیح یا متن اضافه:\n{\n \"title\": \"<h1>عنوان اصلی</h1>\",\n \"content\": \"<!-- بدنه کامل HTML مقاله -->\"\n}";

        $built_prompt = $this->build_prompt($prompt, $_POST);
        $api_provider = !empty($settings['api_provider']) ? $settings['api_provider'] : 'liara';
        $api_model = !empty($settings['api_model']) ? $settings['api_model'] : 'openai/gpt-4o-mini';

        $data = [
            'model' => $api_model,
            'messages' => [
                ['role' => 'user', 'content' => $built_prompt]
            ]
        ];

        $api_url = $api_provider === 'openrouter' ? 'https://openrouter.ai/api/v1/chat/completions' : ($settings['api_endpoint'] . '/chat/completions');
        $api_key = $settings['api_key'];
        error_log('Zarvan AI: Sending API request to ' . $api_url . ' with model ' . $api_model);
        $response = $this->call_api($api_url, $api_key, $data);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('Zarvan AI: API request failed: ' . $error_message);
            wp_send_json_error(['message' => 'خطا در ارتباط با وب‌سرویس: ' . $error_message]);
        }

        $body = wp_remote_retrieve_body($response);
        error_log('Zarvan AI: API response: ' . $body);
        $result = json_decode($body, true);

        if (!isset($result['choices'][0]['message']['content'])) {
            error_log('Zarvan AI: No valid content found in API response');
            wp_send_json_error(['message' => 'خطا: پاسخ API معتبر نیست']);
        }

        $content = $result['choices'][0]['message']['content'];
        
        $cleaned = trim($content);
        if (str_starts_with($cleaned, '```json')) {
            $cleaned = preg_replace('/^```json\s*/', '', $cleaned);
        }
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        

        $ai_response = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($ai_response['title']) && isset($ai_response['content'])) {
            $this->log_report($ai_response['title'], $ai_response['content']);
            wp_send_json_success([
                'title' => strip_tags($ai_response['title']),
                'content' => $ai_response['content'],
                'prompt' => $built_prompt,
                'api_url' => $api_url,
                'api_key' => $api_key
            ]);
        }

        error_log('Zarvan AI: Invalid JSON in response content');
        wp_send_json_error(['message' => $ai_response]);
    }

    /**
     * Save form data via AJAX
     */
    public function save_form_data() {
        check_ajax_referer('zarvan_ai_nonce', 'nonce');
        error_log('Zarvan AI: save_form_data called with data: ' . print_r($_POST, true));

        $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : [];
        $sanitized_data = [
            'audience' => isset($form_data['audience']) ? sanitize_text_field($form_data['audience']) : '',
            'content_type' => isset($form_data['content_type']) ? sanitize_text_field($form_data['content_type']) : '',
            'tone' => isset($form_data['tone']) ? sanitize_text_field($form_data['tone']) : '',
            'word_count' => isset($form_data['word_count']) ? absint($form_data['word_count']) : 0
        ];

        update_option('zarvan_ai_form_data', $sanitized_data);
        error_log('Zarvan AI: Form data saved: ' . print_r($sanitized_data, true));
        wp_send_json_success();
    }

    /**
     * Get saved form data via AJAX
     */
    public function get_form_data() {
        check_ajax_referer('zarvan_ai_nonce', 'nonce');
        error_log('Zarvan AI: get_form_data called');

        $form_data = get_option('zarvan_ai_form_data', []);
        if (!is_array($form_data)) {
            $form_data = [];
        }
        wp_send_json_success($form_data);
    }

    /**
     * Build prompt with dynamic data
     */
    private function build_prompt($prompt, $data) {
        $settings = get_option('zarvan_ai_settings', []);
        $replacements = [
            '#BRAND#' => sanitize_text_field($settings['business_name'] ?? ''),
            '#INDUSTRY#' => sanitize_text_field($settings['business_type'] ?? ''),
            '#ABOUT_BRAND#' => sanitize_textarea_field($settings['business_description'] ?? ''),
            '#MAIN_AUDIENCE#' => sanitize_text_field($data['audience'] ?? ''),
            '#CONTENTTONE#' => sanitize_text_field($data['tone'] ?? ''),
            '#TITLE#' => sanitize_text_field($data['title'] ?? ''),
            '#WORDCOUNT#' => absint($data['word_count'] ?? 0),
            '#CONTENTTYPE#' => sanitize_text_field($data['content_type'] ?? '')
        ];
        $built_prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);
        error_log('Zarvan AI: Built prompt: ' . $built_prompt);
        return $built_prompt;
    }

    /**
     * Call AI web service
     */
    private function call_api($api_url, $api_key, $data) {
        $response = wp_remote_post($api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ],
            'body' => json_encode($data),
            'timeout' => 300,
            'connect_timeout' => 20
        ]);
        error_log('Zarvan AI: API request sent to ' . $api_url);
        return $response;
    }

    /**
     * Log content generation to reports
     */
    private function log_report($title, $content) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zarvan_ai_reports';
        $word_count = str_word_count(strip_tags($content));
        $wpdb->insert($table_name, [
            'post_title' => sanitize_text_field(strip_tags($title)),
            'created_at' => current_time('mysql'),
            'word_count' => $word_count,
            'user_id' => get_current_user_id()
        ]);
        error_log('Zarvan AI: Report logged for title: ' . $title);
    }
}
?>