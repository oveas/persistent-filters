<?php
/**
 * @package Persistent_Filters
 * @version 1.1.0
 * Plugin Name: Persistent Filters
 * Plugin URI: https://github.com/oveas/persistent-filters
 * Description: This plugin enables flexible filter persistence per post type, including a reset button and safeguards. Works for WordPress posts and pages, and Woocommerce products and orders.
 * Author: Oveas Functionality Provider
 * Version: 1.1.0
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
 * Instantiate this plugin
 */
function Persistent_Filters_Execute()
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/persistent_filters_config.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/persistent_filters.php';
	$locale = apply_filters('persistent_filters_locale', get_locale(), '');
	load_textdomain('persistent-filters', plugin_dir_path( __FILE__ )  . 'language/' . $locale . '.mo' );
	$plugin = new Persistent_Filters();
}

if (is_admin()) {
	register_uninstall_hook( __FILE__, 'Persistent_Filters_Uninstall' );
	Persistent_Filters_Execute();
}
