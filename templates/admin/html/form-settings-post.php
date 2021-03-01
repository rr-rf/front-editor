<?php
$comment_status        = isset( $form_settings['comment_status'] ) ? $form_settings['comment_status'] : 'open';
?>
<table class="form-table">
    <tr class="setting">
        <th><?= __('Post Status', FE_TEXT_DOMAIN) ?></th>
        <td>
            <select name="settings[fe_post_status]" id="fe_settings_post_status">
                <?php
                $post_statuses = [
                    'publish' => __('Publish', FE_TEXT_DOMAIN),
                    'pending' => __('Pending', FE_TEXT_DOMAIN)
                ];

                $post_status_selected    = isset($form_settings['fe_post_status']) ? $form_settings['fe_post_status'] : 'publish';

                foreach ($post_statuses as $status => $label) {
                    printf('<option value="%s"%s>%s</option>', esc_attr($status), esc_attr(selected($post_status_selected, $status, false)), esc_html($label));
                }; ?>
            </select>
        </td>
    </tr>
    <tr class="setting">
        <th><?= __('Add new button', FE_TEXT_DOMAIN) ?></th>
        <td>
            <select name="settings[fe_add_new_button]" id="fe_add_new_button">
                <option value="display"><?= __("Display", FE_TEXT_DOMAIN) ?></option>
                <option value="always_display"><?= __("Always display", FE_TEXT_DOMAIN) ?></option>
                <option value="disable"><?= __("Disable this field", FE_TEXT_DOMAIN) ?></option>
            </select>
            <p class="description"><?= __('It will show add new button after post creation or on editing', FE_TEXT_DOMAIN) ?></p>
        </td>
    </tr>
    <tr class="setting">
        <th><?php esc_html_e('Comment Status', 'wp-user-frontend'); ?></th>
        <td>
            <select name="settings[comment_status]">
                <option value="open" <?php selected($comment_status, 'open'); ?>><?php esc_html_e('Open', FE_TEXT_DOMAIN); ?></option>
                <option value="closed" <?php selected($comment_status, 'closed'); ?>><?php esc_html_e('Closed', FE_TEXT_DOMAIN); ?></option>
            </select>
        </td>
    </tr>
</table>