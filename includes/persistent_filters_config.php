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
					  'post'       => ['post_type','cat','orderby','order','s']
					, 'page'       => ['post_type','orderby','order','s','m', 'cat']
					, 'product'    => ['post_type','product_cat','stock_status','product_type','product_brand','orderby','order','s']
					, 'shop_order' => ['post_type','m','_created_via','_customer_user','orderby','order','s']
				] // Filters per post type that should be persistent
				, 'keys-fallback'    => ['post_type','orderby','order','s'] // Default filters for unknown post types (fallback)
				, 'export-params'    => ['export','download','order_status','start_date','end_date'] // Don't set filters when used for export
				, 'ignore-export'    => 'yes'
				, 'fallback-enabled' => 'no'
		    ];
	}
}


/*

* Supported:

Posts:
?s&post_status=all&post_type=post&action=-1&m=202601&cat=1&filter_action=Filter&paged=1&action2=-1

Pages:
?s&post_type=page&m=202601

Products:
?s&post_type=product&product_cat=electronics-tools&product_type=variable&stock_status=instock&product_brand

Orders:
admin.php?s&post_status=all&post_type=shop_order&action=-1&m=202601&_created_via=admin&_customer_user=66&filter_action=Filter&paged=1&action2=-1

Coupons:
?s&post_status=all&post_type=shop_coupon&action=-1&coupon_type=fixed_cart&filter_action=Filter&paged=1&action2=-1


 * To implement:


Other
=====
Reviews (uses post type "Product"):
edit.php?page=product-reviews&post_type=product&pagegen_timestamp=2026-01-26+08%3A35%3A49&comment_status=all&review_type=comment&review_rating=2&product_id=165&filter_action=Filter

WC HPOS orders (not using edit.php):
admin.php?page=wc-orders&paged=1&s&search-filter=all&action=-1&m=202110&_created_via=admin&_customer_user=73&filter_action=Filter&action2=-1

Comments (not using edit.php):
edit-comments.php?s&comment_status=all&pagegen_timestamp=2026-01-26+08%3A39%3A56&_total=1&_per_page=20&_page=1&_ajax_fetch_list_nonce=b3fd265cfc&action=-1&comment_type=comment&filter_action=Filter&action2=-1&orderby=comment_post_ID&order=asc

Users (not using edit.php):
users.php?s=ager&action=-1&new_role&action2=-1&new_role2&orderby=email&order=asc 
*/