<?php

/**
 * This class help to work with editor
 *
 * Long Description.
 *
 * @link URL
 * @since x.x.x (if available)
 *
 * @package bfee
 */

namespace BFE;

/**
 * Editor class;
 */
class Editor
{


	/**
	 * Example data for editor
	 *
	 * @return $data
	 */
	public static function example_editor_data()
	{
		$data = array(
			array(
				'type' => 'paragraph',
				'data' => array(
					'text' => __('Add content', FE_TEXT_DOMAIN),
				),
			),
		);

		return $data;
	}

	/**
	 * Page editor init
	 *
	 * @return string
	 */
	public static function show_front_editor($attributes, $content = '', $type = "")
	{
		/**
		 * Adding post meta to know the editor page if no page founded
		 */
		if (!self::get_editor_page_link()) {
			update_post_meta(get_the_ID(), 'editor_js_page', true);
		}

		$attributes['id'] = isset($attributes['id']) ? (int) sanitize_text_field($attributes['id']) : 0;

		$post_id       = 'new';
		$editor_data   = 'new';
		$button_text   = __('Publish', FE_TEXT_DOMAIN);
		$html_content  = '';
		$new_post_link = self::get_editor_page_link();

		do_action('bfe_before_editor_block_front_print', $attributes);

		if (!empty($_GET['post_id'])) {

			$post_id = intval(sanitize_text_field($_GET['post_id']));

			if (!$post_id) {
				return sprintf('<h2>%s</h2>', __('The post you trying to edit is not exist, please create a new one', FE_TEXT_DOMAIN));
			}

			if (!get_post_status($post_id)) {
				return sprintf('<h2>%s</h2>', __('The post you trying to edit is not exist, please create a new one', FE_TEXT_DOMAIN));
			}
		}

		if (!self::can_edit_post(0, $post_id)) {
			return sprintf('<h2>%s</h2>', __('You do not have permission to edit this post', FE_TEXT_DOMAIN));
		}
		
		if (!$attributes['id']) {
			return sprintf(
				'<h2>%s <a href="%s">%s</a></h2>',
				__('Post form is not selected please select existing one or'),
				admin_url('admin.php?page=fe-post-forms&action=add-new'),
				__('Create New One', FE_TEXT_DOMAIN)
			);
		}

		if ('new' !== $post_id) {
			/**
			 * If we sending $editor_data = "" in js it will render $html_content data
			 */
			$editor_data = '';
			if (get_post_meta($post_id, 'bfe_editor_js_data', true)) {
				$editor_data                = get_post_meta($post_id, 'bfe_editor_js_data', true);
				$editor_data                = json_decode($editor_data, true);
				$admin_post_modified_from_admin   = get_post_meta($post_id, 'fe_post_updated_from_admin', true);
				/**
				 * If is the post is changed from admin we will use html content
				 */
				if ($admin_post_modified_from_admin) {
					$editor_data = '';
					update_post_meta($post_id, 'fe_post_updated_from_admin', 0);
				}
			}

			$post         = get_post($post_id);
			$html_content = $post->post_content;

			$button_text = __('Update', FE_TEXT_DOMAIN);
		} else {
			$editor_data = self::example_editor_data();
		}

		$data = [
			'ajax_url'          => admin_url('admin-ajax.php'),
			'data'              => apply_filters('fe_localize_editor_content_data', $editor_data),
			'html_post_content' => $html_content,
			'translations'      => [
				'save_button' => [
					'publish'  => __('Publish', FE_TEXT_DOMAIN),
					'updating' => sprintf('%s...', __('Updating', FE_TEXT_DOMAIN)),
					'update'   => __('Update', FE_TEXT_DOMAIN),
				],

			],
		];

		$wp_localize_data = apply_filters('bfe_front_editor_localize_data', $data, $attributes, $post_id);
		wp_localize_script('bfee-editor.js', 'editor_data', $wp_localize_data);

		/**
		 * Activating wp media uploader
		 */
		if ($wp_localize_data['post_thumb']['wp_media_uploader'] || $wp_localize_data['editor_settings']['editor_gallery_plugin']) {
			wp_enqueue_media();
		}

		wp_enqueue_script('bfee-editor.js');

		ob_start();

		require_once FE_Template_PATH . 'post-form.php';

		return ob_get_clean();
	}

