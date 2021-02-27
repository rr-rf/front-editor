<?php
$fields_list = json_decode(get_post_meta($attributes['id'], 'formBuilderData', true), true);
$form_settings = get_post_meta($attributes['id'], 'fe_form_settings', true);
$form_id = $attributes['id'] ?? 0;
?>
<form class="bfe-editor" id="bfe-editor">
    <div class="hidden-fields">
        <?php if ($form_id) : ?>
            <input type="text" name="form_id" value="<?= $form_id ?>">
            <input type="text" name="post_id" value="<?= $post_id ?>">
            <input type="text" name="editor_post_id" value="<?= $post_id ?>">
            <?php
            foreach ($form_settings as $name => $value) {
                printf('<input type="text" name="%s" value="%s">', $name, $value);
            }
            wp_nonce_field('bfe_nonce')
            ?>
        <?php endif; ?>
    </div>

    <div id="bfe-editor-block-header">
        <div class="sub-header top">
            <button class="editor-button big" id="save-editor-block" title="<?php echo $button_text ?>"><?php echo $button_text ?></button>
            <?php
            $add_new_button = $attributes['add_new_button'] ?? false;
            if (($post_id !== 'new' && $add_new_button !== 'disable') || $add_new_button === 'always_display') : ?>
                <a target="_blank" class="editor-button" href="<?= $new_post_link ?>" title="<?= __('Add new', FE_TEXT_DOMAIN) ?>"><?= __('Add new', FE_TEXT_DOMAIN) ?></a>
            <?php endif; ?>
            <a target="_blank" class="editor-button view-page <?php echo $post_id === 'new' ? 'hide' : ''; ?>" href="<?php the_permalink($post_id) ?? ''; ?>" title="<?php echo __('View Post', FE_TEXT_DOMAIN) ?>">
                <img src="<?= FE_PLUGIN_URL . '/assets/img/see.svg' ?>" class="button-icon">
            </a>
        </div>
    </div>
    <div class="wrapper">
        <div class="column">
            <?php
            if (!empty($fields_list)) {
                foreach ($fields_list as $field) {
                    switch ($field['type']) {
                        case 'post_title':
                            printf('<input id="post_title" name="post_title" type="text" placeholder="%s" value="%s">', __('Add Title', FE_TEXT_DOMAIN), get_the_title($post_id));
                            break;
                        case 'post_content_editor_js':
                            echo '<div id="bfe-editor-block" post_id="<?= $post_id; ?>"></div>';
                            break;
                        default:
                            do_action('bfe_editor_on_front_field_adding', $post_id, $attributes, $field);
                            break;
                    }
                }
            }
            ?>
        </div>
    </div>

</form>