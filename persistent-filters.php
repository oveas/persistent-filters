<?php
/**
 * @package Persistent_Filters
 * @version 1.0.0
 * Plugin Name: Persistent Filters
 * Plugin URI: https://github.com/oveas/persistent-filters
 * Description: This plugin enables flexible filter persistence per post type, including a reset button and safeguards. Works for WordPress posts and pages, Woocommerce products and for standard filters with all other post types.
 * Author: Oveas Functionality Provider
 * Version: 1.0.0
 * Tested up to: 6.8.3
 * Author URI: http://oveas.com/
 */

if (!defined('ABSPATH')) {
	die;
}

/**
 * Uninstall this plugin
 */
function Persistent_Filters_Uninstall()
{
	$query = new WP_User_Query(
		array('capability' => 'edit_posts', 'fields'  => 'ID')
	);
	$adm_ids = $query->get_results();

	$settings = Persistent_Filters_Config::getInstance()->getDefaults();
	foreach ($settings['keys-allowed'] as $post_type => $url_filter_keys) {
		$meta_key = "_persistent_filter_{$post_type}";
		foreach ($adm_ids as $user_id) {
			delete_user_meta ($user_id, $meta_key);
		}
	}
}

/**
 * Instantiate this plugin
 */
function Persistent_Filters_Execute()
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/persistent_filters_config.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/persistent_filters.php';
	$plugin = new Persistent_Filters();
}

if (is_admin()) {
	register_uninstall_hook( __FILE__, 'Persistent_Filters_Uninstall' );
	Persistent_Filters_Execute();
}
