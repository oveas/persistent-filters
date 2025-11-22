<?php
if (!defined('ABSPATH')) {
	die;
}

/**
 * Singleton class that holds all configurable items
 * @class	Persistent_Filters_Config
 * @package	Persistent_Filters
 * @author	Oveas Functionality Provider
 */
class Persistent_Filters_Config
{
	/**
	 * Reference to self
	 * @var		object reference	$instance
	 */
	private static $instance;

	/**
	 * The unique plugin name.
	 * @access	private
	 * @var		string	$pluginName	The string used to uniquely identify this plugin.
	 */
	private $pluginName;

	/**
	 * All configurable items at global level and the info used to setup the form
	 * @access	private
	 * @var		array	$settings	Configurable items
	 */
	private $settings;


	/**
	 * Constuctor
	 */
	private function __construct ()
	{
		$this->pluginName = 'Persistent_Filters';
	}

	/**
	 * Instantiate an object if not existing yet and return a reference
	 * @return	Persistent_Filters_Config	Reference to self
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self(); // Ensure it returns an object of the class
		}
		return self::$instance;
	}

	/**
	 * Return the plugin name
	 * @return	string	Plugin name
	 */
	public function getPluginName()
	{
		return $this->pluginName;
	}

	/**
	 * Return the version of this plugin
	 * @return	string	Plugin version
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Set default values
	 * @return	array
	 */
	public function getDefaults()
	{
		return [
			  'keys-ignored' => ['edit','trash','delete','untrash','bulk-edit'] // Bulk actions - never make them persistent
    			, 'keys-allowed' => [
					  'post'    => ['post_type','cat','orderby','order','s']
					, 'page'    => ['post_type','orderby','order','s','m', 'cat']
					, 'product' => ['post_type','product_cat','stock_status','product_type','product_brand','orderby','order','s']
				] // Filters per post type that should be persistent
				, 'keys-fallback' => ['post_type','orderby','order','s'] // Default filters for unknown post types (fallback)
				, 'export-params' => ['export','download','order_status','start_date','end_date'] // Don't set filters when used for export
				, 'ignore-export' => 'yes'
				, 'fallback-enabled' => 'yes'
		    ];
	}
}