	/**
	 * Can user edit post
	 *
	 * @param integer $cur_user_id current user id.
	 * @param string  $post_id post id.
	 * @return boolean
	 */
	public static function can_edit_post($cur_user_id = 0, $post_id = 'new')
	{
		if (!$cur_user_id) {
			$cur_user_id = get_current_user_id();
		}

		if (!is_user_logged_in()) {
			return false;
		}

		if (current_user_can('edit_others_pages')) {
			return true;
		}

		if ('new' !== $post_id) {
			$post_user = (int) get_post_field('post_author', $post_id);
			if ($post_user !== $cur_user_id) {
				return false;
			}
		}

		return true;
	}
	/**
	 * Getting edit links by id
	 *
	 * @param integer $post_id past id to get link.
	 * @return string
	 */
	public static function get_post_edit_link($post_id = 0)
	{
		$editor_page = self::get_editor_page_link();

		if (!$editor_page) {
			return false;
		}

		return sprintf('%s?post_id=%s', $editor_page, $post_id);
	}

	/**
	 * Getting the page link with editor shortcode
	 *
	 * @return string
	 */
	public static function get_editor_page_link()
	{

		$args = array(
			'posts_per_page' => 1,
			'post_type'      => 'page',
			'meta_query'     => array(
				array(
					'key'     => 'editor_js_page',
					'compare' => 'EXISTS',
				),
			),
		);

		$query = new \WP_Query;
		$editor_pages = $query->query($args);

		foreach ($editor_pages as $editor_page) {
			return get_the_permalink($editor_page->ID);
		}

		return false;
	}


	/**
	 * Escape brackets function
	 *
	 * @param string $text
	 * @return void
	 */
	public static function esc_brackets($text = '')
	{
		return str_replace(["[", "]"], ["[ ", " ]"], $text);
	}

	/**
	 * Generating html from post data
	 *
	 * @param string $type type of field.
	 * @param array  $data data of that field.
	 * @return string
	 */
	public static function data_to_html($type = '', $data = array())
	{
		$html = '';
		switch ($type) {
			case 'header';
				$guten_type = 'heading';
				$html       = sprintf(
					'<!-- wp:%s {"level" =>%s} --><h%s>%s</h%s><!-- /wp:%s -->',
					$guten_type,
					$data['level'],
					$data['level'],
					$data['text'],
					$data['level'],
					$guten_type
				);
				break;

			case 'paragraph';
				$html = sprintf('<!-- wp:paragraph --><p>%s</p><!-- /wp:paragraph -->', $data['text']);
				break;

			case 'list';
				$html = '<!-- wp:list --><ul>';
				foreach ($data['items'] as $item) {
					$html .= sprintf('<li>%s</li>', $item);
				}
				$html .= '</ul><!-- /wp:list -->';
				break;

			case 'code';
				$editor_data      = json_decode(stripslashes($_POST['editor_data']), true)['blocks'];
				foreach ($editor_data as $block) {
					if ($block['type'] === 'code') {
						$editor_data = htmlentities($block['data']['code']);
					}
				}

				$html = sprintf('<!-- wp:code --><pre class="bfe-code wp-block-code"><code>%s</code></pre><!-- /wp:code -->', $editor_data);
				break;

			case 'delimiter':
				$html = '<!-- wp:separator --><hr class="wp-block-separator bfe-delimiter"/><!-- /wp:separator -->';
				break;

			case 'embed':
				ob_start();
				require FE_Template_PATH . 'editor/embed.php';
				$html = trim(ob_get_clean());
				break;

			case 'image':
				ob_start();
				if (!empty($data['file']['url'])) {
					$image_url = $data['file']['url'];
					$image_id  = attachment_url_to_postid($image_url);
					require FE_Template_PATH . 'editor/image.php';
				}
				$html = trim(ob_get_clean());
				break;

			case 'wpImageGallery':
				ob_start();
				if (!empty($data) && count($data) > 1) {
					$image_ids = $data;
					require FE_Template_PATH . 'editor/gallery.php';
				} else {
					$image_id  = $data[0];
					require FE_Template_PATH . 'editor/image.php';
				}

				$html = trim(ob_get_clean());
				break;

			case 'quote':
				ob_start();
				require FE_Template_PATH . 'editor/quote.php';
				$html = ob_get_clean();
				break;

			case 'warning':
				$html = sprintf('<figure class="warning"><figcaption>%s</figcaption><p>%s</p></figure>', $data['title'], $data['message']);
				break;

			case 'table':
				ob_start();
				require FE_Template_PATH . 'editor/table.php';
				$html = ob_get_clean();
				break;

			case 'checklist':
				ob_start();
				require FE_Template_PATH . 'editor/checklist.php';
				$html = ob_get_clean();
				break;
		}
		$html .= htmlspecialchars("\n");

		/**
		 * HTML that generated type of block and data of
		 * that block you can use this filter for make your html elements
		 */
		$html = apply_filters('bfe_editor_data_to_html_filter', self::esc_brackets($html), $type, $data);

		return $html;
	}
}
