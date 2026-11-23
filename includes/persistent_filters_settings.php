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
	 * @access	private
     * @var		object reference	$instance
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
		require_once plugin_dir_path( __FILE__ ) . 'persistent_filters_config.php';
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
     * Save settings from the POST data
     */
    private function saveFromPost()
    {
        if ( ! is_user_logged_in() ) {
            return;
        }
        $defaults = Persistent_Filters_Config::getInstance()->getDefaults();
        $submitted_pages = isset( $_POST['persistent_filters_pages'] ) && is_array( $_POST['persistent_filters_pages'] )
            ? wp_unslash( $_POST['persistent_filters_pages'] )
            : array();
        $enabled_pages = isset( $_POST['persistent_filters_page_enabled'] ) && is_array( $_POST['persistent_filters_page_enabled'] )
            ? wp_unslash( $_POST['persistent_filters_page_enabled'] )
            : array();
        $pages = array();
        foreach ( $defaults['pages'] as $page => $page_settings ) {
            if ( isset( $enabled_pages[ $page ] ) ) {
                $pages[ $page ] = array_keys( $page_settings['keys-allowed'] );
                continue;
            }

            if ( isset( $submitted_pages[ $page ] ) && is_array( $submitted_pages[ $page ] ) ) {
                $selected_values = array_filter( $submitted_pages[ $page ], 'is_string' );
                $selected = array_map( 'sanitize_key', $selected_values );
                $pages[ $page ] = array_values( array_intersect( array_keys( $page_settings['keys-allowed'] ), $selected ) );
                if ( empty( $pages[ $page ] ) ) {
                    unset( $pages[ $page ] );
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
        $settings = Persistent_Filters_Config::getInstance()->getSettings();
        $defaults = Persistent_Filters_Config::getInstance()->getDefaults();
        $infotext = Persistent_Filters_Config::getInstance()->getInfoText();
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
                                <?php foreach ( $defaults['pages'] as $page => $page_settings ) :
                                    $section_id = 'persistent-filters-' . sanitize_html_class( $page );
                                    $selected_keys = isset( $settings['pages'][ $page ] ) ? (array) $settings['pages'][ $page ] : array();
                                    $all_selected = ! empty( $page_settings['keys-allowed'] )
                                        && count( array_intersect( array_keys( $page_settings['keys-allowed'] ), $selected_keys ) ) === count( $page_settings['keys-allowed'] );
                                ?>
                                    <section class="persistent-filters-settings-section">
                                        <label style="display:block; margin-bottom:4px;">
                                            <input class="persistent-filters-page-toggle" type="checkbox" name="persistent_filters_page_enabled[<?php echo esc_attr( $page ); ?>]" value="1" <?php checked( $all_selected ); ?> />
                                            <?php echo esc_html( $page ); ?>
                                            <i class="dashicons dashicons-editor-help" title="<?php echo esc_html($infotext[$page]['info_text']); ?>"></i>
                                        </label>
                                        <button class="button-link persistent-filters-section-toggle" type="button" aria-expanded="true" aria-controls="<?php echo esc_attr( $section_id ); ?>">
                                            <?php esc_html_e( 'Hide options', 'persistent-filters' ); ?>
                                        </button>
                                        <div id="<?php echo esc_attr( $section_id ); ?>" class="persistent-filters-section-options">
                                            <span class="description"><?php esc_html_e( 'Allowed post types for page', 'persistent-filters' ); ?> <strong><?php echo esc_html( $page ); ?></strong>:</span>
                                            <?php foreach ( $page_settings['keys-allowed'] as $key => $fields ) : ?>
                                                <label style="display:block; margin-bottom:4px;">
                                                    <input style="margin-left: 40px;" class="persistent-filters-subitem-toggle" type="checkbox" name="persistent_filters_pages[<?php echo esc_attr( $page ); ?>][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $selected_keys, true ) ); ?> />
                                                    <?php echo esc_html( $key ); ?>
                                                    <i class="dashicons dashicons-editor-help" title="<?php echo esc_html($infotext[$page][$key]); ?>"></i>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </section>
                                <?php endforeach; ?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable fallback', 'persistent-filters' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="persistent_filters_fallback" value="1" <?php checked( (bool) $settings['fallback'] ); ?> />
                                <?php esc_html_e( 'Use fallback when no stored filters exist', 'persistent-filters' ); ?>
                                <i><?php esc_html_e( '(Experimental!)', 'persistent-filters' ); ?></i>
                                <i class="dashicons dashicons-editor-help" title="<?php echo esc_html($infotext['enable_fallback']); ?>"></i>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Save search field', 'persistent-filters' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="persistent_filters_save_search" value="1" <?php checked( (bool) $settings['save_search'] ); ?> />
                                <?php esc_html_e( 'Save the search field ("s") value', 'persistent-filters' ); ?>
                                <i class="dashicons dashicons-editor-help" title="<?php echo esc_html($infotext['search_field']); ?>"></i>
                            </label>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="persistent_filters_settings_submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'persistent-filters' ); ?>">
                </p>
            </form>
        </div>
        <style>
        .persistent-filters-page-toggle {
            position: relative;
        }
        .persistent-filters-page-toggle:indeterminate::before {
            content: "";
            position: absolute;
            top: 6px;
            left: 3px;
            width: 8px;
            height: 2px;
            margin: 0;
            background-color: #2271b1;
        }
        </style>
        <script>
        document.querySelectorAll('.persistent-filters-settings-section').forEach(function (section) {
            var parent = section.querySelector('.persistent-filters-page-toggle');
            var children = Array.from(section.querySelectorAll('.persistent-filters-subitem-toggle'));
            var toggle = section.querySelector('.persistent-filters-section-toggle');
            var options = section.querySelector('.persistent-filters-section-options');

            function updateParent() {
                var checkedCount = children.filter(function (child) { return child.checked; }).length;
                parent.checked = children.length > 0 && checkedCount === children.length;
                parent.indeterminate = checkedCount > 0 && checkedCount < children.length;
                parent.setAttribute('aria-checked', parent.indeterminate ? 'mixed' : String(parent.checked));
            }

            parent.addEventListener('change', function () {
                children.forEach(function (child) { child.checked = parent.checked; });
                updateParent();
            });
            children.forEach(function (child) {
                child.addEventListener('change', updateParent);
            });
            toggle.addEventListener('click', function () {
                var expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!expanded));
                options.hidden = expanded;
                toggle.textContent = expanded ? '<?php echo esc_js( __( 'Show options', 'persistent-filters' ) ); ?>' : '<?php echo esc_js( __( 'Hide options', 'persistent-filters' ) ); ?>';
            });
            updateParent();
        });
        </script>
        <?php
    }
}

// Instantiate the settings class so the Settings page is added to the admin menu
add_action( 'plugins_loaded', array( 'Persistent_Filters_Settings', 'getInstance' ) );
