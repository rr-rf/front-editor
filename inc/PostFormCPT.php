<?php

/**
 * Gutenberg block to display Post Form.
 *
 * @package BFE;
 */

namespace BFE;

defined('ABSPATH') || exit;

/**
 * Class Post Form - registers custom gutenberg block.
 */
class PostFormCPT
{
    /**
     * Init logic.
     */
    public static function init()
    {
        require_once __DIR__ . '/post-form-builder/PostTitleField.php';
        require_once __DIR__ . '/post-form-builder/EditorJsField.php';
        require_once __DIR__ . '/post-form-builder/TaxonomiesFields.php';
        require_once __DIR__ . '/post-form-builder/PostThumbField.php';

        /**
         * Registering custom post type
         */
        add_action('init', [__CLASS__, 'register_post_types']);

        /**
         * Adding scripts to custom post type
         */
        add_action('admin_enqueue_scripts', [__CLASS__, 'add_admin_scripts'], 10, 1);

        /**
         * Get formBuilder data
         */
        add_action('wp_ajax_fe_get_formBuilder_data', [__CLASS__, 'fe_get_formBuilder_data']);

        add_action('wp_ajax_save_post_front_settings', [__CLASS__, 'save_post_front_settings']);
    }

    /**
     * Adding custom metabox
     *
     * @return void
     */
    public static function front_editor_add_custom_box()
    {
        $screens = ['fe_post_form'];
        add_meta_box('front_editor_metabox', 'Post Form Data', [__CLASS__, 'front_editor_meta_box_callback'], $screens);
    }

    /**
     * Get formBuilder data
     *
     * @return void
     */
    public static function fe_get_formBuilder_data()
    {

        /**
         * Check wp nonce
         */
        if (!wp_verify_nonce($_POST['admin_form_builder_nonce'], 'admin_form_builder_nonce'))
            return;

        /**
         * If this is auto save do nothing
         */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        $post_ID = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : false;

        $data = [
            'settings' => [
                'post_type' => sanitize_text_field($_POST['post_type']),
                'post_id' => $post_ID,
            ],
            'formBuilder_options' => [
                //'prepend' => sprintf('<h2>%s</h2>', __('Post Title', FE_TEXT_DOMAIN)),
                'fields' => [], // New field creation
                'typeUserAttrs' => [], // Custom attr settings for fields,
                'disabledFieldButtons' => [],
                'defaultFields' => [],
                'typeUserDisabledAttrs' => [ // Disable attributes
                    'paragraph' => ['access']
                ],
                'disable_attr' => [],
                'templates' => [],
                'temp_back' => [],
                'disableFields' => ['autocomplete', 'button', 'checkbox-group', 'date', 'file', 'header', 'hidden', 'radio-group', 'select', 'number'],
                'defaultControls' => ['paragraph', 'text', 'textarea'],
                'controls_group' => [
                    'post_fields' => [
                        'label' => __('Post Fields', FE_TEXT_DOMAIN),
                        'types' => []
                    ],
                    'taxonomies' => [
                        'label' => __('Taxonomies', FE_TEXT_DOMAIN),
                        'types' => []
                    ],
                    'custom_fields' => [
                        'label' => __('Custom Fields', FE_TEXT_DOMAIN),
                        'types' => []
                    ],
                ],
                'disabledFieldButtons' => [],
                'controlOrder' => [],
                'disabledActionButtons' => ['data', 'clear', 'save'],
                'messages' => [
                    'max_fields_warning' => __('You already have this field in the form', FE_TEXT_DOMAIN)
                ]
            ],
        ];


        if ($post_ID) {
            $data['formBuilderData'] = get_post_meta($post_ID, 'formBuilderData', true);
        }

        /**
         * Default controls
         */
        $data['formBuilder_options']['controls_group']['custom_fields']['types'] = $data['formBuilder_options']['defaultControls'];

        /**
         * Ability to add custom group
         */
        $data['formBuilder_options']['controls_group'] = apply_filters('admin_post_form_formBuilder_add_controls_group', $data['formBuilder_options']['controls_group']);

        $filter_data = apply_filters('admin_post_form_formBuilder_settings', $data);

        /**
         * Order Elements in control bar
         */
        foreach ($filter_data['formBuilder_options']['controls_group'] as $group) {

            if (empty($group['types'])) {
                continue;
            }

            foreach ($group['types'] as $types) {
                $filter_data['formBuilder_options']['controlOrder'][] = $types;
            }
        }
        wp_send_json_success($filter_data);
    }


    /**
     * Callback method for Post Forms submenu
     *
     * @since 2.5
     *
     * @return void
     */
    public static function fe_post_forms_page()
    {
        $action           = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : null;
        $post_ID           = isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : 'new';
        $add_new_page_url = admin_url('admin.php?page=fe-post-forms&action=add-new');


        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('bfe-block-script');
        wp_enqueue_script('bfe-form-builder');
        wp_enqueue_style('fe_post_form_CPT');


        $data = [
            'post_id' => $post_ID,
            'ajax_url' => admin_url('admin-ajax.php'),
        ];

        wp_localize_script('bfe-block-script', 'fe_post_form_data', apply_filters('bfe_fe_post_form_backend_block_localize_data', $data));

        switch ($action) {
            case 'edit':

                require FE_PLUGIN_DIR_PATH . 'templates/admin/post-form.php';
                break;

            case 'add-new':
                require FE_PLUGIN_DIR_PATH . 'templates/admin/post-form.php';
                break;

            default:
                //require_once WPUF_ROOT . '/admin/post-forms-list-table-view.php';
                break;
        }
    }

