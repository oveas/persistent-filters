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
		require_once plugin_dir_path( __FILE__ ) . 'persistent_filters_clean.php';
		$this->settings = Persistent_Filters_Config::getInstance()->getDefaults();

		$cleaner = new Persistent_Filters_Clean();
		add_action('load-edit.php', array($this, 'setEditFilters'), 30, 0);
		add_action('wp_ajax_persistent_filters_update', array($this, 'handleAjaxFilterUpdate'));
		add_action('load-woocommerce_page_wc-orders', array($this, 'setAdminFilters'), 30, 0);

		add_action('restrict_manage_posts', array($this, 'resetEditFilters'), 30, 1);
		add_action('woocommerce_order_list_table_restrict_manage_orders', array($this, 'resetAdminFilters'), 30, 1);

		add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
		add_action('admin_init', array($this, 'checkForAdminFilters'), 5);
	}

	/**
	 * Apply saved filters for pages that don´t have their own filter handling
	 */
	public function checkForAdminFilters()
	{
		if (!is_admin()) {
			return;
		}
		if (isset($_GET['page']) && array_key_exists($_GET['page'], $this->settings['pages']['admin']['keys-allowed'])) {
			$this->setFilters('admin', $_GET);
		}
	}

	/**
	 * Enqueue admin scripts on admin pages that require AJAX support for filters to work properly
	 */
	public function enqueueAdminScripts()
	{
		if ((isset($_GET['page']) && in_array($_GET['page'], $this->settings['pages']['admin']['needs-ajax'], true))) {
			wp_enqueue_script(
				  'persistent-filters-admin'
				, plugin_dir_url(PERSISTENT_FILTERS_MAIN_FILE) . 'js/persistent_filters.js'
				, array()
				, PERSISTENT_FILTERS_VERSION
				, true
			);

			// Localize the script with nonce and other data
			// Prepare saved filters for the current page (backwards compatible)
			$page = sanitize_text_field($_GET['page']);
			$meta_key = "_persistent_filter_admin_{$page}";
			$user_id = get_current_user_id();
			$saved = get_user_meta($user_id, $meta_key, true);
			if (is_string($saved)) {
				parse_str($saved, $saved_array);
			} elseif (is_array($saved)) {
				$saved_array = $saved;
			} else {
				$saved_array = array();
			}

			wp_localize_script('persistent-filters-admin', 'pf_data', array(
				  'nonce' => wp_create_nonce('persistent_filters_nonce')
				, 'saved_filters' => $saved_array
				, 'endpoints' => $this->settings['pages']['admin']['ajax-endpoints']
			));
		}
	}

	/**
	 * Handle AJAX call from JavaScript to update filters for pages that need AJAX support
	 */
	public function handleAjaxFilterUpdate()
	{
		check_ajax_referer('persistent_filters_nonce', 'nonce');
		$this->setFilters('admin', $_POST);
		wp_send_json_success(array('message' => 'Filters updated'));
	}
	
	public function setEditFilters()
	{
		$this->setFilters('edit');
	}

	public function setAdminFilters()
	{
		$this->setFilters('admin', $_REQUEST);
	}

	/**
	 * Take all given filters from the current URL and check for filter parameters.
	 * If found, store them in user meta for the current user and post type.
	 * @param	string $page	Current page
	 * @param	array|null $request	Request parameters to check for filters. If null, $_REQUEST will be used.
	 */
	public function setFilters($page = 'edit', $request = null)
	{
		if ($request === null) {
			if ($_REQUEST) {
				$request = $_REQUEST;
			} else {
				return;
			}
		}

		if ($page == 'edit') {
			$post_type = isset($request['post_type']) ? sanitize_key($request['post_type']) : 'post';
			$meta_key  = "_persistent_filter_{$page}_{$post_type}";
		} elseif ($page == 'admin') {
			$post_type = isset($request['page']) ? sanitize_key($request['page']) : 'wc-orders';
			$meta_key  = "_persistent_filter_{$page}_{$post_type}";
		} else {
			return; // Safety net
		}
		$user_id   = get_current_user_id();

		if ((isset($request['action']) && in_array($request['action'], $this->settings['keys-ignored'], true)) ||
			(isset($request['action2']) && in_array($request['action2'], $this->settings['keys-ignored'], true))) {
			return;
		}

		if ('yes' == $this->settings['ignore-export']) {
			foreach ($this->settings['export-params'] as $param) {
				if (isset($request[$param])) {
					return;
				}
			}
		}

		if (isset($request['_wpnonce']) || isset($request['_wp_http_referer'])) {
			return;
		}

		// Reset filters
		if (isset($request['reset_filters'])) {
			delete_user_meta($user_id, $meta_key);
			if ($page == 'edit') {
				wp_safe_redirect(admin_url('edit.php?post_type=' . $post_type));
			} elseif ($page == 'admin') {
				wp_safe_redirect(admin_url('admin.php?page=' . $post_type));
			} else {
				return; // Safety net
			}
			exit;
		}

		// If no allowed keys for this post type and fallback is disabled, do nothing
		// This check must be after the reset above to make sure saved filters can still be removed after
		// fallback has been disabled.
		if ('yes' != $this->settings['pages'][$page]['fallback-enabled'] && !isset($this->settings['pages'][$page]['keys-allowed'][$post_type])) {
			return;
		}

		// Check if filters are set in the URL. If so, save them.
		$keys_allowed = isset($this->settings['pages'][$page]['keys-allowed'][$post_type]) ? $this->settings['pages'][$page]['keys-allowed'][$post_type] : $this->settings['pages'][$page]['keys-fallback'];
		if (!empty($request) && (count($request) > 1 || isset($request['orderby']))) {
			$new_query = array_intersect_key($request, array_flip($keys_allowed));
			if (count($new_query) > 1) { // Only save if there are supported filters
				// Store filters as an array (not as a URL-encoded string) so values are preserved
				update_user_meta($user_id, $meta_key, $new_query);
				return;
			}
		}

		if (($page == 'edit' && (isset($request['post_type']))
			|| (!isset($request['post_type']) && false !== strpos($_SERVER['REQUEST_URI'], 'edit.php')))
		  || ($page == 'admin' && (isset($request['page']))
		    || (!isset($request['page']) && false !== strpos($_SERVER['REQUEST_URI'], 'admin.php')))
		) {
			$saved = get_user_meta($user_id, $meta_key, true);
			if ($saved) {
				// Normalize saved filters to an array for consistent handling.
				if (is_string($saved)) {
					// Backwards compatibility: saved value might be a URL-encoded query string.
					parse_str($saved, $saved_array);
				} elseif (is_array($saved)) {
					$saved_array = $saved;
				} else {
					$saved_array = array();
				}

				if (!empty($saved_array)) {
					$original_query = '';
					if (count($request) > 1) { // Make sure quicklinks are added back
						if ($page == 'edit') {
							unset ($request['post_type']);
						} elseif ($page == 'admin') {
							unset ($request['page']);
						}
						$original_query = '&' . http_build_query($request, '', '&');
					}

					$saved_query = http_build_query($saved_array, '', '&');
					wp_safe_redirect(admin_url($page . '.php?' . $saved_query . $original_query));
					exit;
				}
			}
		}
	}

	/**
	 * Reset filters for the given post type at the edit screen
	 * @param	string $post_type	Post type to reset filters for
	 */
	public function resetEditFilters($post_type)
	{
		$this->resetFilters($post_type, 'edit');
	}

	/**
	 * Reset filters for the given post type at the admin screen
	 * @param	string $post_type	Post type to reset filters for
	 */
	public function resetAdminFilters($post_type)
	{
		$translated_post_type = [
			  'shop_order' => 'wc-orders'
		];
		$this->resetFilters($translated_post_type[$post_type] ?? $post_type, 'admin');
	}

	/**
	 * Add the 'Reset filters' button to the admin list
	 * @param	string $post_type	Current post type being listed
	 * @param	string $page		Current page
	 */
	public function resetFilters($post_type, $page = 'edit')
	{
		$user_id  = get_current_user_id();
		$meta_key = "_persistent_filter_{$page}_{$post_type}";
		$saved    = get_user_meta($user_id, $meta_key, true);

		if ($saved) {
			if ($page == 'edit') {
				$reset_url = add_query_arg([
						  'post_type'     => $post_type
						, 'reset_filters' => 1
					]
					, admin_url('edit.php')
				);
			} elseif ($page == 'admin') {
				$reset_url = add_query_arg([
						  'page'          => $post_type
						, 'reset_filters' => 1
					]
					, admin_url('admin.php')
				);
			} else {
				return; // Safety net
			}
			echo '<a href="'
				. esc_url($reset_url)
				. '" class="button" style="float:right; margin-right:5px;">'
				. esc_html(__('Reset filters', 'persistent-filters'))
				. '</a>';
		}
	}
}
