<?php
/**
 * @package Persistent_Filters
 * @version 1.2.0
 * Plugin Name: Persistent Filters
 * Plugin URI: https://github.com/oveas/persistent-filters
 * Description: This plugin enables flexible filter persistence per post type, including a reset button and safeguards. Works for WordPress posts and pages, and Woocommerce products and orders.
 * Author: Oveas Functionality Provider
 * Version: 1.2.0
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
	Persistent_Filters_Execute();
}


