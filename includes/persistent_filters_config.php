<?php
if (!defined('ABSPATH')) {
    die;
}

/**
 * Singleton class that holds all configurable items
 * @class    Persistent_Filters_Config
 * @package    Persistent_Filters
 * @author    Oveas Functionality Provider
 */
class Persistent_Filters_Config
{
    /**
     * Reference to self
     * @access    private
     * @var        object reference    $instance
     */
    private static $instance;

    /**
     * The unique plugin name.
     * @access    private
     * @var        string    $pluginName    The string used to uniquely identify this plugin.
     */
    private $pluginName;

    /**
     * The user ID for which the settings are being retrieved
     * @access    private
     * @var        int    $user_id    The user ID for which the settings are being retrieved
     */
    private $user_id;

    /**
     * Constuctor
     */
    private function __construct ()
    {
        $this->user_id = get_current_user_id();
        $this->pluginName = 'Persistent_Filters';
    }

    /**
     * Instantiate an object if not existing yet and return a reference
     * @return    Persistent_Filters_Config    Reference to self
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
     * @return    string    Plugin name
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Set default values
     * @return    array
     */
    public function getDefaults()
    {
        return [
              'keys-ignored'     => ['edit','trash','delete','untrash','bulk-edit'] // Bulk actions - never make them persistent
            , 'export-params'    => ['export','download','order_status','start_date','end_date'] // Don't set filters when used for export
            , 'ignore-export'    => 'yes'
            , 'pages' => [
                 'edit' => [
                     'keys-allowed'     => [
                         'post'         => ['post_type','cat','orderby','order','s']
                       , 'page'         => ['post_type','orderby','order','s','m', 'cat']
                       , 'product'      => ['post_type','product_cat','stock_status','product_type','product_brand','orderby','order','s']
                       , 'shop_order'   => ['post_type','m','_created_via','_customer_user','orderby','order','s']
                       , 'shop_coupon'  => ['post_type','coupon_type','s']
                       , 'tribe_events' => ['post_type','orderby','order','s']
                     ]
                   , 'fallback-enabled' => 'no'
                   , 'keys-fallback'    => ['post_type','orderby','order','s']
                 ]
               , 'admin' => [
                     'keys-allowed' => [
                         'wc-orders' => ['page', 's', 'search-filter', 'm', '_created_via', '_customer_user']
                       , 'simple_history_admin_menu_page' => ['page', 'messages', 'date', 'users', 'q', 'exclude-users', 'levels', 'metadata', 'context']
                     ]
                   , 'needs-ajax' => ['simple_history_admin_menu_page'] // Pages that need AJAX support for filters to work properly
                   , 'ajax-endpoints'   => ['simple-history', '/simple-history/']
                   , 'fallback-enabled' => 'no'
                   , 'keys-fallback'    => ['s','orderby','order']
                 ]
               , 'users' => [
                     'keys-allowed' => [
                         'all' => ['s','orderby','order']
                     ]
                   , 'fallback-enabled' => 'no'
                   , 'keys-fallback'    => ['s','orderby','order']
                ]
            ]
        ];
    }

