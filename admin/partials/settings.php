<?php
/**
 * Settings page for Zarvan AI plugin
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>تنظیمات زروان</h1>
    <div class="zarvan-ai-tabs">
        <a href="#business-settings" class="active">تنظیمات کسب و کار</a>
        <a href="#api-settings">تنظیمات وب‌سرویس</a>
        <a href="#content-settings">تنظیمات محتوا</a>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields('zarvan_ai_settings'); ?>
        
        <div id="business-settings" class="zarvan-ai-tab-content">
            <h2>تنظیمات کسب و کار</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="zarvan_ai_business_name">نام کسب و کار</label></th>
                    <td><input type="text" id="zarvan_ai_business_name" name="zarvan_ai_settings[business_name]" value="<?php echo esc_attr(get_option('zarvan_ai_settings')['business_name'] ?? ''); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zarvan_ai_business_type">نوع کسب و کار</label></th>
                    <td><input type="text" id="zarvan_ai_business_type" name="zarvan_ai_settings[business_type]" value="<?php echo esc_attr(get_option('zarvan_ai_settings')['business_type'] ?? ''); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zarvan_ai_business_description">توضیحات کسب و کار</label></th>
                    <td><textarea id="zarvan_ai_business_description" name="zarvan_ai_settings[business_description]" rows="5" class="large-text"><?php echo esc_textarea(get_option('zarvan_ai_settings')['business_description'] ?? ''); ?></textarea></td>
                </tr>
            </table>
        </div>

        <div id="api-settings" class="zarvan-ai-tab-content" style="display: none;">
            <h2>تنظیمات وب‌سرویس</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="zarvan_ai_api_provider">ارائه‌دهنده API</label></th>
                    <td>
                        <select id="zarvan_ai_api_provider" name="zarvan_ai_settings[api_provider]">
                            <option value="liara" <?php selected(get_option('zarvan_ai_settings')['api_provider'] ?? 'liara', 'liara'); ?>>Liara</option>
                            <option value="openrouter" <?php selected(get_option('zarvan_ai_settings')['api_provider'] ?? '', 'openrouter'); ?>>OpenRouter</option>
                        </select>
                    </td>
                </tr>
                <tr id="zarvan_ai_api_model_row" style="display: <?php echo (get_option('zarvan_ai_settings')['api_provider'] ?? 'liara') === 'openrouter' ? 'table-row' : 'none'; ?>;">
                    <th scope="row"><label for="zarvan_ai_api_model">نام مدل (OpenRouter)</label></th>
                    <td><input type="text" id="zarvan_ai_api_model" name="zarvan_ai_settings[api_model]" value="<?php echo esc_attr(get_option('zarvan_ai_settings')['api_model'] ?? 'deepseek/deepseek-r1-0528-qwen3-8b:free'); ?>" class="regular-text" placeholder="مثال: deepseek/deepseek-r1-0528-qwen3-8b:free" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zarvan_ai_api_endpoint">آدرس وب‌سرویس</label></th>
                    <td><input type="text" id="zarvan_ai_api_endpoint" name="zarvan_ai_settings[api_endpoint]" value="<?php echo esc_attr(get_option('zarvan_ai_settings')['api_endpoint'] ?? ''); ?>" class="regular-text" <?php echo (get_option('zarvan_ai_settings')['api_provider'] ?? 'liara') === 'openrouter' ? 'disabled' : ''; ?> /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zarvan_ai_api_key">کلید API</label></th>
                    <td><input type="text" id="zarvan_ai_api_key" name="zarvan_ai_settings[api_key]" value="<?php echo esc_attr(get_option('zarvan_ai_settings')['api_key'] ?? ''); ?>" class="regular-text" /></td>
                </tr>
            </table>
        </div>

        <div id="content-settings" class="zarvan-ai-tab-content" style="display: none;">
            <h2>تنظیمات محتوا</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="zarvan_ai_content_prompt">پرامپت محتوا</label></th>
                    <td><textarea id="zarvan_ai_content_prompt" name="zarvan_ai_settings[content_prompt]" rows="10" class="large-text"><?php echo esc_textarea(get_option('zarvan_ai_settings')['content_prompt'] ?? ''); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="zarvan_ai_daily_limit">محدودیت روزانه (برای ویرایشگران)</label></th>
                    <td><input type="number" id="zarvan_ai_daily_limit" name="zarvan_ai_settings[daily_limit]" value="<?php echo esc_attr(get_option('zarvan_ai_settings')['daily_limit'] ?? 0); ?>" min="0" class="small-text" /> (0 به معنای بدون محدودیت)</td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>

    <script>
        jQuery(document).ready(function($) {
            // Handle tab navigation
            $('.zarvan-ai-tabs a').on('click', function(e) {
                e.preventDefault();
                $('.zarvan-ai-tabs a').removeClass('active');
                $(this).addClass('active');
                $('.zarvan-ai-tab-content').hide();
                $($(this).attr('href')).show();
            });

            // Handle API provider change
            $('#zarvan_ai_api_provider').on('change', function() {
                var provider = $(this).val();
                if (provider === 'openrouter') {
                    $('#zarvan_ai_api_model_row').show();
                    $('#zarvan_ai_api_endpoint').prop('disabled', true).val('https://openrouter.ai/api/v1/chat/completions');
                } else {
                    $('#zarvan_ai_api_model_row').hide();
                    $('#zarvan_ai_api_endpoint').prop('disabled', false).val('');
                }
            });
        });
    </script>
</div>