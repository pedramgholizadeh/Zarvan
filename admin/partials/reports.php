<?php
/**
 * Reports page template
 */
?>
<div class="wrap">
    <h1>گزارش‌گیری زروان</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>عنوان</th>
                <th>تاریخ تولید</th>
                <th>تعداد کلمات</th>
                <th>کاربر</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $report) : ?>
                <tr>
                    <td><?php echo esc_html($report->post_title); ?></td>
                    <td><?php echo esc_html(date_i18n('Y/m/d H:i', strtotime($report->created_at))); ?></td>
                    <td><?php echo esc_html($report->word_count); ?></td>
                    <td><?php echo esc_html(get_user_by('id', $report->user_id)->display_name); ?></td>
                    <td>
                        <a href="<?php echo admin_url('post.php?post=' . $report->id . '&action=edit'); ?>" class="button">ویرایش</a>
                        <a href="<?php echo get_permalink($report->id); ?>" class="button">مشاهده</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>