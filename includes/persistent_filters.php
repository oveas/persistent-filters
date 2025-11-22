<?php
if (!defined('ABSPATH')) {
	die;
}

/**
 * Main module to make WordPresss filter in the backend persistent
 * @class	Persistent_Filters
 * @package	Persistent_Filters
 * @author	Oveas Functionality Provider
 */
class Persistent_Filters
{
	/**
	 * Configuration
	 * @access	private
	 * @var		array	$settings	Configuration
	 */
	private $settings;

	/**
	 * Constructor
	 * Bring the core functionality to live.
	 */
	public function __construct()
	{
		require_once plugin_dir_path( __FILE__ ) . 'persistent_filters_config.php';
		$this->settings = Persistent_Filters_Config::getInstance()->getDefaults();
		
		add_action('load-edit.php', array($this, 'setFilters'), 30, 0);
		add_action('restrict_manage_posts', array($this, 'resetFilters'), 30, 1);
		add_filter('views_edit-product', array($this, 'alterViewLinks'), 30, 1);
	}
	
	/**
	 * Take all given filters from the current URL and check for filter parameters.
	 * If found, store them in user meta for the current user and post type.
	 */
	public function setFilters()
	{
		$post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : 'post';
		$user_id   = get_current_user_id();
		$meta_key  = "_persistent_filter_{$post_type}";

		if ((isset($_REQUEST['action']) && in_array($_REQUEST['action'], $this->settings['keys-ignored'], true)) ||
			(isset($_REQUEST['action2']) && in_array($_REQUEST['action2'], $this->settings['keys-ignored'], true))) {
			return;
		}

		if ('yes' == $this->settings['ignore-export']) {
			foreach ($this->settings['export-params'] as $param) {
				if (isset($_REQUEST[$param])) {
					return;
				}
			}
		}

		if (isset($_REQUEST['_wpnonce']) || isset($_REQUEST['_wp_http_referer'])) {
			return;
		}

		// Reset filters
		if (isset($_GET['reset_filters'])) {
			delete_user_meta($user_id, $meta_key);
			wp_safe_redirect(admin_url('edit.php?post_type=' . $post_type));
			exit;
		}

		// Check if filters are set in the URL. If so, save them.
		$keys_allowed = $this->settings['keys-allowed'][$post_type] ?? ('yes' == $this->settings['fallback-enabled'] ? $this->settings['keys-fallback'] : []);
		if (!empty($_GET) && (count($_GET) > 1 || isset($_GET['orderby']))) {
			$new_query = array_intersect_key($_GET, array_flip($keys_allowed));
			$query_string = http_build_query($new_query, '', '&');
			update_user_meta($user_id, $meta_key, $query_string);
			return;
		}

		// Reset filters when the querystring is empty
		if ((isset($_GET['post_type']) && 1 === count($_GET))
			|| (!isset($_GET['post_type']) && false !== strpos($_SERVER['REQUEST_URI'], 'edit.php'))) {
			$saved = get_user_meta($user_id, $meta_key, true);
			if ($saved) {
				wp_safe_redirect(admin_url('edit.php?' . $saved));
				exit;
			}
		}
	}

	/**
	 * Update the view links so they include the persistent filters
	 * @param	array $views	Current view links
	 * @return	array
	 */
	public function alterViewLinks ($views)
	{
		$saved = get_user_meta(get_current_user_id(), '_persistent_filter_' . $_GET['post_type'], true);
		if (!$saved) {
			return $views;
		}
		parse_str($saved, $saved_args);
		if (isset($_GET['post_type'])) {
			foreach ($views as $key => $html) {

				if ( preg_match( '/href="([^"]+)"/', $html, $matches ) ) {
					$url = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

					list(, $query) = explode('?', $url, 2);
					parse_str($query, $qry_args);

					$this_view_args = $saved_args;
					if ((isset($qry_args['all_posts']) && 1 == $qry_args['all_posts']) && isset($this->settings['all-resets'][$_GET['post_type']])) {
						foreach ($this->settings['all-resets'][$_GET['post_type']] as $reset_key) {
							if (isset($this_view_args[$reset_key])) {
								unset($this_view_args[$reset_key]);
							}
						}
					}

					$new_query = $qry_args + $this_view_args;
					$query_string = http_build_query($new_query, '', '&');											

					$views[$key] = preg_replace( '/href="([^"]+)"/', 'href="edit.php?' . $query_string . '"', $html);
        		}
        	}
        }

	    return $views;
	}

	/**
	 * Add the 'Reset filters' button to the admin list
	 * @param	string $post_type	Current post type being listed
	 */
	public function resetFilters($post_type)
	{
		$user_id  = get_current_user_id();
		$meta_key = "_persistent_filter_{$post_type}";
		$saved    = get_user_meta($user_id, $meta_key, true);

		if ($saved) {
			$reset_url = add_query_arg([
				  'post_type'     => $post_type
				, 'reset_filters' => 1
				]
				, admin_url('edit.php')
			);

			echo '<a href="'
				. esc_url($reset_url)
				. '" class="button" style="float:right; margin-right:5px;">'
				. esc_html(__('Reset filters', 'persistent-filters'))
				. '</a>';
		}
	}
}
