<?php
/**
 * @package Persistent_Filters
 * @version 1.3.1
 * Plugin Name: Persistent Filters
 * Plugin URI: https://github.com/oveas/persistent-filters
 * Description: This plugin enables flexible filter persistence per post type, including a reset button and safeguards. Works for WordPress posts and pages, and Woocommerce products and orders.
 * Author: Oveas Functionality Provider
 * Version: 1.3.1
 * Text Domain: persistent-filters
 * Domain Path: /languages
 * Requires at least: 4.6
 * Tested up to: 7.0
 * License: GPLv2 or later
 * Author URI: http://oveas.com/
 */

if (!defined('ABSPATH')) {
	die;
}
define('PERSISTENT_FILTERS_MAIN_FILE', __FILE__);
define('PERSISTENT_FILTERS_VERSION', '1.3.1');

/**
 * Uninstall this plugin
 */
function Persistent_Filters_Uninstall()
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/persistent_filters_clean.php';
	$cleaner = new Persistent_Filters_Clean();
	$cleaner->cleanFilters('all');
}

/**
 * Update the database if the plugin version has changed
 */
function Persistent_Filters_Update_Database()
{
	$current_version = get_option('persistent_filters_version');
    if ( false === $current_version ) {
        $current_version = '1.2.0';
		add_option('persistent_filters_version', '1.2.0');
    }

	if ($current_version !== PERSISTENT_FILTERS_VERSION) {
		global $wpdb;

		if (version_compare($current_version, '1.3.0', '<')) {
			$query = $wpdb->prepare(
				"SELECT umeta_id, meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				'_persistent_filter_%'
			);
			$ukeys = $wpdb->get_results($query);
			foreach ($ukeys as $umeta) {
				$new_meta_key = str_replace('_persistent_filter_', '_persistent_filter_edit_', $umeta->meta_key);
				$wpdb->update(
					$wpdb->usermeta,
					array('meta_key' => $new_meta_key),
					array('umeta_id' => $umeta->umeta_id)
				);
			}
		}

		// Migration for 1.3.1: convert stored URL-encoded query-string filters to arrays
		if (version_compare($current_version, '1.3.1', '<')) {
			$migrate_keys = [ '_persistent_filter_%', '_persistent_filter_edit_%' ];
			foreach ($migrate_keys as $pattern) {
				$query = $wpdb->prepare(
					"SELECT umeta_id, user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
					$pattern
				);
				$rows = $wpdb->get_results($query, ARRAY_A);
				foreach ($rows as $row) {
					$meta_value = $row['meta_value'];
					// If value looks like a query string, convert to array and save via update_user_meta
					if (is_string($meta_value) && strpos($meta_value, '=') !== false) {
						parse_str($meta_value, $parsed);
						if (!empty($parsed)) {
							// Use WP API to ensure proper serialization
							update_user_meta((int) $row['user_id'], $row['meta_key'], $parsed);
						}
					}
				}
			}
		}

		update_option('persistent_filters_version', PERSISTENT_FILTERS_VERSION);
	}
}

/**
 * Load the textdomain for translations
 */
function Persistent_Filters_Load_Textdomain()
{
    load_plugin_textdomain('persistent-filters', false, dirname(plugin_basename( __FILE__ )) . '/language');
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
	add_action( 'init', 'Persistent_Filters_Load_Textdomain' );
	add_action( 'plugins_loaded', 'Persistent_Filters_Update_Database' );
	Persistent_Filters_Execute();
}


