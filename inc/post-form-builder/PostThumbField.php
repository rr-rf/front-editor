<?php

/**
 * formBuilder EditorJS field
 *
 * @package BFE;
 */

namespace BFE\Field;

defined('ABSPATH') || exit;


class PostThumbField
{
    public static $field_label = 'Featured Image';
    public static $field_type =  'featured_image';

    public static function init()
    {
        add_filter('admin_post_form_formBuilder_settings', [__CLASS__, 'add_field_settings']);
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
                'icon' => '<span class="dashicons dashicons-format-image"></span>',
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
         * Adding attribute settings 
         */
        $data['formBuilder_options']['typeUserAttrs'][self::$field_type] =
            [
                'wp_media_uploader' => [
                    'label' => __('WP Media Uploader', FE_TEXT_DOMAIN),
                    'value' => true,
                    'type' => 'checkbox',
                ]
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

PostThumbField::init();
