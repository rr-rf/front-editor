<?php
$update_message = isset($form_settings['update_message']) ? $form_settings['update_message'] : __('Post updated successfully', FE_TEXT_DOMAIN);
$post_update_button_text = isset($form_settings['post_update_button_text']) ? $form_settings['post_update_button_text'] : __('Update', FE_TEXT_DOMAIN);
?>
<table class="form-table">
    <tr class="update-message">
        <th><?php esc_html_e('Post Update Message', FE_TEXT_DOMAIN); ?></th>
        <td>
            <textarea rows="3" cols="40" name="settings[update_message]"><?php echo esc_textarea($update_message); ?></textarea>
        </td>
    </tr>
    <tr class="post_update_button_text">
        <th><?php esc_html_e('Update Post Button text', FE_TEXT_DOMAIN); ?></th>
        <td>
            <input type="text" name="settings[post_update_button_text]" value="<?php echo esc_attr($post_update_button_text); ?>">
        </td>
    </tr>
</table>