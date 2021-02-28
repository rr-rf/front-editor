<?php

/**
 * formBuilder EditorJS field
 *
 * @package BFE;
 */

namespace BFE\Field;

use BFE\PostFormCPT;

defined('ABSPATH') || exit;


class EditorJsField
{
    public static $field_label = 'Post Content (EditorJS - block styled editor)';
    public static $field_type =  'post_content_editor_js';

    public static function init()
    {
        /**
         * Adding setting to admin
         */
        add_filter('admin_post_form_formBuilder_settings', [__CLASS__, 'add_field_settings']);

        add_filter('bfe_front_editor_localize_data', [__CLASS__, 'field_setting_for_frontend'], 10, 3);

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
                    'message' => __('Post Content Field is missing', FE_TEXT_DOMAIN),
                    'status' => 'warning'
                ]
            ]);
        }
    }

    /**
     * Adding setting to admin
     */
    public static function add_field_settings($data)
    {
        /**
         * Adding field
         */
        $data['formBuilder_options']['fields'][] =
            [
                'label' => self::$field_label,
                'attrs' => [
                    'type' => self::$field_type
                ],
                'icon' => '<svg width="84" height="84" viewBox="0 0 84 84" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="editorjs-logo-a"><stop stop-color="#39FFD7" offset="0%"></stop><stop stop-color="#308EFF" offset="100%"></stop></linearGradient></defs><g fill-rule="nonzero" fill="none"><circle fill="url(#editorjs-logo-a)" cx="42" cy="42" r="42"></circle><rect fill="#FFF" x="38" y="17" width="8" height="50" rx="4"></rect><rect fill="#FFF" x="17" y="38" width="50" height="8" rx="4"></rect></g></svg>',
            ];

        $data['formBuilder_options']['temp_back'][self::$field_type] = [
            'field' => sprintf('<div class="%s editor" name="%s"></div>', self::$field_type, self::$field_type),
            'onRender' => '',
            'max_in_form' => 1,
            'required' => 1
        ];

        /**
         * Adding attribute settings 
         */
        $data['formBuilder_options']['typeUserAttrs'][self::$field_type] =
            [
                'editor_image_plugin' => [
                    'label' => 'Image',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_header_plugin' => [
                    'label' => 'Header',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_embed_plugin' => [
                    'label' => 'Embed',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_list_plugin' => [
                    'label' => 'List',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_checklist_plugin' => [
                    'label' => 'Checklist',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_quote_plugin' => [
                    'label' => 'Quote',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_marker_plugin' => [
                    'label' => 'Marker',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_code_plugin' => [
                    'label' => 'Code',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_delimiter_plugin' => [
                    'label' => 'Delimiter',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_inlineCode_plugin' => [
                    'label' => 'Code',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_linkTool_plugin' => [
                    'label' => 'Link Tool',
                    'value' => true,
                    'type' => 'checkbox',
                ],
                'editor_warning_plugin' => [
                    'label' => 'Warning (Pro)',
                    'value' => false,
                    'type' => 'checkbox',
                ],
                'editor_gallery_plugin' => [
                    'label' => 'Gallery (Pro)',
                    'value' => false,
                    'type' => 'checkbox',
                ],
                'editor_table_plugin' => [
                    'label' => 'Table (Pro)',
                    'value' => false,
                    'type' => 'checkbox',
                ]
            ];

        /**
         * Adding as default
         */
        $data['formBuilder_options']['defaultFields'][] = [
            'label' => self::$field_label,
            'type' => self::$field_type
        ];

        /**
         * Adding field to group
         */
        $data['formBuilder_options']['controls_group']['post_fields']['types'][] = self::$field_type;

        /**
         * Disable attr if there is no pro version
         */
        $data['formBuilder_options']['disable_attr'][] = '.fld-editor_warning_plugin';
        $data['formBuilder_options']['disable_attr'][] = '.fld-editor_gallery_plugin';
        $data['formBuilder_options']['disable_attr'][] = '.fld-editor_table_plugin';
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
                'placeholder',
                'access',
                'value',
            ];

        $data['formBuilder_options']['disabledFieldButtons'][self::$field_type] = ['copy'];

        return $data;
    }

    /**
     * Field setting for front end 
     *
     * @param [type] $data
     * @param [type] $attributes
     * @param [type] $post_id
     * @return void
     */
    public static function field_setting_for_frontend($data, $attributes, $post_id)
    {
        $settings = PostFormCPT::get_form_field_settings(self::$field_type, $attributes['id']);

        if (empty($settings)) {
            $data['editor_settings'] = [
                'editor_image_plugin' => $attributes['editor_image_plugin'] ?? true,
                'editor_header_plugin' => $attributes['editor_header_plugin'] ?? true,
                'editor_embed_plugin' => $attributes['editor_embed_plugin'] ?? true,
                'editor_list_plugin' => $attributes['editor_list_plugin'] ?? true,
                'editor_checklist_plugin' => $attributes['editor_checklist_plugin'] ?? true,
                'editor_quote_plugin' => $attributes['editor_quote_plugin'] ?? true,
                'editor_marker_plugin' => $attributes['editor_marker_plugin'] ?? true,
                'editor_code_plugin' => $attributes['editor_code_plugin'] ?? true,
                'editor_delimiter_plugin' => $attributes['editor_delimiter_plugin'] ?? true,
                'editor_inlineCode_plugin' => $attributes['editor_inlineCode_plugin'] ?? true,
                'editor_linkTool_plugin' => $attributes['editor_linkTool_plugin'] ?? true,
                'tags_add_new' => $attributes['tags_add_new'] ?? false,
                'wp_media_uploader' => false, // pro
                'editor_warning_plugin' => false, // pro
                'editor_table_plugin' => false, // pro
                'editor_gallery_plugin' => false, // pro
            ];
        }

        if (is_array($settings) && !empty($settings)) {
            foreach($settings as $name => $value){
                $data['editor_settings'][$name] = $value;
            }
        }


        $data['translations']['i18n'] = [
            'messages' => [
                'ui' => [
                    "blockTunes" => [
                        "toggler" => [
                            "Click to tune" => __("Click to tune", FE_TEXT_DOMAIN),
                            "or drag to move" => __("or drag to move", FE_TEXT_DOMAIN)
                        ]
                    ],
                    'inlineToolbar' => [
                        'converter' => [
                            "Convert to" => __("Convert to", FE_TEXT_DOMAIN)

                        ]
                    ],
                    "toolbar" => [
                        "toolbox" => [
                            "Add" => __("Add", FE_TEXT_DOMAIN)
                        ]
                    ]
                ],
                'toolNames' => [
                    "Text" => __("Text", FE_TEXT_DOMAIN),
                    "Heading" => __("Heading", FE_TEXT_DOMAIN),
                    "List" => __("List", FE_TEXT_DOMAIN),
                    "Warning" => __("Warning", FE_TEXT_DOMAIN),
                    "Checklist" => __("Checklist", FE_TEXT_DOMAIN),
                    "Quote" => __("Quote", FE_TEXT_DOMAIN),
                    "Code" => __("Code", FE_TEXT_DOMAIN),
                    "Delimiter" => __("Delimiter", FE_TEXT_DOMAIN),
                    "Raw HTML" => __("Raw HTML", FE_TEXT_DOMAIN),
                    "Table" => __("Table", FE_TEXT_DOMAIN),
                    "Link" => __("Link", FE_TEXT_DOMAIN),
                    "Marker" => __("Marker", FE_TEXT_DOMAIN),
                    "Bold" => __("Bold", FE_TEXT_DOMAIN),
                    "Italic" => __("Italic", FE_TEXT_DOMAIN),
                    "InlineCode" => __("InlineCode", FE_TEXT_DOMAIN),
                    "Image & Gallery" => __("Image & Gallery", FE_TEXT_DOMAIN),
                    "Image" => __("Image", FE_TEXT_DOMAIN)
                ],
                'tools' => [
                    'warning' => [
                        "Title" => __("Title", FE_TEXT_DOMAIN),
                        "Message" => __("Message", FE_TEXT_DOMAIN)
                    ],
                    'link' => [
                        "Add a link" => __("Add a link", FE_TEXT_DOMAIN),
                    ],
                    'stub' => [
                        "The block can not be displayed correctly." => __("The block can not be displayed correctly.", FE_TEXT_DOMAIN),
                    ]
                ],
                'blockTunes' => [
                    'delete' => [
                        "Delete" => __("Delete", FE_TEXT_DOMAIN),
                    ],
                    'moveUp' => [
                        "Move up" => __("Move up", FE_TEXT_DOMAIN),
                    ],
                    'moveDown' => [
                        "Move down" => __("Move down", FE_TEXT_DOMAIN),
                    ]
                ]
            ]
        ];

        $data['translations']['editor_field_placeholder'] = __('Start writing or enter Tab to choose a block', FE_TEXT_DOMAIN);

        return $data;
    }
}

EditorJsField::init();
