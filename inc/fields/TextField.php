<?php

/**
 * formBuilder EditorJS field
 *
 * @package BFE;
 */

namespace BFE\Field;

use BFE\PostFormCPT;

defined('ABSPATH') || exit;


class TextField
{
    public static $field_label = 'Featured Image';
    public static $field_type =  'text';

    public static function init()
    {
        add_filter('admin_post_form_formBuilder_settings', [__CLASS__, 'add_field_settings']);

        if (fe_fs()->is__premium_only()) {
            add_action('bfe_editor_on_front_field_adding', [__CLASS__, 'add_field_to_front_form'], 10, 3);
            add_action('bfe_ajax_after_front_editor_post_update_or_creation', [__CLASS__, 'save_field_to_front_form'], 10);
        }
    }

    /**
     * This settings for wp admin form builder
     *
     * @param [type] $data
     * @return void
     */
    public static function add_field_settings($data)
    {
        if (!fe_fs()->is__premium_only()) {
            $data['formBuilder_options']['disableFields'][] = 'text';
        }

        return $data;
    }

    /**
     * Add post image selection
     *
     * @return void
     */
    public static function add_field_to_front_form($post_id, $attributes, $field)
    {

        if ($field['type'] !== self::$field_type) {
            return;
        }

        require FE_Template_PATH . 'front-editor/text.php';
    }

    /**
     * Image check
     *
     * @param [type] $post_data
     * @param [type] $data
     * @param [type] $file
     * @return void
     */
    public static function save_field_to_front_form($post_id)
    {
        if (!isset($_POST['text_fields'])) {
            return;
        }

        foreach ($_POST['text_fields'] as $name => $value) {
            update_post_meta($post_id, $name, $value);
        }
    }
}

TextField::init();
