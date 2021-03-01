<?php
$form_settings = get_post_meta($post_ID, 'fe_form_settings', true);
?>
<form action id="fe-fromBuilder">
    <?php // wp nonce for security
    wp_nonce_field('admin_form_builder_nonce', 'admin_form_builder_nonce');
    ?>
    <div class="settings-header primary">
        <div>
            <p><?= __('Form Title', FE_TEXT_DOMAIN) ?></p>
            <input type="text" name="fe_title" value="<?php echo $post_ID !== 'new' ? get_the_title($post_ID) : __('Sample Form', FE_TEXT_DOMAIN) ?>" placeholder="<?= __('Sample Form', FE_TEXT_DOMAIN) ?>">
        </div>
        <div>
            <p><?= __('Shortcode', FE_TEXT_DOMAIN) ?></p>
            <?php
            $shortcode = '[fe_form id="%s"]';
            ?>
            <code><?php echo $post_ID !== 'new' ? sprintf($shortcode, $post_ID) : '' ?></code>
        </div>

        <input type="text" id="post_id" name="post_id" value="<?php echo $post_ID ?>" class="hidden">
        <button id="save-form-post" class="right_top"><?= __('Save', FE_TEXT_DOMAIN) ?></button>
    </div>
    <div class="settings-header">
        <fieldset>
            <h2 class="nav-tab-wrapper">
                <a href="#post-form-builder" class="nav-tab top nav-tab-active"><?= __('Form Editor', FE_TEXT_DOMAIN) ?></a>
                <a href="#post-form-settings" class="nav-tab top"><?= __('Settings', FE_TEXT_DOMAIN) ?></a>
            </h2>
        </fieldset>
    </div>

    <div class="tab-contents">
        <div id="post-form-builder" class="group top active">
            <h3><?= __('Options', FE_TEXT_DOMAIN) ?></h3>
            <span><?= __('Select post type', FE_TEXT_DOMAIN) ?></span>
            <select name="settings[fe_post_type]" id="fe_settings_post_type">
                <?php
                $post_types = get_post_types();
                $post_type_selected    = isset($form_settings['fe_post_type']) ? $form_settings['fe_post_type'] : 'post';
                unset($post_types['attachment']);
                unset($post_types['revision']);
                unset($post_types['nav_menu_item']);
                unset($post_types['fe_forms']);
                unset($post_types['fe_profile']);
                unset($post_types['fe_input']);
                unset($post_types['fe_subscription']);
                unset($post_types['custom_css']);
                unset($post_types['customize_changeset']);
                unset($post_types['fe_coupon']);
                unset($post_types['oembed_cache']);
                unset($post_types['fe_post_form']);
                unset($post_types['wp_block']);

                foreach ($post_types as $post_type) {
                    $post_type_name = $post_type;
                    if (!fe_fs()->is__premium_only()) {
                        $disabled = $post_type !== 'post' ? 'disabled' : '';
                        $post_type_name = $post_type !== 'post' ? $post_type . ' (PRO)' : $post_type;
                    }
                    printf('<option value="%s" %s %s>%s</option>', esc_attr($post_type), $disabled ?? '', esc_attr(selected($post_type_selected, $post_type, false)), esc_html($post_type_name));
                }; ?>
            </select>
            <div class="formBuilder-wrapper">
                <div id="form-builder"></div>
            </div>
        </div>

        <div id="post-form-settings" class="group top clearfix">
            <fieldset>
                <h2 id="fe-form-builder-settings-tabs" class="nav-tab-wrapper">
                    <a href="#fe-metabox-settings" class="nav-tab sub nav-tab-active">Post Settings</a>
                    <a href="#fe-metabox-settings-update" class="nav-tab sub ">Edit Settings</a>
                    <a href="#fe-metabox-submission-restriction" class="nav-tab sub ">Submission Restriction</a>
                    <a href="#fe-metabox-settings-payment" class="nav-tab sub ">Payment Settings</a>
                    <a href="#fe-metabox-settings-display" class="nav-tab sub ">Display Settings</a>
                    <a href="#fe-metabox-post_expiration" class="nav-tab sub ">Post Expiration</a>
                </h2>
            </fieldset>
            <div class="sub_field_groups_container">
                <div id="fe-metabox-settings" class="group sub active">
                    <?php require_once __DIR__ .'/html/form-settings-post.php' ?>
                </div>
            </div>

        </div>
    </div>

</form>