<?php
/**
 * Settings registration for Zarvan AI plugin
 */
class Zarvan_AI_Settings {
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('zarvan_ai_settings', 'zarvan_ai_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);

        add_settings_section(
            'zarvan_ai_main',
            'تنظیمات اصلی',
            null,
            'zarvan-ai'
        );

        add_settings_field(
            'zarvan_ai_business_name',
            'نام کسب و کار',
            [$this, 'business_name_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_business_type',
            'نوع کسب و کار',
            [$this, 'business_type_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_business_description',
            'توضیحات کسب و کار',
            [$this, 'business_description_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_api_provider',
            'ارائه‌دهنده API',
            [$this, 'api_provider_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_api_model',
            'نام مدل (OpenRouter)',
            [$this, 'api_model_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_api_endpoint',
            'آدرس وب‌سرویس',
            [$this, 'api_endpoint_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_api_key',
            'کلید API',
            [$this, 'api_key_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_content_prompt',
            'پرامپت محتوا',
            [$this, 'content_prompt_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );

        add_settings_field(
            'zarvan_ai_daily_limit',
            'محدودیت روزانه',
            [$this, 'daily_limit_callback'],
            'zarvan-ai',
            'zarvan_ai_main'
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        $sanitized['business_name'] = sanitize_text_field($input['business_name'] ?? '');
        $sanitized['business_type'] = sanitize_text_field($input['business_type'] ?? '');
        $sanitized['business_description'] = sanitize_textarea_field($input['business_description'] ?? '');
        $sanitized['api_provider'] = in_array($input['api_provider'] ?? '', ['liara', 'openrouter']) ? $input['api_provider'] : 'liara';
        $sanitized['api_model'] = sanitize_text_field($input['api_model'] ?? 'deepseek/deepseek-r1-0528-qwen3-8b:free');
        $sanitized['api_endpoint'] = $sanitized['api_provider'] === 'openrouter' ? 'https://openrouter.ai/api/v1/chat/completions' : esc_url_raw($input['api_endpoint'] ?? '');
        $sanitized['api_key'] = sanitize_text_field($input['api_key'] ?? '');
        $sanitized['content_prompt'] = sanitize_textarea_field($input['content_prompt'] ?? '');
        $sanitized['daily_limit'] = absint($input['daily_limit'] ?? 0);
        return $sanitized;
    }

    /**
     * Business name field callback
     */
    public function business_name_callback() {
        $value = get_option('zarvan_ai_settings')['business_name'] ?? '';
        echo '<input type="text" id="zarvan_ai_business_name" name="zarvan_ai_settings[business_name]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * Business type field callback
     */
    public function business_type_callback() {
        $value = get_option('zarvan_ai_settings')['business_type'] ?? '';
        echo '<input type="text" id="zarvan_ai_business_type" name="zarvan_ai_settings[business_type]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * Business description field callback
     */
    public function business_description_callback() {
        $value = get_option('zarvan_ai_settings')['business_description'] ?? '';
        echo '<textarea id="zarvan_ai_business_description" name="zarvan_ai_settings[business_description]" rows="5" class="large-text">' . esc_textarea($value) . '</textarea>';
    }

    /**
     * API provider field callback
     */
    public function api_provider_callback() {
        $value = get_option('zarvan_ai_settings')['api_provider'] ?? 'liara';
        ?>
        <select id="zarvan_ai_api_provider" name="zarvan_ai_settings[api_provider]">
            <option value="liara" <?php selected($value, 'liara'); ?>>Liara</option>
            <option value="openrouter" <?php selected($value, 'openrouter'); ?>>OpenRouter</option>
        </select>
        <?php
    }

    /**
     * API model field callback
     */
    public function api_model_callback() {
        $value = get_option('zarvan_ai_settings')['api_model'] ?? 'deepseek/deepseek-r1-0528-qwen3-8b:free';
        echo '<input type="text" id="zarvan_ai_api_model" name="zarvan_ai_settings[api_model]" value="' . esc_attr($value) . '" class="regular-text" placeholder="مثال: deepseek/deepseek-r1-0528-qwen3-8b:free" />';
    }

    /**
     * API endpoint field callback
     */
    public function api_endpoint_callback() {
        $value = get_option('zarvan_ai_settings')['api_endpoint'] ?? '';
        echo '<input type="text" id="zarvan_ai_api_endpoint" name="zarvan_ai_settings[api_endpoint]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * API key field callback
     */
    public function api_key_callback() {
        $value = get_option('zarvan_ai_settings')['api_key'] ?? '';
        echo '<input type="text" id="zarvan_ai_api_key" name="zarvan_ai_settings[api_key]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * Content prompt field callback
     */
    public function content_prompt_callback() {
        $value = get_option('zarvan_ai_settings')['content_prompt'] ?? '';
        echo '<textarea id="zarvan_ai_content_prompt" name="zarvan_ai_settings[content_prompt]" rows="10" class="large-text">' . esc_textarea($value) . '</textarea>';
    }

    /**
     * Daily limit field callback
     */
    public function daily_limit_callback() {
        $value = get_option('zarvan_ai_settings')['daily_limit'] ?? 0;
        echo '<input type="number" id="zarvan_ai_daily_limit" name="zarvan_ai_settings[daily_limit]" value="' . esc_attr($value) . '" min="0" class="small-text" /> (0 به معنای بدون محدودیت)';
    }
}
?>