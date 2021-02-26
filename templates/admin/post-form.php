<form action id="fe-fromBuilder">
    <?php // wp nonce for security
    wp_nonce_field('admin_form_builder_nonce', 'admin_form_builder_nonce');
    ?>
    <div class="settings-header">
        <div>
            <p><?= __('Form Title', FE_TEXT_DOMAIN) ?></p>
            <input type="text" name="fe_title" value="<?php echo $post_ID !== 'new' ? get_the_title($post_ID) : __('Sample Form', FE_TEXT_DOMAIN) ?>" placeholder="<?= __('Sample Form', FE_TEXT_DOMAIN) ?>">
        </div>
        <div>
            <p><?= __('Shortcode', FE_TEXT_DOMAIN) ?></p>
            <?php
                $shortcode = '[fe_form id="%s"]';
            ?>
            <code><?php echo $post_ID !== 'new' ? sprintf($shortcode,$post_ID) : '' ?></code>
        </div>

        <input type="text" id="post_id" name="post_id" value="<?php echo $post_ID ?>" class="hidden">
        <button id="save-form-post" class="right_top"><?= __('Save', FE_TEXT_DOMAIN) ?></button>
    </div>
    <div class="settings-header">
        <h2 class="nav-tab-wrapper">
            <a href="#post-form-builder" class="nav-tab nav-tab-active"><?= __('Form Editor', FE_TEXT_DOMAIN) ?></a>
            <a href="#post-form-settings" class="nav-tab "><?= __('Settings', FE_TEXT_DOMAIN) ?></a>
        </h2>
    </div>

    <div class="tab-contents">
        <div id="post-form-builder" class="group active">
            <h3><?= __('Options', FE_TEXT_DOMAIN) ?></h3>
            <span><?= __('Select post type', FE_TEXT_DOMAIN) ?></span>
            <select name="post_type" id="fe_settings_post_type">
                <?php

                $post_types = get_post_types();
                $post_type_selected    = get_post_meta($post_ID,'fe_post_type',true) ? get_post_meta($post_ID,'fe_post_type',true) : 'post';
                unset($post_types['attachment']);
                unset($post_types['revision']);
                unset($post_types['nav_menu_item']);
                unset($post_types['wpuf_forms']);
                unset($post_types['wpuf_profile']);
                unset($post_types['wpuf_input']);
                unset($post_types['wpuf_subscription']);
                unset($post_types['custom_css']);
                unset($post_types['customize_changeset']);
                unset($post_types['wpuf_coupon']);
                unset($post_types['oembed_cache']);
                unset($post_types['fe_post_form']);
                unset($post_types['wp_block']);

                foreach ($post_types as $post_type) {
                    printf('<option value="%s"%s>%s</option>', esc_attr($post_type), esc_attr(selected($post_type_selected, $post_type, false)), esc_html($post_type));
                }; ?>
            </select>
            <div class="formBuilder-wrapper">
                <div id="form-builder"></div>
            </div>
        </div>

        <div id="post-form-settings" class="group  clearfix">
        </div>
    </div>

</form>