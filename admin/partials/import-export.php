<?php
/**
 * Import/Export page template
 */
?>
<div class="wrap">
    <h1>برون‌بری / درون‌ریزی</h1>
    <h2>برون‌بری تنظیمات</h2>
    <p><a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=zarvan_ai_export'), 'zarvan_ai_export'); ?>" class="button button-primary">دانلود فایل تنظیمات</a></p>
    <h2>درون‌ریزی تنظیمات</h2>
    <form method="post" enctype="multipart/form-data" action="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=zarvan_ai_import'), 'zarvan_ai_import'); ?>">
        <input type="file" name="import_file" accept=".json" required>
        <?php submit_button('درون‌ریزی'); ?>
    </form>
</div>