<?php

/**
 * formBuilder Tax Fields
 *
 * @package BFE;
 */

namespace BFE\Field;

defined('ABSPATH') || exit;


class TaxonomiesFields
{
    public static function init()
    {
        add_filter('admin_post_form_formBuilder_settings', [__CLASS__, 'add_field_settings']);

        // category selection addon
        add_action('bfe_editor_on_front_field_adding', [__CLASS__, 'front_tax_select'], 10, 3);
        add_filter('bfe_ajax_before_front_editor_post_update_or_creation', [__CLASS__, 'add_tax_on_save_and_check'], 10, 3);
    }

    /**
     * Adding in backend forBuilder field
     *
     * @param [type] $data
     * @return void
     */
    public static function add_field_settings($data)
    {
        $post_type = $data['settings']['post_type'];
        $post_taxonomies = get_object_taxonomies($post_type, 'objects');

        foreach ($post_taxonomies as $tax) {
            $tax_type = sprintf('tax_%s', $tax->name);
            /**
             * We do not need post_format
             */
            if ($tax->name === 'post_format')
                continue;

            $data['formBuilder_options']['fields'][] =
                [
                    'label' => $tax->label,
                    'attrs' => [
                        'placeholder' => sprintf('%s %s', __('Select', FE_TEXT_DOMAIN), $tax->label),
                        'type' => $tax_type
                    ],
                    'icon' => '<span class="dashicons dashicons-list-view"></span>'
                ];

            /**
             * Templates
             */
            $data['formBuilder_options']['temp_back'][$tax_type] = [
                'field' => sprintf('<div class="%s tax" name="%s"></div>', $tax->name, $tax->name),
                'onRender' => '',
                'max_in_form' => 1
            ];

            /**
             * Adding field to group
             */
            $data['formBuilder_options']['controls_group']['taxonomies']['types'][] = $tax_type;

            /**
             * Adding attribute settings 
             */
            $data['formBuilder_options']['typeUserAttrs'][$tax_type] =
                [
                    'order' => [
                        'label' => __('Order', FE_TEXT_DOMAIN),
                        'options' => [
                            'asc' => 'ASC',
                            'desc' => 'Desc'
                        ],
                        'type' => 'select',
                    ],
                    'multiple' => [
                        'label' => __('Multiple Selections', FE_TEXT_DOMAIN),
                        'value' => false,
                        'type' => 'checkbox',
                    ],
                    'show_empty' => [
                        'label' => __('Show empty', FE_TEXT_DOMAIN),
                        'value' => false,
                        'type' => 'checkbox',
                    ],
                    'add_new' => [
                        'label' => __('Allow Add New', FE_TEXT_DOMAIN),
                        'value' => false,
                        'type' => 'checkbox',
                    ],
                ];
            /**
             * Disabling default settings
             */
            $data['formBuilder_options']['typeUserDisabledAttrs'][$tax_type] =
                [
                    'name',
                    'description',
                    'inline',
                    'toggle',
                    'placeholder',
                    'access',
                    'value',
                ];
        }


        return $data;
    }

    public static function front_tax_select($post_id, $attributes, $field)
    {

        if (strpos($field['type'], 'tax') === false ) {
            return;
        }

        $tax_name = str_replace("tax_", "", $field['type']);

        require FE_Template_PATH . 'front-editor/taxonomy.php';
    }


    public static function add_tax_on_save_and_check($post_data, $data, $file)
    {
        if (empty($_POST['category'])) {
            return $post_data;
        }

        $settings = get_post_meta($_POST['editor_post_id'], 'save_editor_attributes_to_meta', 1);
        $post_category_settings = sanitize_text_field($settings['post_category']);
        $post_category_val = sanitize_text_field($_POST['category']);
        $post_id = intval(sanitize_text_field($_POST['post_id']));

        if ($post_category_settings === 'disable') {
            return $post_data;
        }

        if ($post_category_settings === 'require' && empty($post_category_val)) {
            wp_send_json_error(['message' => __('The category selection is required', FE_TEXT_DOMAIN)]);
        }

        if ($post_category_val === 'null') {
            if ($post_id) {
                wp_delete_object_term_relationships($post_id, 'category');
            }
        }

        if (!empty($post_category_val) && $post_category_val !== 'null') {
            $post_data['post_category'] = explode(",", $post_category_val);
        }

        return $post_data;
    }
}

TaxonomiesFields::init();
