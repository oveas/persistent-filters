<?php
if (!defined('ABSPATH')) {
	die;
}

/**
 * Main module to make WordPresss filter in the backend persistent
 * @class	Persistent_Filters_Clean
 * @package	Persistent_Filters
 * @author	Oveas Functionality Provider
 */
class Persistent_Filters_Clean
{
   	/**
	 * Initialize the cleaning functionality
	 */
	public function __construct()
	{
		add_action('admin_footer', array($this, 'modalMarkup'), 30, 1);
		add_action('admin_enqueue_scripts', array($this, 'adminAssets'), 30, 1);
		add_action('admin_post_persistent-filters_do_action', array($this, 'cleanFiltersHandler'), 30, 0);
		add_action('admin_notices', array($this, 'confirmClean'));
		add_filter('plugin_action_links_' . plugin_basename(PERSISTENT_FILTERS_MAIN_FILE), array($this, 'cleanAllFilters'), 30, 1);
	}
	

	/**
	 * Add 'Reset all filters' link to the plugin action links
	 * @param	array $links	Current plugin action links
	 * @return	array
	 */
	public function cleanAllFilters($links)
	{
		$links[] = '<a href="#" class="persistent-filters-open-modal">' . __('Reset all filters', 'persistent-filters') . '</a>';
		return $links;
	}

	/**
	 * Add the modal markup to the admin footer
	 */
	public function modalMarkup()
	{
		if (!get_current_screen()->id || get_current_screen()->id !== 'plugins') {
        	return;
    	}
		?>
    		<div id="persistent-filters-modal" style="display:none;">
		        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    	        	<input type="hidden" name="action" value="persistent-filters_do_action" />
    	        	<?php wp_nonce_field( 'persistent-filters_do_action_nonce' ); ?>
					<h2><?php _e('Reset all filters', 'persistent-filters'); ?></h2>
					<p>
	    	        	<label>
        	    			<?php _e('This will remove all persistent filters for all post types.', 'persistent-filters'); ?></p>
        	            	<select name="users">
	    	                    <option value="me"><?php _e('For me only', 'persistent-filters'); ?></option>
        	                	<option value="all"><?php _e('For all users', 'persistent-filters'); ?></option>
        	            	</select>
        	        	</label>
        	    	</p>  
        	    	<p>
	    	            <button class="button button-primary"><?php _e('Remove filters', 'persistent-filters'); ?></button>
        	        	<button type="button" class="button persistent-filters-close-modal"><?php _e('Cancel', 'persistent-filters'); ?></button>
        	    	</p>
        		</form>
    		</div>
		<?php
	}

	/**
	 * Enqueue admin assets
	 * @param	string $hook	Current admin page hook
	 */
	public function adminAssets($hook)
	{
		if ( $hook !== 'plugins.php' ) {
        	return;
		}	

		wp_add_inline_style('wp-admin', '
 	       #persistent-filters-modal {
    	        position: fixed;
        	    top: 50%;
	            left: 50%;
            	transform: translate(-50%, -50%);
            	background: #fff;
            	padding: 20px;
            	border: 1px solid #ccd0d4;
            	box-shadow: 0 5px 15px rgba(0,0,0,.3);
            	z-index: 100000;
        	}
        	#persistent-filters-modal h2 { margin-top: 0; }
        	#persistent-filters-backdrop {
	            position: fixed;
            	top:0; left:0;
            	width:100%; height:100%;
            	background: rgba(0,0,0,.4);
            	z-index: 99999;
        	}
    	');

	    wp_add_inline_script('jquery-core', '
    	    jQuery(function($){
            	$(".persistent-filters-open-modal").on("click", function(e){
	                e.preventDefault();
                	$("body").append("<div id=\'persistent-filters-backdrop\'></div>");
                	$("#persistent-filters-modal").show();
            	});

	            $(".persistent-filters-close-modal").on("click", function(){
                	$("#persistent-filters-modal").hide();
                	$("#persistent-filters-backdrop").remove();
            	});
        	});
    	');
    }

	/**
	 * Handle the clean filters request
	 */
	public function cleanFiltersHandler()
	{
		if (!isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'persistent-filters_do_action_nonce')) {
			wp_die( 'Invalid nonce.');
    	}
    	if (!current_user_can( 'manage_options' ) ) {
	        wp_die( 'Permission denied.');
    	}
		$users = sanitize_text_field($_POST['users']);
		$this->cleanFilters($users);

	    wp_redirect(add_query_arg(
			  array('persistent_filters_done' => '1', 'freset_users' => $users)
			, admin_url( 'plugins.php' ))
		);
	    exit;
	}

	/**
	 * Show confirmation notice after cleaning filters
	 */
	public function confirmClean()
	{
    	if (isset( $_GET['persistent_filters_done'])) {
			$users = sanitize_text_field( $_GET['freset_users']);

        	echo '<div class="notice notice-success is-dismissible">
        		<p><strong>Persistent Filters:</strong> ' . __('All filters have been reset', 'persistent-filters')
				. ' ' . ( 'all' === $users ? __('for all users.', 'persistent-filters') : __('for your user only.', 'persistent-filters') )	
				. '</p>
            	</div>';
    	}
	}

	/**
	 * Clean persistent filters for all post types and given users. Called on uninstall or via admin action.
	 * @param	string $users	"me" or "all"
	 */
	public function cleanFilters($users)
	{
		require_once plugin_dir_path( __FILE__ ) . 'persistent_filters_config.php';
		$settings = Persistent_Filters_Config::getInstance()->getDefaults();

		if ('all' === $users) {
			$query = new WP_User_Query(
				array('capability' => 'edit_posts', 'fields'  => 'ID')
			);
	
			$adm_ids = $query->get_results();
			foreach ($settings['keys-allowed'] as $post_type => $url_filter_keys) {
				$meta_key = "_persistent_filter_{$post_type}";
				foreach ($adm_ids as $user_id) {
					// FIXME: Doesn't work on uninstall
					delete_user_meta ($user_id, $meta_key);
				}
			}
			return;
		}

		$user_id = get_current_user_id();
		foreach ($settings['keys-allowed'] as $post_type => $url_filter_keys) {
			$meta_key = "_persistent_filter_{$post_type}";
			delete_user_meta ($user_id, $meta_key);
		}
	}
}