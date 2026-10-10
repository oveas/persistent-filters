<?php
if (!defined('ABSPATH')) {
	die;
}

/**
 * Singleton class handling user specific settings for the Persistent Filters plugin
 * @class	Persistent_Filters_Settings
 * @package	Persistent_Filters
 * @author	Oveas Functionality Provider
 */
class Persistent_Filters_Settings
{
    /**
     * Reference to self
     * @var		object reference	$instance
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
		require_once plugin_dir_path( __FILE__ ) . 'persistent_filters_config.php';
        add_action( 'admin_menu', array( $this, 'addMenu' ) );
		add_filter('plugin_action_links_' . plugin_basename(PERSISTENT_FILTERS_MAIN_FILE), array($this, 'openSettingsPage'), 30, 1);
    }

	/**
	 * Add 'Settings' link to the plugin action links
	 * @param	array $links	Current plugin action links
	 * @return	array
	 */
	public function openSettingsPage($links)
	{
		$links[] = '<a href="options-general.php?page=persistent-filters-settings">' . __('Settings', 'persistent-filters') . '</a>';
		return $links;
	}

    /**
	 * Instantiate an object if not existing yet and return a reference
     * @return	Persistent_Filters_Settings Reference to self
     */
    public static function getInstance()
    {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Add a page under Settings for each admin user to edit their own settings
     */
    public function addMenu()
    {
        add_options_page(
            __( 'Persistent Filters Settings', 'persistent-filters' ),
            __( 'Persistent Filters', 'persistent-filters' ),
            'read',
            'persistent-filters-settings',
            array( $this, 'renderPage' )
        );
    }

    /**
     * Save settings from the POST data
     */
    private function saveFromPost()
    {
        if ( ! is_user_logged_in() ) {
            return;
        }
        $pages = array();
        if ( isset( $_POST['persistent_filters_pages'] ) && is_array( $_POST['persistent_filters_pages'] ) ) {
            foreach ( $_POST['persistent_filters_pages'] as $page => $post_type ) {
                $pages[$page] = 1;
                if ( is_array( $post_type ) ) {
                    $pages[$page] = array();
                    foreach ( $post_type as $p ) {
                        $pages[$page][] = sanitize_text_field( wp_unslash( $p ) );
                    }
                }
            }
        }
        $fallback = isset( $_POST['persistent_filters_fallback'] ) ? true : false;
        $save_search = isset( $_POST['persistent_filters_save_search'] ) ? true : false;
        $to_store = array(
            'pages' => $pages,
            'fallback' => (bool) $fallback,
            'save_search' => (bool) $save_search,
        );
        update_user_meta( get_current_user_id(), 'persistent_filters_settings', $to_store );
    }

    /**
     * Return the nonce field name for the settings form
     * @return	string	Nonce field name
     */
    private function nonceName()
    {
        return 'persistent_filters_settings_nonce';
    }

    /**
     * Render the settings page
     */
    public function renderPage()
    {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( isset( $_POST['persistent_filters_settings_submit'] ) ) {
            if ( ! isset( $_POST[ $this->nonceName() ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ $this->nonceName() ] ), 'save_persistent_filters_settings' ) ) {
                echo '<div class="notice notice-error"><p>Invalid request.</p></div>';
            } else {
                $this->saveFromPost();
                echo '<div class="updated"><p>' . esc_html__( 'Settings saved.', 'persistent-filters' ) . '</p></div>';
            }
        }
        $settings = Persistent_Filters_Config::getInstance()->getUserSettings();
        $defaults = Persistent_Filters_Config::getInstance()->getDefaults();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Persistent Filters - Personal Settings', 'persistent-filters' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'save_persistent_filters_settings', $this->nonceName() ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Pages to save filters for', 'persistent-filters' ); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php esc_html_e( 'Pages to save filters for', 'persistent-filters' ); ?></span></legend>
                                <?php foreach ($defaults['pages'] as $page => $page_settings) :
                                    if (array_key_exists( $page, (array) $settings['pages'])) {
                                        $checked = true;
                                    } else {
                                        $checked = false;
                                    }
                                ?>
                                    <label style="display:block; margin-bottom:4px;">
                                        <input type="checkbox" name="persistent_filters_pages[<?php echo esc_attr( $page ); ?>]" value="1" <?php checked( $checked ); ?> />
                                        <?php echo esc_html( $page ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Allowed post types for page', 'persistent-filters' ); ?> <strong><?php echo esc_html( $page ); ?></strong>:</p>
                                    <?php foreach ( $page_settings['keys-allowed'] as $key => $fields ) : ?>
                                        <?php
                                        if (array_key_exists( $page, (array) $settings['pages'] ) && in_array( $key, (array) $settings['pages'][$page], true )) {
                                            $checked = true;
                                        } else {
                                            $checked = false;
                                        }
                                        ?>
                                        <label style="display:block; margin-bottom:4px;">
                                            <input style="margin-left: 40px;" type="checkbox" name="persistent_filters_pages[<?php echo ($page); ?>][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $checked ); ?> />
                                            <?php echo esc_html( $key ); ?>
                                        </label>
                                        <!-- <p style="margin-left: 40px;" class="description"><?php esc_html_e( 'Allowed fields for post type', 'persistent-filters' ); ?> <?php echo esc_html( $key ); ?>:</p>
                                            <?php foreach ( $fields as $field ) : ?>
                                                <label style="display:block; margin-bottom:4px;">
                                                    <input style="margin-left: 80px;" type="checkbox" name="persistent_filters_page_keys_fields[]" value="<?php echo esc_attr( $field ); ?>" <?php checked( in_array( $page, (array) $settings['pages'], true ) ); ?> />
                                                    <?php echo esc_html( $field ); ?>
                                                </label>
                                            <?php endforeach; ?> -->
                                    <?php endforeach; ?>    
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="description"><?php esc_html_e( 'Select which admin pages should have persistent filters saved.', 'persistent-filters' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable fallback', 'persistent-filters' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="persistent_filters_fallback" value="1" <?php checked( (bool) $settings['fallback'] ); ?> />
                                <?php esc_html_e( 'Use fallback when no stored filters exist', 'persistent-filters' ); ?>
                                <i><?php esc_html_e( '(Experimental!)', 'persistent-filters' ); ?></i>
                                <i class="dashicons dashicons-editor-help" title="<?php esc_html_e( 'When enabled, if no stored filters exist for a page, the default filters "s", "orderby", and "order" will be used instead. Disable this if you experience infinte loops.', 'persistent-filters' ); ?>"></i>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Save search field', 'persistent-filters' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="persistent_filters_save_search" value="1" <?php checked( (bool) $settings['save_search'] ); ?> />
                                <?php esc_html_e( 'Save the search field ("s") value', 'persistent-filters' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="persistent_filters_settings_submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'persistent-filters' ); ?>">
                </p>
            </form>
        </div>
        <?php
    }
}

// Instantiate the settings class so the Settings page is added to the admin menu
add_action( 'plugins_loaded', array( 'Persistent_Filters_Settings', 'getInstance' ) );
