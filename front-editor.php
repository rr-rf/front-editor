<?php

/**
 * Plugin Name: Front User Submit | Front Editor
 * Plugin URI: https://wpfronteditor.com/
 * Description: Have you ever seen websites that allow users to submit posts or other type of content? Do you want to have user-submitted content on your site? Front Editor allow users to submit blog posts to your WordPress site with new frontend block editor EditorJs.
 * Author: Aleksan Aharonyan
 * Author URI: https://github.com/Aharonyan/
 * Developer: Aleksan Aharonyan
 * Developer URI: https://github.com/Aharonyan/front-editor
 * Text Domain: front-editor
 * Domain Path: /languages
 * PHP requires at least: 5.6
 * WP requires at least: 5.0
 * Tested up to: 5.6
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 2.1.1
 */

namespace BFE;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Core
 */
class BestFrontEndEditor
{

  /**
   * The init
   */
  public static function init()
  {
    if ( ! function_exists( 'fe_fs' ) ) {
      // Create a helper function for easy SDK access.
      function fe_fs() {
          global $fe_fs;
  
          if ( ! isset( $fe_fs ) ) {
              // Include Freemius SDK.
              require_once dirname(__FILE__) . '/freemius/start.php';
  
              $fe_fs = fs_dynamic_init( array(
                  'id'                  => '7886',
                  'slug'                => 'front-editor',
                  'type'                => 'plugin',
                  'public_key'          => 'pk_721b5ebdb9cda3d26691a9fb5c35c',
                  'is_premium'          => true,
                  // If your plugin is a serviceware, set this option to false.
                  'has_premium_version' => true,
                  'has_addons'          => false,
                  'has_paid_plans'      => true,
                  'trial'               => array(
                      'days'               => 7,
                      'is_require_payment' => false,
                  ),
                  'menu'                => array(
                      'slug'           => 'front_editor_settings',
                  ),
                  // Set the SDK to work in a sandbox mode (for development & testing).
                  // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                  'secret_key'          => 'sk_0c1aMUbAp.N+26}=q4q?K2-GL!3RT',
              ) );
          }
  
          return $fe_fs;
      }
  
      // Init Freemius.
      fe_fs();
      // Signal that SDK was initiated.
      do_action( 'fe_fs_loaded' );
  }

    define('FE_PLUGIN_URL', plugins_url('', __FILE__));
    define('FE_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
    define('FE_Template_PATH', plugin_dir_path(__FILE__) . 'templates/');
    define('FE_TEXT_DOMAIN', 'front-editor');

    register_activation_hook(__FILE__, [__CLASS__, 'fe_plugin_activate']);

    register_deactivation_hook(__FILE__, [__CLASS__, 'fe_plugin_deactivate']);

    add_action('plugins_loaded', [__CLASS__, 'true_load_plugin_textdomain']);

    add_action('plugins_loaded', [__CLASS__, 'add_components']);

    add_filter('post_row_actions', [__CLASS__, 'add_link_to_edit_this_post'], 10, 2);

    add_action('BFE_activate', [__CLASS__, 'activate_user_ability_to_upload_files']);

    add_action('BFE_deactivate', [__CLASS__, 'disable_user_ability_to_upload_files']);
  }

  /**
   * On plugin activate
   *
   * @return void
   */
  public static function fe_plugin_activate()
  {
    do_action('BFE_activate');
  }

  /**
   * On plugin deactivate
   *
   * @return void
   */
  public static function fe_plugin_deactivate()
  {
    do_action('BFE_deactivate');
  }

  /**
   * Add Components
   */
  public static function add_components()
  {
    require_once __DIR__ . '/functions.php';
    require_once __DIR__ . '/inc/MenuSettings.php';
    require_once __DIR__ . '/inc/Shortcodes.php';
    require_once __DIR__ . '/inc/PostsList.php';
    require_once __DIR__ . '/inc/SavePost.php';
    require_once __DIR__ . '/inc/Blocks.php';
    require_once __DIR__ . '/inc/Editor.php';
    require_once __DIR__ . '/inc/EditorWidget.php';
    require_once __DIR__ . '/inc/PostFormCPT.php';

    add_action('wp_enqueue_scripts', [__CLASS__, 'add_scripts']);
  }


  /**
   * Add languages
   *
   * @return void
   */
  public static function true_load_plugin_textdomain()
  {
    load_plugin_textdomain(FE_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');
  }

  /**
   * Add scripts
   */
  public static function add_scripts()
  {
    if (is_admin()) {
      return;
    }

    if (!is_user_logged_in()) {
      return;
    }

    if (is_page() || is_single()) {
      $asset = require FE_PLUGIN_DIR_PATH . 'assets/frontend/frontend.asset.php';

      wp_register_script('bfee-editor.js', FE_PLUGIN_URL . '/assets/frontend/frontend.js', array('jquery'), $asset['version'], true);

      wp_register_style(
        'bfe-block-style',
        FE_PLUGIN_URL . '/assets/frontend/main.css',
        [],
        $asset['version']
      );

      wp_enqueue_style('bfe-block-style');
    }
  }

  /**
   * added link wp admin post archive
   *
   * @param [type] $actions
   * @param [type] $post
   * @return void
   */
  public static function add_link_to_edit_this_post($actions, $post)
  {
    if ($post->post_type !== 'fe_post_form') {
      if ($edit_link = Editor::get_post_edit_link($post->ID)) {
        $actions['bfe_front_editor_link'] = sprintf(
          '<a target="_blank" style="color:#388ffe;" href="%s">%s</a>',
          $edit_link,
          __('Edit in front editor', FE_TEXT_DOMAIN)
        );
      }
    }

    /**
     * Changing postForm archive page edit link
     */
    if ($post->post_type === 'fe_post_form') {
      $actions['edit'] = sprintf(
        '<a target="_blank" href="%s">%s</a>',
        home_url(sprintf('/wp-admin/edit.php?page=fe-post-forms&action=edit&id=%s', $post->ID)),
        __('Edit', FE_TEXT_DOMAIN)
      );

      printf(
        '<script>jQuery("#post-%s a.row-title").attr("href","%s")</script>',
        $post->ID,
        home_url(sprintf('/wp-admin/edit.php?page=fe-post-forms&action=edit&id=%s', $post->ID))
      );

      printf(
        '<script>jQuery(".post-type-fe_post_form .page-title-action").attr("href","%s")</script>',
        home_url('/wp-admin/admin.php?page=fe-post-forms&action=add-new')
      );

      unset($actions['inline hide-if-no-js']);
    }

    return $actions;
  }

  public static function activate_user_ability_to_upload_files()
  {
    $contributor = get_role('subscriber');
    $contributor->add_cap('upload_files');
  }

  public static function disable_user_ability_to_upload_files()
  {
    $contributor = get_role('subscriber');
    $contributor->remove_cap('upload_files');
  }
}

BestFrontEndEditor::init();
