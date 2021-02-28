<?php

/**
 * formBuilder EditorJS field
 *
 * @package BFE;
 */

namespace BFE\Field;

use BFE\PostFormCPT;

defined('ABSPATH') || exit;


class PostTitleField
{
    public static $field_label = 'Post Title';
    public static $field_type =  'post_title';

    public static function init()
    {
        add_filter('admin_post_form_formBuilder_settings', [__CLASS__, 'add_field_settings']);

        /**
         * Validate field on wp admin form save
         */
        add_action('fe_before_wp_admin_form_create_save', [__CLASS__, 'validate_field_before_wp_admin_form_save']);
   
    }

    /**
     * Validate field on wp admin form save
     *
     * @param [type] $data
     * @return void
     */
    public static function validate_field_before_wp_admin_form_save($data){
        $settings = PostFormCPT::get_form_field_settings(self::$field_type, 0, $_POST['formBuilderData']);

        if(!$settings){
            wp_send_json_success([
                'message' => [
                    'title' => __('Oops', FE_TEXT_DOMAIN),
                    'message' => __('Post Title Field is missing', FE_TEXT_DOMAIN),
                    'status' => 'warning'
                ]
            ]);
        }
    }

    public static function add_field_settings($data)
    {
        $field_label = __(self::$field_label, FE_TEXT_DOMAIN);
        /**
         * Adding field
         */
        $data['formBuilder_options']['fields'][] =
            [
                'label' => $field_label,
                'attrs' => [
                    'type' => self::$field_type
                ],
                'icon' => '🅰️',
            ];

        $data['formBuilder_options']['temp_back'][self::$field_type] = [
            'field' => sprintf('<input type="text" class="%s" name="%s">', self::$field_type, self::$field_type),
            'onRender' => '',
            'max_in_form' => 1
        ];

        /**
         * Adding as default
         */
        $data['formBuilder_options']['defaultFields'][] = [
            'label' => $field_label,
            'type' => self::$field_type
        ];

        /**
         * Adding field to group
         */
        $data['formBuilder_options']['controls_group']['post_fields']['types'][] = self::$field_type;


        $data['formBuilder_options']['disable_attr'][] = '.fld-editor_warning_plugin';

        /**
         * Disabling default settings
         */
        $data['formBuilder_options']['typeUserDisabledAttrs'][self::$field_type] =
            [
                'name',
                'description',
                'inline',
                'toggle',
                'access',
                'value',
            ];

        return $data;
    }
}

PostTitleField::init();