	/**
	 * Get the info text for the settings page
	 * @return    array    Info text
	 */
    public function getInfoText()
    {
        return [
                 'edit' => [
                     'post'         => __( 'Wordpress Posts', 'persistent-filters' )
                   , 'page'         => __( 'Wordpress Pages', 'persistent-filters' )
                   , 'product'      => __( 'WooCommerce Products', 'persistent-filters' )
                   , 'shop_order'   => __( 'WooCommerce Legacy Orders', 'persistent-filters' )
                   , 'shop_coupon'  => __( 'WooCommerce Coupons', 'persistent-filters' )
                   , 'tribe_events' => __( 'Events Calendar plugin Events listing', 'persistent-filters' )
				   , 'info_text'    => __( "Select for which post types of 'wp-admin/edit.php' the filters should be saved.", 'persistent-filters' )
                ]
                , 'admin' => [
                     'wc-orders' => __( 'WooCommerce HPOS Orders', 'persistent-filters' )
                   , 'simple_history_admin_menu_page' => __( 'Simple History plugin', 'persistent-filters' )
				   , 'info_text'    => __( "Select for which post types of 'wp-admin/admin.php' the filters should be saved.", 'persistent-filters' )
                ]
               , 'users' => [
                     'all' => __( 'Wordpress Users', 'persistent-filters' )
				   , 'info_text'    => __( "Select for which post types of 'wp-admin/users.php' the filters should be saved.", 'persistent-filters' )
                ]
				, 'enable_fallback' => __( 'When enabled, if no stored filters exist for a page, the default filters "s", "orderby", and "order" will be used instead. Disable this if you experience infinte loops.', 'persistent-filters' )
				, 'search_field' => __( 'Uncheck this if you do not want searches to be persistent. This works for all pages and post types.', 'persistent-filters' )
	       ];
    }

	/**
     * Get the default pages to save filters for
     * @return  array   List of supported pages and keys
     */
    private function getSupportedPagesKeys()
    {
        $settings = Persistent_Filters_Config::getInstance()->getDefaults();
        $pages = array();
        foreach ( $settings['pages'] as $page => $page_settings ) {
            $pages[$page] = array();
            foreach ( $page_settings['keys-allowed'] as $post_type => $url_filter_keys ) {
                $pages[$page][] = $post_type;
            }
        }
        return $pages;
    }

    /**
     * Get the preferences for this user
     * @return  array   User's preferences
     */
    public function getSettings()
    {
        $defaults = array(
            'pages' => $this->getSupportedPagesKeys(),
            'fallback' => false,
            'save_search' => true,
        );
        $stored = get_user_meta( $this->user_id, 'persistent_filters_settings', true );
        if ( ! is_array( $stored ) ) {
            $stored = array();
        }
        return wp_parse_args( $stored, $defaults );
    }

    /**
     * Get the default settings and apply the user-specific preferences
     * @return  array   User-specific settings
     */
    public function getUserSettings()
    {
        $defaults = $this->getDefaults();
        $stored = get_user_meta( get_current_user_id(), 'persistent_filters_settings', true );

        if ( ! is_array( $stored ) ) {
            $stored = array();
        }
        $settings = wp_parse_args( $stored, $defaults );
        $settings['pages'] = $defaults['pages'];

        if ( isset( $stored['pages'] ) && is_array( $stored['pages'] ) ) {
            foreach ( $settings['pages'] as $page => &$page_settings ) {
                $selected = isset( $stored['pages'][ $page ] ) && is_array( $stored['pages'][ $page ] )
                    ? $stored['pages'][ $page ]
                    : array();
                $selected_keys = array();
                foreach ( $selected as $value ) {
                    if ( is_string( $value ) && array_key_exists( $value, $page_settings['keys-allowed'] ) ) {
                        $selected_keys[ $value ] = true;
                    }
                }
                $page_settings['keys-allowed'] = array_intersect_key( $page_settings['keys-allowed'], $selected_keys );
            }
            unset( $page_settings );
        }
        if ($stored['fallback']) {
            foreach ($settings['pages'] as $page => &$page_settings) {
                $page_settings['fallback-enabled'] = 'yes';
            }
        }
        if (!$stored['save_search']) {
            foreach ($settings['pages'] as $page => &$page_settings) {
                foreach ($page_settings['keys-allowed'] as $post_type => $action_array) {
                    if (in_array('s', $action_array)) {
                        $index = array_search('s', $action_array);
                        if ($index !== false) {
                            unset($settings['pages'][$page]['keys-allowed'][$post_type][$index]);
                        }
                    }
                }
                if (in_array('s', $settings['pages'][$page]['keys-fallback'])) {
                    $index = array_search('s', $settings['pages'][$page]['keys-fallback']);
                    if ($index !== false) {
                        unset($settings['pages'][$page]['keys-fallback'][$index]);
                    }
                }
            }
        }

        return $settings;
    }
}