    /**
     * Updating post
     *
     * @return void
     */
    public static function save_post_front_settings()
    {

        /**
         * Check wp nonce
         */
        if (!wp_verify_nonce($_POST['admin_form_builder_nonce'], 'admin_form_builder_nonce'))
            return;

        /**
         * If this is auto save do nothing
         */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        $title = isset($_POST['fe_title']) ? $_POST['fe_title'] : __('Sample Form', FE_TEXT_DOMAIN);
        if (!empty($_POST['post_id']) && $_POST['post_id'] !== 'new') {
            $post_ID = intval(sanitize_text_field($_POST['post_id']));
            wp_update_post([
                'ID'           => $post_ID,
                'post_title'   => $title,
            ]);
        } elseif (!empty($_POST['post_id']) && $_POST['post_id'] === 'new') {
            $post_ID = wp_insert_post([
                'post_title' => $title,
                'post_type' => 'fe_post_form',
                'post_status'   => 'publish',
            ]);
        }

        /**
         * Saving data
         */
        if (!empty($_POST['formBuilderData'])) {
            update_post_meta($post_ID, 'formBuilderData', $_POST['formBuilderData']);
        }

        /**
         * Adding all settings to meta fields
         */
        if (!empty($_POST['settings'])) {
            update_post_meta($post_ID, 'fe_form_settings', $_POST['settings']);
        }


        wp_send_json_success([
            'post_id' => $post_ID,
            'message' => [
                'title' => __('Thanks', FE_TEXT_DOMAIN),
                'message' => __('Post Updated'),
                'status' => 'success'
            ]
        ]);
    }

    /**
     * Adding scripts to custom post type
     *
     * @param [type] $hook
     * @return void
     */
    public static function add_admin_scripts($hook)
    {

        global $post;
        $asset = require FE_PLUGIN_DIR_PATH . 'assets/frontend/frontend.asset.php';

        wp_register_script(
            'jquery-ui',
            plugins_url('assets/vendors/jquery-ui.min.js', dirname(__FILE__)),
            $asset['dependencies'],
            $asset['version'],
            true
        );
        wp_register_script(
            'bfe-form-builder',
            plugins_url('assets/vendors/form-builder.min.js', dirname(__FILE__)),
            $asset['dependencies'],
            $asset['version'],
            true
        );
        wp_register_style('fe_post_form_CPT', FE_PLUGIN_URL . '/assets/editor/main.css', [], $asset['version']);
        wp_register_script(
            'bfe-block-script',
            plugins_url('assets/editor/editor.js', dirname(__FILE__)),
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    /**
     * Registering post type
     *
     * @return void
     */
    public static function register_post_types()
    {
        register_post_type('fe_post_form', [
            'label'  => null,
            'labels' => [
                'name'               => __('Post Form', FE_TEXT_DOMAIN),
                'singular_name'      => __('Post Form', FE_TEXT_DOMAIN),
                'add_new'            => __('Add Post Form', FE_TEXT_DOMAIN),
                'add_new_item'       => __('Add Post Form', FE_TEXT_DOMAIN),
                'edit_item'          => __('Edit Post Form', FE_TEXT_DOMAIN),
                'new_item'           => __('New Post Form', FE_TEXT_DOMAIN),
                'view_item'          => __('Watch Post Form', FE_TEXT_DOMAIN),
                'search_items'       => __('Search Post Form', FE_TEXT_DOMAIN),
                'not_found'          => __('Not Found', FE_TEXT_DOMAIN),
                'not_found_in_trash' => __('Not found in trash', FE_TEXT_DOMAIN),
                'parent_item_colon'  => '',
                'menu_name'          => __('Post Forms', FE_TEXT_DOMAIN),
            ],
            'description'         => '',
            'public'              => false,
            'show_ui'            => true,
            'show_in_menu'       => 'front_editor_settings',
            'show_in_rest'        => false,
            'rest_base'           => null,
            'menu_position'       => 10,
            'exclude_from_search' => true,
            'menu_icon'           => 'dashicons-format-quote',
            'capability_type'   => 'post',
            'capabilities'      => array(
                'edit_post'          => 'update_core',
                'read_post'          => 'update_core',
                'delete_post'        => 'update_core',
                'edit_posts'         => 'update_core',
                'edit_others_posts'  => 'update_core',
                'delete_posts'       => 'update_core',
                'publish_posts'      => 'update_core',
                'read_private_posts' => 'update_core'
            ),
            'map_meta_cap'      => null,
            'hierarchical'        => false,
            'supports'            => ['title'],
            'has_archive'         => false,
            'rewrite'             => true,
            'query_var'           => true,
        ]);
    }

    /**
     * Get Form field settings
     *
     * @param [type] $name
     * @param [type] $form_id
     * @return void
     */
    public static function get_form_field_settings($name, $form_id)
    {
        $form_settings = json_decode(get_post_meta($form_id, 'formBuilderData', true),true);

        if(empty($form_settings)){
            return false;
        }

        foreach($form_settings as $field){
            if($field['type'] === $name){
                return $field;
            }
        }

        return false;
    }
}

PostFormCPT::init();
