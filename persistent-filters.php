<?php
/**
 * @package Persistent_Filters
 * @version 1.3.0
 * Plugin Name: Persistent Filters
 * Plugin URI: https://github.com/oveas/persistent-filters
 * Description: This plugin enables flexible filter persistence per post type, including a reset button and safeguards. Works for WordPress posts and pages, and Woocommerce products and orders.
 * Author: Oveas Functionality Provider
 * Version: 1.3.0
 * Text Domain: persistent-filters
 * Domain Path: /languages
 * Requires at least: 4.6
 * Tested up to: 6.9
 * License: GPLv2 or later
 * Author URI: http://oveas.com/
 */

if (!defined('ABSPATH')) {
	die;
}
define('PERSISTENT_FILTERS_MAIN_FILE', __FILE__);
define('PERSISTENT_FILTERS_VERSION', '1.3.0');

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
		if (version_compare($current_version, '1.3.0', '<')) {
			global $wpdb;

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


