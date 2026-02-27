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
		add_action('load-woocommerce_page_wc-orders', array($this, 'setAdminFilters'), 30, 0);

		add_action('restrict_manage_posts', array($this, 'resetEditFilters'), 30, 1);
		add_action('woocommerce_order_list_table_restrict_manage_orders', array($this, 'resetAdminFilters'), 30, 1);
	}
	
	public function setEditFilters()
	{
		$this->setFilters('edit');
	}

	public function setAdminFilters()
	{
		$this->setFilters('admin');
	}

	/**
	 * Take all given filters from the current URL and check for filter parameters.
	 * If found, store them in user meta for the current user and post type.
	 * @param	string $page	Current page
	 */
	public function setFilters($page = 'edit')
	{
		if ($page == 'edit') {
			$post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : 'post';
			$meta_key  = "_persistent_filter_{$page}_{$post_type}";
		} elseif ($page == 'admin') {
			$post_type = isset($_GET['page']) ? sanitize_key($_GET['page']) : 'wc-orders';
			$meta_key  = "_persistent_filter_{$page}_{$post_type}";
		} else {
			return; // Safety net
		}
		$user_id   = get_current_user_id();

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
		if (!empty($_GET) && (count($_GET) > 1 || isset($_GET['orderby']))) {
			$new_query = array_intersect_key($_GET, array_flip($keys_allowed));
			if (count($new_query) > 1) { // Only save if there are supported filters
				$query_string = http_build_query($new_query, '', '&');
				update_user_meta($user_id, $meta_key, $query_string);
				return;
			}
		}

		if (($page == 'edit' && (isset($_GET['post_type']))
			|| (!isset($_GET['post_type']) && false !== strpos($_SERVER['REQUEST_URI'], 'edit.php')))
		  || ($page == 'admin' && (isset($_GET['page']))
		  	|| (!isset($_GET['page']) && false !== strpos($_SERVER['REQUEST_URI'], 'admin.php')))
		) {
			$saved = get_user_meta($user_id, $meta_key, true);
			if ($saved) {
				$original_query = '';
				if (count($_GET) > 1) { // Make sure quicklinks are added back
					if ($page == 'edit') {
						unset ($_GET['post_type']);
					} elseif ($page == 'admin') {
						unset ($_GET['page']);
					}
					$original_query = '&' . http_build_query($_GET, '', '&');
				}
				wp_safe_redirect(admin_url($page . '.php?' . $saved . $original_query));
				exit;
			}
		}
	}

	public function resetEditFilters($post_type)
	{
		$this->resetFilters($post_type, 'edit');
	}

	public function resetAdminFilters($post_type)
	{
		$this->resetFilters('wc-orders', 'admin'); // Given post_type is 'shop_order', translage to the page.
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
