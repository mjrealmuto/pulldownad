<?php
/**
 * Plugin Name.
 *
 * @package   Pull Down Ad
 * @author    Michael Realmuto <mrealmuto@hbi.com>
 * @license   GPL-2.0+
 * @link      http://www.wilv.com
 * @copyright 2013 Hubbard Broadcasting Inc
 */

class PulldownAd {

	protected $version = '1.0.0';

	protected $plugin_slug = 'pull-down-ad';
	protected $plugin_slug_new = 'pull-down-ad-new';
	protected $plugin_slug_edit = 'pull-down-ad-edit';
	protected $plugin_slug_entries = 'pull-down-ad-entries';

	protected static $instance = null;
	
	protected $plugin_screen_hook_suffix = null;
	protected $plugin_screen_hook_suffix_add = null;
	protected $plugin_screen_hook_suffix_edit = null;
	protected $plugin_screen_hook_suffix_entries = null;
	
	protected static $db_name = "";

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() 
	{

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page. TODO: Rename "plugin-name.php" to the name your plugin
		// $plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'plugin-name.php' );
		// add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'admin_init', array($this, 'pulldownad_settings_init') );
		
		add_action( 'wp_ajax_new_pulldownad', array($this, 'new_pulldownad' ) );
		
		add_action( 'wp_ajax_edit_pulldownad', array($this, 'edit_pulldownad' ) );
		
		add_action( 'wp_ajax_change_pulldownad_image', array($this, 'change_pulldownad_image' ) );
		
		//add_action( 'wp_ajax_nopriv_get_pulldownad', array($this, 'pulldownad_get_ad' ) );
		//add_ew( 'wp_ajax_get_pulldownad', array($this, 'pulldownad_get_ad') );
		
		add_shortcode('get_pulldownad', array($this, 'pulldownad_get_ad') );
		
		//AJAX Call that will update click and open counts
		add_action( 'wp_ajax_delete_pulldownad', array( $this, 'delete_pulldownad' ) );
		
		add_action( 'wp_ajax_pulldownad_compare_date', array( $this, 'compare_pulldownad_dates' ) );
		
		add_action( 'wp_ajax_change_pulldownad_asset', array( $this, 'change_pulldownad_asset' ) );
		
		add_action( 'wp_ajax_delete_pulldownad_video', array( $this, 'pulldownad_delete_video' ) );
		
		add_action( 'wp_ajax_nopriv_pulldownad_formentry', array( $this, 'pulldownad_formentry' ) );
		
		add_action( 'wp_ajax_pulldownad_get_template', array( $this, 'pulldownad_get_template' ) );
		
		add_action( 'wp_ajax_pulldownad_delete_ad', array($this, 'pulldownad_delete_ad' ) );
		
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * Return the database name 
	 *
	 * @since 1.0.0
	 *
	 * @return string name of the database being used for storing takeover information
	*/
	protected static function get_db_name( )
	{
		global $wpdb;
	
		$db_name = $wpdb->get_var( "SELECT DATABASE( )" );
			
		return $db_name;
		
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		
		//Include the global $wpdb variable for database usage.	
		global $wpdb;
		
		$table_name_one = $wpdb->prefix . "pull_down_ad";
		$table_name_two = $wpdb->prefix . "pull_down_ad_entries";
	
				
		//Require the upgrade.php script to call DBDelta command to insert the above SQL Command
		require_once(ABSPATH . "wp-admin/includes/upgrade.php");
		
		//If the table does not exist, create the variable that houses the CREATE SQL command
		if( ! $wpdb->get_var("SHOW TABLES LIKE '{$table_name_one}'") )
		{
		
			$create_pulldownad_table = "CREATE TABLE " . $table_name_one . " (\n";
			$create_pulldownad_table .= "id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,\n";
			$create_pulldownad_table .= "title_client varchar(255) NOT NULL,\n";
			$create_pulldownad_table .= "position varchar(10) NOT NULL,\n";
			$create_pulldownad_table .= "content longtext,\n";
			$create_pulldownad_table .= "start_date datetime,\n";
			$create_pulldownad_table .= "end_date datetime,\n";
			$create_pulldownad_table .= "tag_image varchar( 255 ) )";
			
			//Create the table
			dbDelta( $create_pulldownad_table );

		}
		
		if( ! $wpdb->get_var("SHOW TABLES LIKE '{$table_name_two}'") )
		{
			$create_entries_table = "CREATE TABLE " . $table_name_two . " (\n";
			$create_entries_table .= "id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,\n";
			$create_entries_table .= "pulldown_ad_id int( 11 ), \n";
			$create_entries_table .= "first_name varchar( 255 ),\n";
			$create_entries_table .= "last_name varchar (255 ),\n";
			$create_entries_table .= "street_address varchar (255 ),\n";
			$create_entries_table .= "street_address_2 varchar ( 255 ),\n";
			$create_entries_table .= "city varchar ( 255 ),\n";
			$create_entries_table .= "state varchar ( 25 ),\n";
			$create_entries_table .= "zip varchar ( 10 ),\n";
			$create_entries_table .= "phone varchar (50 ),\n";
			$create_entries_table .= "dob date,\n";
			$create_entries_table .= "email_address varchar ( 255 ),\n";
			$create_entries_table .= "extra_fields longtext ) ";
			
			//Create the table
			dbDelta( $create_entries_table );
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ||
			 $screen->id == $this->plugin_screen_hook_suffix_add ||
			 $screen->id == $this->plugin_screen_hook_suffix_edit ||
			 $screen->id == $this->plugin_screen_hook_suffix_entries ) 
		{
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
			wp_enqueue_style( $this->plugin_slug . '-colorbox-styles', plugins_url( 'css/colorbox.css', __FILE__), array(), $this->version );
			wp_enqueue_style( 'jquery-ui-style', "/js/jquery-ui/themes/base/jquery-ui.css", array( ), $this->version);
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		
		if ( $screen->id == $this->plugin_screen_hook_suffix ||
			 $screen->id == $this->plugin_screen_hook_suffix_add ||
			 $screen->id == $this->plugin_screen_hook_suffix_edit ||
			 $screen->id == $this->plugin_screen_hook_suffix_entries ) 
		{
			wp_enqueue_script("jquery-ui-datepicker" );
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
			wp_enqueue_script('colorbox', plugins_url('js/jquery.colorbox-min.js', __FILE__), array('jquery') );
			wp_enqueue_script('jqueryForms', plugins_url('js/jquery.form.js', __FILE__), array('jquery') );
		}
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
		wp_enqueue_style( 'jquery-ui-style', "/js/jquery-ui/themes/base/jquery-ui.css", array( ), $this->version);

	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() 
	{
		wp_enqueue_script("jquery-ui-draggable");
		wp_enqueue_script('jqueryForms', plugins_url('js/jquery.form.js', __FILE__), array('jquery') );
		//wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery', 'jqueryForms' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() 
	{
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Pull Down Ad', $this->plugin_slug ),
			__( 'Pull Down Ad', $this->plugin_slug ),
			'edit_posts',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
		
		$this->plugin_screen_hook_suffix_add = add_submenu_page($this->plugin_slug, 
			__("New Pull Down Ad", $this->plugin_slug_new), 
			__("New Pull Down Ad", $this->plugin_slug_new), 
			'edit_posts', 
			$this->plugin_slug_new, 
			array( $this, 'display_plugin_new_page')
		);
		
		$this->plugin_screen_hook_suffix_edit = add_submenu_page($this->plugin_slug, 
			__("Edit Pull Down Ad", $this->plugin_slug_edit), 
			__("Edit Pull Down Ad", $this->plugin_slug_edit), 
			'edit_posts', 
			$this->plugin_slug_edit, 
			array( $this, 'display_plugin_edit_page')
		);
		
		$this->plugin_screen_hook_suffix_entries = add_submenu_page($this->plugin_slug, 
			__("Pull Down Ad Entries", $this->plugin_slug_entries), 
			__("Pull Down Ad Entries", $this->plugin_slug_entries), 
			'edit_posts', 
			$this->plugin_slug_entries, 
			array( $this, 'display_plugin_entries_page')
		);
		
	}
	
	public function pulldownad_settings_init( )
	{
		register_setting('pulldownad_options', 'pulldownad_option');
		
		add_settings_section('pulldownad_settings', NULL, NULL, $this->plugin_slug);
		add_settings_section('pulldownad_add_settings', NULL, NULL, $this->plugin_slug_new);
		add_settings_section('pulldownad_edit_settings', NULL, NULL, $this->plugin_slug_edit);
		
		global $wpdb;
		
		
		//Setting Fields for New Takeover
		
		add_settings_field(
			'title-client', 
			"Title/Client", 
			array( $this, "pulldownad_title_client_callback" ), 
			$this->plugin_slug_new, 
			'pulldownad_add_settings', 
			array( 
				"size" => "45", 
				"type" => "text" 
			) 
		);
		
		add_settings_field(
			'position', 
			"Position", 
			array( $this, "pulldownad_position_callback" ), 
			$this->plugin_slug_new, 
			'pulldownad_add_settings', 
			array( 
				 "type" => "radio",
				 "options" => array('left','right') 
			) 
		);
		
		add_settings_field(
			'height',
			"Height",
			array( $this, "pulldownad_height_callback" ),
			$this->plugin_slug_new,
			'pulldownad_add_settings',
			array(
				"value" => 768	
			)
		);
		
		add_settings_field(
			'drop_height',
			'Drop Height',
			array($this, "pulldownad_drop_height_callback" ),
			$this->plugin_slug_new,
			'pulldownad_add_settings',
			array(
				"value" => 15	
			)
		);
		
		add_settings_field(
			'page_content', 
			"Page Content", 
			array( $this, "pulldownad_pagecontent_callback" ), 
			$this->plugin_slug_new, 
			'pulldownad_add_settings'			 
		);
		
		add_settings_field(
			'link',
			"Link",
			array($this, "pulldownad_link_callback" ),
			$this->plugin_slug_new,
			'pulldownad_add_settings'
		);
		
		add_settings_field(
			'bg_color',
			'BG Color',
			array($this, "pulldownad_bg_color_callback" ),
			$this->plugin_slug_new,
			'pulldownad_add_settings'
		);
		
		add_settings_field(
			'bg_image',
			'BG Image',
			array($this, "pulldownad_bg_image_callback" ),
			$this->plugin_slug_new,
			'pulldownad_add_settings'
		);
		
		add_settings_field(
			'bg_image_repeat',
			'BG Image Repeat',
			array($this, "pulldownad_bg_image_repeat_callback"),
			$this->plugin_slug_new,
			'pulldownad_add_settings'
		);
		
		add_settings_field(
			'video_assets',
			'Video Assets',
			array($this, "pulldownad_video_assets_callback"),
			$this->plugin_slug_new,
			'pulldownad_add_settings',
			array(
				"video" => ""
			)
		);
		
		add_settings_field(
			'entry_form',
			'Entry Form',
			array($this, "pulldownad_entry_form_callback"),
			$this->plugin_slug_new,
			'pulldownad_add_settings',
			array(
				"has_entry" => 0	
			)
		);
		
		add_settings_field(
			'start-date', 
			"Start Date", 
			array( $this, "pulldownad_start_date_callback" ), 
			$this->plugin_slug_new, 
			'pulldownad_add_settings'
		);
		
		add_settings_field(
			'end-date', 
			"End Date", 
			array( $this, "pulldownad_end_date_callback" ), 
			$this->plugin_slug_new, 
			'pulldownad_add_settings' 
		);
		
		add_settings_field(
			'tag-image', 
			"Tag Image", 
			array( $this, "pulldownad_tagimage_callback" ), 
			$this->plugin_slug_new, 
			'pulldownad_add_settings'
		);
		
		//Setting Fields for Ediing a Pulldown Ad
		if( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) )
		{
			$table_name = $wpdb->prefix . "pull_down_ad";
			
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT title_client, position, content, start_date, end_date, tag_image
													FROM {$table_name}
													WHERE id = %d", $_GET['id'] ) );
		
			if( $row )
			{
				add_settings_field(
					'title-client', 
					"Title/Client", 
					array( $this, "pulldownad_title_client_callback" ), 
					$this->plugin_slug_edit, 
					'pulldownad_edit_settings', 
					array( 
						"size" => "45", 
						"type" => "text",
						"value" => $row->title_client
					) 
				);
				
				add_settings_field(
					'position', 
					"Position", 
					array( $this, "pulldownad_position_callback" ), 
					$this->plugin_slug_edit, 
					'pulldownad_edit_settings', 
					array( 
						 "type" => "radio",
						 "options" => array('left','right'),
						 "value" => $row->position 
					) 
				);
				
				$pulldown_content = unserialize( $row->content );
				
				$entry = FALSE;
				
				add_settings_field(
					'height',
					"Height",
					array( $this, "pulldownad_height_callback" ),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array(
						"value" => $pulldown_content['height']
					)
				);
				
				$drop_height = 0;
				
				if( ! isset( $pulldown_content['drop_height'] )  )
				{
					$drop_height = 15;
				}
				else
				{
					$drop_height = $pulldown_content['drop_height'];
				}
				
				add_settings_field(
					'drop_height',
					'Drop Height',
					array($this, "pulldownad_drop_height_callback" ),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array(
						"value" => $drop_height	
					)
				);
				
				add_settings_field(
					'page_content', 
					"Page Content", 
					array( $this, "pulldownad_pagecontent_callback" ), 
					$this->plugin_slug_edit, 
					'pulldownad_edit_settings',
					array(
						"value" => stripslashes( $pulldown_content['content'] ),
					)			 
				);
				
				add_settings_field(
					'link',
					"Link",
					array($this, "pulldownad_link_callback" ),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array(
						"value" => $pulldown_content['link'],
					)
				);
				
				add_settings_field(
					'bg_color',
					'BG Color',
					array($this, "pulldownad_bg_color_callback" ),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array(
						"value" => $pulldown_content['bg_color']
					)
				);
				
				add_settings_field(
					'bg_image',
					'BG Image',
					array($this, "pulldownad_bg_image_callback" ),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array( 
						"bg_image" => $pulldown_content['bg_image']['path']
					
					)
				);
				
				add_settings_field(
					'bg_image_repeat',
					'BG Image Repeat',
					array($this, "pulldownad_bg_image_repeat_callback"),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array(
						"value"	=> $pulldown_content['bg_image']['repeat']
					)
				);
				
				add_settings_field(
					'video_assets',
					'Video Assets',
					array($this, "pulldownad_video_assets_callback"),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array(
						"video" => $pulldown_content['videos']
					)
				);
				
				add_settings_field(
					'entry_form',
					'Entry Form',
					array($this, "pulldownad_entry_form_callback"),
					$this->plugin_slug_edit,
					'pulldownad_edit_settings',
					array(
						"has_entry" 		=> (bool)$pulldown_content['has_entry'],
						"form_data" 		=> $pulldown_content['form_data']
					)
				);

				add_settings_field(
					'start-date', 
					"Start Date", 
					array( $this, "pulldownad_start_date_callback" ), 
					$this->plugin_slug_edit, 
					'pulldownad_edit_settings',
					array(
						"value" => $row->start_date
					)
				);
				
				add_settings_field(
					'end-date', 
					"End Date", 
					array( $this, "pulldownad_end_date_callback" ), 
					$this->plugin_slug_edit, 
					'pulldownad_edit_settings',
					array(
						"value" => $row->end_date
					) 
				);
				
				add_settings_field(
					'tag-image', 
					"Tag Image", 
					array( $this, "pulldownad_tagimage_callback" ), 
					$this->plugin_slug_edit, 
					'pulldownad_edit_settings',
					array(
						"value" => $row->tag_image
					)
				);
			}
		}		
	}
	
	
	public function pulldownad_title_client_callback( $args )
	{
		$html = "";
	
		if( isset( $args['value'] ) )
		{
			$html = "<input type='text' id='pulldownad_title_client' name='pulldownad_title_client' value='{$args['value']}' size='45'/>";
		}
		else
		{
			$html = "<input type='text' id='pulldownad_title_client' name='pulldownad_title_client'  size='45'/>";
		}
		
		$html .= "<div id='pulldownad_title_client_error' class='sub_error'></div>";
		
		echo $html;
	}
	
	public function pulldownad_position_callback( $args )
	{
		$html = "";
		
		if( isset( $args['value'] ) )
		{
			if( $args['value'] == 'left' )
			{
				$html = "<table ><tr><td style='padding-top: 0;'>Left</td><td style='padding-top: 0;'><input type='radio' name='pulldownad_position' value='left' checked='checked' /> </td>
						<td style='padding-top: 0;'>Right</td><td style='padding-top: 0;'><input type='radio' name='pulldownad_position' value='right' /></td></tr></table>";	
			}
			else
			{
				$html = "<table><tr><td style='padding-top: 0;'>Left</td><td style='padding-top: 0;'><input type='radio' name='pulldownad_position' value='left'  /> </td>
						<td style='padding-top: 0;'>Right</td><td style='padding-top: 0;'><input type='radio' name='pulldownad_position' value='right' checked='checked' /></td></tr></table>";
			}
		}
		else
		{
			$html = "<table><tr><td style='padding-top: 0;'>Left</td><td style='padding-top: 0;'><input type='radio' name='pulldownad_position' value='left'  checked='checked'/> </td>
					<td style='padding-top: 0;'>Right</td><td style='padding-top: 0;'><input type='radio' name='pulldownad_position' value='right' /></td></tr></table>";
		}
		
		echo $html;
	}
	
	public function pulldownad_height_callback( $args )
	{
		$html = "";
		
		if( isset( $args['value'] ) )
		{
			$html = "<input type='text' name='pulldownad_height' value='" . $args['value'] . "' size='5'/> ( Max Height is 768px )<br />";
		}
		else
		{
			$html = "<input type='text' name='pulldownad_height' value='768px' size='5'/> ( Max Height is 768px )<br />";
		}
		
		$html .= "<div id='pulldownad_height_error'></div>";
		
		echo $html;
	}
	
	public function pulldownad_drop_height_callback( $args )
	{
		$html = "";
		
		$html = "<input type='text' name='pulldownad_drop_height' value='" . $args['value'] . "' size='5'/> ( Must be at least 15px )<br />";
		
		$html .= "<div id='pulldownad_drop_height_error'></div>";
		
		echo $html;
	}
	
	public function pulldownad_link_callback( $args )
	{
		$html = "";
		
		if( isset( $args['value'] ) )
		{
			$html = "<input type='text' id='pulldownad_link' name='pulldownad_link' value='" . $args['value'] . "' size='45' />";	
		}
		else
		{
			$html = "<input type='text' id='pulldownad_link' name='pulldownad_link' size='45' />";
		}
		
		$html .= "<div id='pulldownad_link_error'></div>";
		
		echo $html;
	}
	
	public function pulldownad_bg_color_callback( $args )
	{
		$html = "";
		
		if( isset( $args['value'] ) )
		{
			$html = "<input type='text' id='pulldownad_bgcolor' name='pulldownad_bgcolor' value='" . $args['value'] . "' />";
		}	
		else
		{
			$html = "<input type='text' id='pulldownad_bgcolor' name='pulldownad_bgcolor' />";
		}
		
		echo $html;	
	}
	
	public function pulldownad_bg_image_callback( $args )
	{
		$html = "";
	
		if( isset($args['bg_image'] ) )
		{
			$filename = basename( $args['bg_image'] );
			
			if( ! empty( $filename ) )
			{
				$html .= " {$filename} &nbsp; <a href='javascript: changeAsset(0, \"{$filename}\", \"bg_image\");'>Change</a> &nbsp; <a class='pulldownad_images' href='{$args['bg_image']}'>View</a> ";
				$html .= "<input type='hidden' name='pulldownad_image_file' value='" . $args['bg_image'] . "' />";
			}
			else
			{
				echo "<input type='file' name='pulldownad_image' />";
			}
		}
		else
		{
			$html = "<input type='file' name='pulldownad_image' />";
		}
		
		echo $html;
	}
	
	public function pulldownad_bg_image_repeat_callback( $args )
	{
		$html = "";
	
		if( isset( $args['value'] ) )
		{
			if( $args['value'] == "x" )
			{
				$html .= "<table><tr><td>BG Image Repeat</td><td> X <input type='radio' name='pulldownad_bg_repeat' value='x' checked='checked' />&nbsp;Y<input type='radio' name='pulldownad_bg_repeat' value='y'  />&nbsp; Both <input type='radio' name='pulldownad_bg_repeat' value='both' />&nbsp;None <input type='radio' name='pulldownad_bg_repeat' value='none' /></td></tr></table>";	
			}
			else if( $args['value'] == "y" )
			{
				$html .= "<table><tr><td>BG Image Repeat</td><td> X <input type='radio' name='pulldownad_bg_repeat' value='x' checked='checked' />&nbsp;Y<input type='radio' name='pulldownad_bg_repeat' value='y' checked='checked' />&nbsp; Both <input type='radio' name='pulldownad_bg_repeat' value='both' />&nbsp;None <input type='radio' name='pulldownad_bg_repeat' value='none' /></td></tr></table>";	
			}
			else if( $args['value'] == "both" )
			{
				$html .= "<table><tr><td>BG Image Repeat</td><td> X <input type='radio' name='pulldownad_bg_repeat' value='x' checked='checked' />&nbsp;Y<input type='radio' name='pulldownad_bg_repeat' value='y'  />&nbsp; Both <input type='radio' name='pulldownad_bg_repeat' value='both' checked='checked'/>&nbsp;None <input type='radio' name='pulldownad_bg_repeat' value='none' /></td></tr></table>";	
			}
			else
			{
				$html .= "<table><tr><td> X <input type='radio' name='pulldownad_bg_repeat' value='x' checked='checked' />&nbsp;Y<input type='radio' name='pulldownad_bg_repeat' value='y'  />&nbsp; Both <input type='radio' name='pulldownad_bg_repeat' value='both' />&nbsp;None <input type='radio' name='pulldownad_bg_repeat' value='none' checked='checked'/></td></tr></table>";	
			}
		}
		else
		{
			$html .= "<table><tr><td> X <input type='radio' name='pulldownad_bg_repeat' value='x' checked='checked' />&nbsp;Y<input type='radio' name='pulldownad_bg_repeat' value='y'  />&nbsp; Both <input type='radio' name='pulldownad_bg_repeat' value='both' />&nbsp;None <input type='radio' name='pulldownad_bg_repeat' value='none' checked='checked'/></td></tr></table>";	
		}
		
		echo $html;
	}
	
	public function pulldownad_video_assets_callback( $args )
	{
		$html = "";
		
		$html .= self::get_video_callback( $args['video'] );
		
		echo $html;
		
	}
	
	public function pulldownad_entry_form_callback( $args )
	{
		$html = "";
		
		if( $args['has_entry'] == 1 )
		{
			$html .= "<input type='checkbox' name='has_entry_form' value='yes' checked='checked' /><br />";	
			$html .= "<div id='page_entry_form' style=' padding: 5px;'>";
			$html .= "The following fields are default: <br /><br />First and Last Name, E-mail, Street Address, City, State, Zip, Phone and Birthday. <br /><br />For extra fields, click options below.";
			$html .= "<div id='fields' style=' border-radius: 3px; padding-top: 15px;'> GENERIC FIELDS <br /> <input type='button' name='textbox' value='Text' /> &nbsp; <input type='button' name='checkbox' value='Check Box' /> &nbsp;";
			$html .= "<input type='button' name='radio' value='Radio' /> &nbsp; <input type='button'  name='dropdown' value='Dropdown' /> &nbsp; <input type='button' name='multi_select' value='Multi-Select' />";
			$html .=  "&nbsp; <input type='button' name='textarea' value='Text Area' />";
			$html .= "<br /><br />";
			$html .= "<div id='extra_fields'>";
			
			$form_data = unserialize( $args['form_data'] );
			
			$extra_field_count = 0;
			
			foreach( $form_data as $form_item )
			{
				if( isset( $form_item['type'] ) )
				{
					if( $form_item['type'] == "text" )					
					{
						$html .= "<label for='ef_" . $extra_field_count . "' style='font-weight: bold'>Question</label><br />";
						$html .= "<input type='hidden' name='ef_type_" . $extra_field_count . "' value='text' />";
						$html .= "<input type='text' name='ef_text_" . $extra_field_count . "' size='45' value='" . $form_item['field'] . "' /><br />";
					 	
					 	if( $form_item['required'] == 1 )
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' checked='checked'/> Is this Required? <br /><br />";	
						}
						else
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' /> Is this Required? <br /><br />";
						}
					}
					else if( $form_item['type'] == "radio" )
					{
						$html .= "<label for='ef_" . $extra_field_count . "' style='font-weight: bold'>Question</label><br />";
						$html .= "<input type='hidden' name='ef_type_" . $extra_field_count . "' value='radio' />";
						$html .= "<input type='text' name='ef_text_" . $extra_field_count . "' size='45' value='" . $form_item['field'] . "' /><br />";
						
						if( $form_item['required'] == 1 )
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' checked='checked'/> Is this Required? <br /><br />";	
						}
						else
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' /> Is this Required? <br /><br />";
						}
						
						$html .= "<div id='radio_" . $extra_field_count . "'>";
						

						
						if( count( $form_item['inputs'] ) > 0 )
						{
						
							foreach( $form_item['inputs'] as $input )
							{
								
								$html .= "Radio Value : <input type='text' name='ef_value_" . $extra_field_count . "[]' value='" . $input['name'] . "' /><br />";
								$html .= "Radio Label : <input type='text' name='ef_label_" . $extra_field_count . "[]' value='" . $input['label'] . "' /><br />";		
							}
						}
						
						$html .= "</div>";
						$html .= "<input type='button' name='add_radio' id='" . $extra_field_count . "' value='Add Radio Button' /><br /><br />";

					}
					else if( $form_item['type'] == 'checkbox' )
					{
						$html .= "<label for='ef_" . $extra_field_count . "' style='font-weight: bold'>Question</label><br />";
						$html .= "<input type='hidden' name='ef_type_" . $extra_field_count . "' value='checkbox' />";
						$html .= "<input type='text' name='ef_text_" . $extra_field_count . "' size='45' value='" . $form_item['field'] . "' /><br />";
						
						if( $form_item['required'] == 1 )
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' checked='checked'/> Is this Required? <br /><br />";	
						}
						else
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' /> Is this Required? <br /><br />";
						}
						
						$html .= "<div id='checkbox_" . $extra_field_count . "'>";
						
						if( count( $form_item['inputs'] ) > 0 )
						{
							foreach( $form_item['inputs'] as $input )
							{
								$html .= "Checkbox Value : <input type='text' name='ef_value_" . $extra_field_count . "[]' value='" . $input['name'] . "' /><br />";
								$html .= "Checkbox Label : <input type='text' name='ef_label_" . $extra_field_count . "[]' value='" . $input['label'] . "' /><br />";
							}
						}
						
						$html .= "</div>";
						$html .= "<input type='button' name='add_checkbox' id='" . $extra_field_count . "' value='Add Checkbox' /> <br /><br />";
					}
					else if( $form_item['type'] == 'select' )
					{
						$html .= "<label for='ef_" . $extra_field_count . "' style='font-weight: bold'>Question</label><br />";
						$html .= "<input type='hidden' name='ef_type_" . $extra_field_count . "' value='select' />";
						$html .= "<input type='text' name='ef_text_" . $extra_field_count . "' size='45' value='" . $form_item['field'] . "' /><br />";
						
						if( $form_item['required'] == 1 )
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' checked='checked'/> Is this Required? <br /><br />";	
						}
						else
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' /> Is this Required? <br /><br />";
						}
						
						$html .= "<div id='options_" . $extra_field_count .  "'>";
						if( count( $form_item['inputs'] ) > 0 )
						{
							foreach( $form_item['inputs'] as $input )
							{
								$html .= "Option Value : <input type='text' name='ef_value_" . $extra_field_count . "[]' value='" . $input['name'] . "' /><br />";
								$html .= "Option Label : <input type='text' name='ef_label_" . $extra_field_count . "[]' value='" . $input['label'] . "' /><br />";
							}
						}
						$html .= "</div>";
						$html .= "<input type='button' name='add_option' value='Add Option' id='" . $extra_field_count . "' />  <br /><br />";
					}
					else if( $form_item['type'] == "multiselect" )
					{
						
						$html .= "<label for='ef_". $extra_field_count . "' style='font-weight: bold'>Question</label><br />";
						$html .= "<input type='hidden' name='ef_type_" . $extra_field_count . "' value='multi_select' />";
						$html .= "<input type='text' name='ef_text_" . $extra_field_count . "' size='45' value='" . $form_item['field'] . "' /><br />";
						
						if( $form_item['required'] == 1 )
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' checked='checked'/> Is this Required? <br /><br />";	
						}
						else
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' /> Is this Required? <br /><br />";
						}
						
						$html .= "<div id='options_" . $extra_field_count . "'>";
						if( count( $form_item['inputs'] ) > 0 )
						{
							foreach( $form_item['inputs'] as $input )
							{
								$html .= "Option Value : <input type='text' name='ef_value_" . $extra_field_count . "[]' value='" . $input['name'] . "' /><br />";
								$html .= "Option Label : <input type='text' name='ef_label_" . $extra_field_count . "[]' value='" . $input['label'] . "' /><br />";
							}
						}
						$html .= "</div>";
						$html .= "<input type='button' name='add_option' value='Add Option' id='" . $extra_field_count . "' /> <br /><br />";
					}
					else if( $form_item['type'] == "textarea" )
					{
						$html .= "<label for='ef_" . $extra_field_count . "' style='font-weight: bold'>Question</label><br />";
						$html .= "<input type='hidden' name='ef_type_" . $extra_field_count . "' value='textarea' />";
						$html .= "<input type='text' name='ef_text_" . $extra_field_count . "' size='45' value='" . $form_item['field'] . "'/><br />";
						
						if( $form_item['required'] == 1 )
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' checked='checked'/> Is this Required? <br /><br />";	
						}
						else
						{
							$html .= "<input type='checkbox' name='ef_req_" . $extra_field_count . "' value='1' /> Is this Required? <br /><br />";
						}
					}
					
					$extra_field_count++;
				}	
			}
			
			$html .= "</div>";
			$html .= "</div>";
			$html .= "<input type='hidden' name='extra_field_count' value='" . $extra_field_count . "' />";
		}
		else
		{
			$html .= "<input type='checkbox' name='has_entry_form' value='yes'  /><br />";	
			$html .= "<div id='page_entry_form' style=' padding: 5px;'>";
			$html .= "<input type='hidden' name='extra_field_count' value='0' />";
			$html .= "The following fields are default: <br /><br />First and Last Name, E-mail, Street Address, City, State, Zip, Phone and Birthday. <br /><br />For extra fields, click options below.";
			$html .= "<div id='fields' style=' border-radius: 3px; padding-top: 15px;'> GENERIC FIELDS <br /> <input type='button' name='textbox' value='Text' /> &nbsp; <input type='button' name='checkbox' value='Check Box' /> &nbsp;";
			$html .= "<input type='button' name='radio' value='Radio' /> &nbsp; <input type='button'  name='dropdown' value='Dropdown' /> &nbsp; <input type='button' name='multi_select' value='Multi-Select' />";
			$html .=  "&nbsp; <input type='button' name='textarea' value='Text Area' />";
			$html .= "<br /><br />";
			$html .= "<div id='extra_fields'></div>";
			$html .= "</div>";
		}
		
		echo $html;
	}
	
	public function pulldownad_pagecontent_callback( $args )
	{
		$html = "";
		
		$file_path = pathinfo(__FILE__);
		
		echo "<b>To Add Form or Video components to the content type '<__VIDEO__>' for video or '<__FORM__>' for form. For written content simply type in the desired <DIV> tag.</b>";
		
		if( isset( $args['value'] ) )
		{
			$html .= wp_editor( stripslashes( $args['value'] ), "pulldownad_content" );
			$html .= "<br />";
			$html .= "Select one of the buttons below for a Pulldown Ad template OR to create a new template type your code above and select 'Add Template' button below.<br /><br />";
			if( $dh = opendir( $file_path['dirname'] . "/assets/templates/") )
			{
				while( ($file = readdir( $dh ) ) !== FALSE )
				{
					if( $file != "." && $file != ".." )
					{
						list($f_name, $f_ext) = explode(".", $file);
						
						$html .= "<button name='" . $f_name ."_button'  onclick='javascript: getTemplate(\"" . $file . "\");' >" . ucwords( $f_name ) . "</button> &nbsp; ";
					}
				}
			}	
		}
		else
		{
			$html .= wp_editor("", "pulldownad_content" );
			$html .= "<br />";
			$html .= "Select one of the buttons below for a Pulldown Ad template OR to create a new template type your code above and select 'Add Template' button below.<br /><br />";
			if( $dh = opendir( $file_path['dirname'] . "/assets/templates/") )
			{
				while( ($file = readdir( $dh ) ) !== FALSE )
				{
					if( $file != "." && $file != ".." )
					{
						list($f_name, $f_ext) = explode(".", $file);
						
						$html .= "<button name='" . $f_name ."_button'  onclick='javascript: getTemplate(\"" . $file . "\");' >" . ucwords( $f_name ) . "</button> &nbsp; ";
					}
				}
			}
		}
		
		echo $html;
	}	
			
	public function pulldownad_start_date_callback( $args )
	{
		$html = "";
		
		if( count( $args ) > 0 ) 
		{
			list( $date, $time) = explode(" ", $args['value']);
				
			$html = "<input type='text' id='pulldownad_start_date' name='pulldownad_start_date' autocomplete='off' value='{$date}' />";
				
			list($hour, $min, $sec) = explode(":", $time);
				
			$html .= "&nbsp; HH";
			$html .= $this->get_hours('pulldownad_start', $hour);
			$html .= ": MM ";
			$html .= $this->get_minutes('pulldownad_start', $min);
		}
		else
		{
			$html .= "<input type='text' id='pulldownad_start_date' name='pulldownad_start_date' autocomplete='off' />";
			$html .= "&nbsp; HH";
			$html .= $this->get_hours('pulldownad_start');
			$html .= ": MM ";
			$html .= $this->get_minutes('pulldownad_start');
		}
		
		$html .= "<div id='start_date_error' class='sub_error'></div>";
		
		echo $html;
	}
	
	public function pulldownad_end_date_callback( $args )
	{
		$html = "";
	
		if( count( $args ) > 0 )
		{
			list( $date, $time) = explode(" ", $args['value']);
			
			$html = "<input type='text' id='pulldownad_end_date' name='pulldownad_end_date' autocomplete='off' value='{$date}' />";
			
			list($hour, $min, $sec) = explode(":", $time);
			
			$html .= "&nbsp; HH";
			$html .= $this->get_hours('pulldownad_end', $hour);
			$html .= ": MM ";
			$html .= $this->get_minutes('pulldownad_end', $min);
		}
		else
		{
			$html .= "<input type='text' id='pulldownad_end_date' name='pulldownad_end_date' autocomplete='off' />";
			$html .= "&nbsp; HH";
			$html .= $this->get_hours('pulldownad_end');
			$html .= ": MM ";
			$html .= $this->get_minutes('pulldownad_end'); 
		}
		
		$html .= "<div id='end_date_error' class='sub_error'></div>";
		
		echo $html;
	}
	
	public function pulldownad_tagimage_callback( $args )
	{
		$html = "";
		
		if( count( $args ) > 0 && ! empty( $args['value'] ) )
		{
			$filename = basename( $args['value'] );
			
			$html = "<table>
						<tr>
							<td>
								{$filename}
								<input type='hidden' name='pulldownad_tag_image_file' value='" . $args['value'] . "' />
							</td>
							<td><a href='javascript: changeAsset(0, \"{$filename}\", \"tag_image\");'>Change</a></td>
							<td><a class='pulldownad_images' href='{$args['value']}'>View</a></td>
						</tr>
					</table>";
		}
		else
		{
			$html = "<input type='file' name='pulldownad_tag_image' /><br />";
		}
		
		$html .= "<div id='pulldownad_tag_image_error' class='sub_error'></div>";
		
		echo $html;
	}
	
	protected static function get_video_callback( $video_assets )
	{
		if( ! empty( $video_assets ) )
		{
			$v_assets = unserialize( $video_assets );
			
			$mp4_file 	= "";
			$ogg_file 	= "";
			$webm_file 	= "";
			$flv_file 	= "";
			
			$html 		= "";
			
			if( ! empty( $v_assets) )
			{
				foreach( $v_assets as $asset )
				{

					if( $asset["type"] == "video/mp4" || $asset["type"] == "mp4" )
					{
						if( isset( $asset["file"] ) )
						{
							$mp4_file_comp = explode("/", $asset['file']);
							
							$mp4_file = $mp4_file_comp[(count($mp4_file_comp) - 1)];
								
						}
						else
						{
							$mp4_file = $asset["name"];
						}
					}
					
					if( $asset["type"] == "video/ogg" || $asset["type"] == "ogg" )
					{
						if( isset( $asset["file"] ) )
						{
							$ogg_file_comp = explode("/", $asset['file']);
							
							$ogg_file = $ogg_file_comp[(count($ogg_file_comp) - 1)];
						}
						else
						{
							$ogg_file = $asset["name"];
						}
					}
					
					if( $asset["type"] == "video/webm" || $asset["type"] == "webm" )
					{
						if( isset( $asset["file"] ) )
						{
							$webm_file_comp = explode("/", $asset['file']);
							
							$webm_file = $webm_file_comp[(count($webm_file_comp) - 1)];
						}
						else
						{
							$webm_file = $asset["name"];
						}
					}
					
					if( $asset["type"] == "video/x-flv" || $asset["type"] == "flv" )
					{
						if( isset( $asset["file"] ) )
						{
							$flv_file_comp = explode("/", $asset['file']);
							
							$flv_file = $flv_file_comp[(count($flv_file_comp) - 1)];
						}
						else
						{
							$flv_file = $asset["name"];
						}
					}
					
					if( $asset["type"] == "bgcolor" )
					{
						$bg_color = $asset["hex"];
					}
				}
			}
			$html .= "
				<div id='video_assets'>
					<table>
			";
			
			if( empty( $mp4_file ) )
			{
				$html .= "
				<tr>
					<td style='padding-left: 0;' ><label for='video_mp4'>MP4</label> </td><td> <input type='file' name='video_mp4' /></td>
				</tr>";
			}
			else
			{
				$html .= "
				<tr>
					<td id='mp4filename' style='padding-left: 0;' colspan='2'>" . $mp4_file . " 
					<a href='javascript: changeAsset(\"mp4\", \"" . $mp4_file . "\", \"video\");'>Change</a> &nbsp; 
						<a href='javascript: deleteVideo(\"mp4\", \"video\");'>Delete</a>  
					<input type='hidden' name='has_mp4_file' value='1' /></td>
				</tr>
				";
			}
			
			if( empty( $ogg_file ) )
			{
				$html .= "
				<tr>
					<td style='padding-left: 0;' ><label for='video_ogv'>OGV</label></td><td> <input type='file' name='video_ogv' /></td>
				</tr>
				";
				
			}
			else
			{
				$html .= "
				<tr>
					<td id='oggfilename' style='padding-left: 0;' colspan='2'>" . $ogg_file . "
					<a href='javascript: changeAsset(\"ogg\", \"" . $ogg_file . "\", \"video\");'>Change</a> &nbsp; 
						<a href='javascript: deleteVideo(\"ogg\", \"video\");'>Delete</a> 
						<input type='hidden' name='has_ogv_file' value='1' /> </td>
				</tr>
				";
			}
			
			if( empty( $webm_file ) )
			{
				$html .= "
				<tr>
					<td style='padding-left: 0;'><label for='video_webm'>WebM</label></td><td> <input type='file' name='video_webm' /></td>
				</tr>
				";
			}
			else
			{
				$html .= "
				<tr>
					<td id='webmfilename' style='padding-left: 0;' colspan='2'>" . $webm_file . "
					<a href='javascript: changeAsset(\"webm\", \"" . $webm_file . "\", \"video\");'>Change</a> &nbsp; 
						<a href='javascript: deleteVideo(\"webm\", \"video\");'>Delete</a>  
						<input type='hidden' name='has_webm_file' value='1' /></td>
				</tr>
				";
			}
			
			if( empty( $flv_file ) )
			{
				$html .= "
				<tr>
					<td style='padding-left: 0;'><label for='video_flv'>FLV</label></td><td><input type='file' name='video_flv' /></td>
				</tr>
				";
			}
			else
			{
				$html .= "
				<tr>
					<td id='flvfilename' style='padding-left: 0;' colspan='2'>" . $flv_file . "
					<a href='javascript: changeAsset(\"x-flv\", \"" . $flv_file . "\", \"video\");'>Change</a> &nbsp; 
						<a href='javascript: deleteVideo(\"x-flv\", \"video\");'>Delete</a>  
						<input type='hidden' name='has_flv_file' value='1' /></td>
				</tr>
				";
			}
			
			$html .= "</table>
				</div>
				<br />";
		}
		else
		{
			$html = "<div id='video_assets' >";
			$html .= "<table>
						<tr>
							<td><label for='video_mp4'>MP4</label></td>
							<td><input type='file' name='video_mp4' /></td>
						</tr>
						<tr>
							<td><label for='video_ogv'>OGV</label></td>
							<td><input type='file' name='video_ogv' /></td>
						</tr> 
						<tr>
							<td><label for='video_webm'>WebM</label></td>
							<td><input type='file' name='video_webm' /></td>
						</tr>
						<tr>
							<td><label for='video_flv'>FLV</label></td>
							<td><input type='file' name='video_flv' /></td>
						</tr>
						
					</table>
					</div>";
		}
		
		return $html;
	}

	public function display_plugin_admin_page() 
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad";
	
		include_once( 'views/admin.php' );
	}
	
	public function display_plugin_new_page() 
	{
		include_once( 'views/add.php' );
	}
	
	public function display_plugin_edit_page() 
	{
		global $wpdb;
		
		include_once( 'views/edit.php' );
	}
	
	public function display_plugin_entries_page( )
	{
		global $wpdb;
		
		include_once( 'views/entries.php' );
	}
	
	/**
	 * Render the Hours dropdown for the start and end date for the plugin
	 *
	 * @since    1.0.0
	 */
	public function get_hours( $prefix, $val = 0 )
	{
		$out = "<select name='" . $prefix ."_hour'>";
		
		for( $h = 0 ; $h < 24 ; $h++ )
		{
			if( $h < 10 )
			{
				if( $val == $h )
				{
					$out .= "<option value='0" . $h . "' selected='selected'>0" . $h . "</option>";	
				}
				else
				{
					$out .= "<option value='0" . $h . "'>0" . $h . "</option>";	
				}
			}
			else
			{
				if( $val == $h )
				{
					$out .= "<option value='" . $h . "' selected='selected'>" . $h . "</option>";	
				}
				else
				{
					$out .= "<option avlue='" . $h . "'>" . $h . "</option>";
				}
			}
		}
		
		$out .= "</select>";
		
		return $out;
	}
	
	/**
	 * Render the Minutes dropdown for the start and end date for the plugin
	 *
	 * @since    1.0.0
	 */
	public function get_minutes( $prefix, $val = 0)
	{
		$out = "<select name='" . $prefix ."_minutes'>";
		
		for( $m = 0 ; $m < 60 ; $m++ )
		{
			if( $m < 10 )
			{
				if( $val == $m )
				{
					$out .= "<option value='0" . $m . "' selected='selected'>0" . $m . "</option>";
				}
				else
				{
					$out .= "<option value='0" . $m . "'>0" . $m . "</option>";
				}
				
			}
			else
			{
				if( $val == $m )
				{
					$out .= "<option value='" . $m . "' selected='selected'>" . $m . "</option>";	
				}
				else
				{
					$out .= "<option value='" . $m . "'>" . $m . "</option>";
				}
			}
		}
		
		$out .= "</select>";
		
		return $out;
	}
	
	public function compare_pulldownad_dates( )
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad";
		
		$start_date = $_POST['s_date'] . " " . $_POST['s_time'];
		$end_date   = $_POST['e_date'] . " " . $_POST['e_time'];
		
		$takeover_count = 0;
		
		if( empty( $_POST['id'] ) )
		{
			$pulldownad_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) 
												FROM {$table_name} 
												WHERE (start_date 
														BETWEEN %s AND %s  
												OR end_date
														BETWEEN %s AND %s)
												AND (id <> %d)",
												$start_date,
												$end_date,
												$start_date,
												$end_date
											) );
		}
		else
		{
			$pulldownad_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(id)
												FROM {$table_name}
												WHERE (start_date 
														BETWEEN %s AND %s  
												OR end_date
														BETWEEN %s AND %s)
												AND (id <> %d)",
												$start_date,
												$end_date,
												$start_date,
												$end_date,
												$_POST['id'] 
											 ) );
		}
		
		if( $pulldownad_count > 0 )
		{
			echo 1;
			die;
		}
		else
		{
			echo 0;
			die;
		}
	}
	
	public function new_pulldownad( )
	{
		
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad";
		
		$title_client 	= $_POST['pulldownad_title_client'];
		
		$position 		= $_POST['pulldownad_position'];
		
		$height			= $_POST['pulldownad_height'];
		
		$drop_height	= $_POST['pulldownad_drop_height'];
		
		$start_date 	= $_POST['pulldownad_start_date'];
		
		$start_hour 	= $_POST['pulldownad_start_hour'];
		
		$start_minutes 	= $_POST['pulldownad_start_minutes'];
		
		$end_date		= $_POST['pulldownad_end_date'];
		
		$end_hour		= $_POST['pulldownad_end_hour'];
		
		$end_minutes	= $_POST['pulldownad_end_minutes'];
		
		$link 			= $_POST['pulldownad_link'];
		
		$order			= array( );
		
		$order_str		= "";
		
		$image_tag		= wp_handle_upload($_FILES['pulldownad_tag_image'], array('test_form' => FALSE) );
		
		$video_assets = array( );
		
		$content = array( );
		
		$extra_field_count = 0;
		
		$page_content = $_POST['pulldownad_content'];
			
		$bg_color = $_POST['pulldownad_bgcolor'];
			
		$main_image = "";
			
		$extra_field_count = $_POST['extra_field_count'];
			
		if( ! empty( $_FILES['pulldownad_image']['name'] ) )
		{
			$main_image_tmp = wp_handle_upload( $_FILES['pulldownad_image'], array('test_form' => FALSE) );
			
			$main_image = $main_image_tmp['url'];
		}
			
		$bg_repeat = $_POST['pulldownad_bg_repeat'];
			
		if( isset( $_FILES['video_mp4']['name'] ) && ! empty( $_FILES['video_mp4']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_mp4'], array('test_form' => false) );
		}
			
		if( isset( $_FILES['video_ogv']['name'] ) && ! empty( $_FILES['video_ogv']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_ogv'], array('test_form' => false) );
		}
			
		if( isset( $_FILES['video_webm']['name'] ) && ! empty( $_FILES['video_webm']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_webm'], array('test_form' => false) );
		}
			
		if( isset( $_FILES['video_flv']['name'] ) && ! empty( $_FILES['video_flv']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_flv'], array('test_form' => false) );
		}
		
				
		$order_str = implode("|", $order);
		
		if( isset( $_POST['has_entry_form'] ) )
		{
			$content['content'] 		= $page_content;
			$content['height']			= $height;
			$content['drop_height']		= $drop_height;
			$content['bg_color'] 		= $bg_color;
			$content['link'] 			= $link;
			$content['bg_image']		= array('path' => $main_image, 'repeat' => $bg_repeat );
			$content['videos'] 	 		= serialize( $video_assets );
			$content['has_entry']		= 1;
			$content['form_data']		= serialize( self::createInputFields( $extra_field_count, $_POST ) );
		}
		else
		{
			$content['content'] 		= $page_content;
			$content['height']			= $height;
			$content['drop_height']		= $drop_height;
			$content['bg_color'] 		= $bg_color;
			$content['link'] 			= $link;
			$content['bg_image']		= array('path' => $main_image, 'repeat' => $bg_repeat );
			$content['videos'] 	 		= serialize( $video_assets );
			$content['has_entry']		= 0;
			$content['form_data']		= serialize( array( ) );
		}
	
		$content_serialized = serialize( $content );
		
		$insert = $wpdb->insert(
			$table_name,
			array(
				"title_client" 	=> $title_client,
				"position"		=> $position,
				"content"		=> $content_serialized,
				"start_date"	=> $start_date . " " . $start_hour . ":" . $start_minutes . ":00",
				"end_date"		=> $end_date . " " . $end_hour . ":" . $end_minutes . ":00",
				"tag_image"		=> $image_tag['url']
			),
			array(
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s"
			)
		);
		
		if( $insert == 0 )
		{
			$wpdb->print_error();
		}
		else
		{
			echo $insert;	
		}
		die;

	}
	
	public function edit_pulldownad( )
	{
		global $wpdb;
		
			
				
				
		$table_name = $wpdb->prefix . "pull_down_ad";
		
		$id					= $_POST['id'];
		
		$title_client 		= $_POST['pulldownad_title_client'];
		
		$position 			= $_POST['pulldownad_position'];
		
		$height				= $_POST['pulldownad_height'];
		
		$drop_height		= $_POST['pulldownad_drop_height'];
		
		$start_date 		= $_POST['pulldownad_start_date'];
		
		$start_hour 		= $_POST['pulldownad_start_hour'];
		
		$start_minutes 		= $_POST['pulldownad_start_minutes'];
		
		$end_date			= $_POST['pulldownad_end_date'];
		
		$end_hour			= $_POST['pulldownad_end_hour'];
		
		$end_minutes		= $_POST['pulldownad_end_minutes'];
		
		$link 				= $_POST['pulldownad_link'];
		
		$page_content 		= $_POST['pulldownad_content'];
			
		$bg_color 			= $_POST['pulldownad_bgcolor'];
		
		$bg_repeat 			= $_POST['pulldownad_bg_repeat'];
		
		$extra_field_count 	= 0;
		
		$extra_field_count 	= $_POST['extra_field_count'];
		
		$video_assets 		= array( );
		
		$current_content = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$table_name} WHERE id = %d", $id) );
		
		$current_content = unserialize( $current_content );
		
		$video_assets = unserialize( $current_content['videos'] );
		
		$content 			= array( );
		
		if( isset( $_FILES['pulldownad_image']['name'] ) && ! empty( $_FILES['pulldownad_image']['name'] ) )
		{
			$main_image_tmp = wp_handle_upload( $_FILES['pulldownad_image'], array('test_form' => FALSE) );
			
			$main_image = $main_image_tmp['url'];
		}
		else if( isset( $_POST['pulldownad_image_file'] ) && ! empty( $_POST['pulldownad_image_file'] ) )
		{
			$main_image = $_POST['pulldownad_image_file'];
		}
		else
		{
			$main_image = "";
		}
		
		if( isset( $_FILES['pulldownad_tag_image']['name'] ) && ! empty( $_FILES['pulldownad_tag_image']['name'] ) )
		{
			$image_tag	= wp_handle_upload($_FILES['pulldownad_tag_image'], array('test_form' => FALSE) );
			
			$image_tag = $image_tag['url'];
		}
		else if( isset( $_POST['pulldownad_tag_image_file'] ) && ! empty( $_POST['pulldownad_tag_image_file'] ) )
		{
			$image_tag = $_POST['pulldownad_tag_image_file'];
		}
		else
		{
			$image_tag = "";
		}
		
		if( isset( $_FILES['video_mp4']['name'] ) && ! empty( $_FILES['video_mp4']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_mp4'], array('test_form' => false) );
		}
		
		if( isset( $_FILES['video_ogv']['name'] ) && ! empty( $_FILES['video_ogv']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_ogv'], array('test_form' => false) );
		}
		
		if( isset( $_FILES['video_webm']['name'] ) && ! empty( $_FILES['video_webm']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_webm'], array('test_form' => false) );
		}
			
		if( isset( $_FILES['video_flv']['name'] ) && ! empty( $_FILES['video_flv']['name'] ) )
		{
			$video_assets[] = wp_handle_upload($_FILES['video_flv'], array('test_form' => false) );
		}
		
		
		
		if( isset( $_POST['has_entry_form'] ) )
		{
			$content['content'] 		= $page_content;
			$content['height']			= $height;
			$content['drop_height']		= $drop_height;
			$content['bg_color'] 		= $bg_color;
			$content['link'] 			= $link;
			$content['bg_image']		= array('path' => $main_image, 'repeat' => $bg_repeat );
			$content['videos'] 	 		= serialize( $video_assets );
			$content['has_entry']		= 1;
			$content['form_data']		= serialize( self::createInputFields( $extra_field_count, $_POST ) );
			
			
		}
		else
		{
			$content['content'] 		= $page_content;
			$content['height']			= $height;
			$content['drop_height']		= $drop_height;	
			$content['bg_color'] 		= $bg_color;
			$content['link'] 			= $link;
			$content['bg_image']		= array('path' => $main_image, 'repeat' => $bg_repeat );
			$content['videos'] 	 		= serialize( $video_assets );
			$content['has_entry']		= 0;
			$content['form_data']		= serialize( array( ) );
		}
	
		$content_serialized = serialize( $content );
		
		$update = $wpdb->update(
			$table_name,
			array(
				"title_client" 	=> $title_client,
				"position"		=> $position,
				"content"		=> $content_serialized,
				"start_date"	=> $start_date . " " . $start_hour . ":" . $start_minutes . ":00",
				"end_date"		=> $end_date . " " . $end_hour . ":" . $end_minutes . ":00",
				"tag_image"		=> $image_tag
			),
			array( "id" => $id ),
			array(
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s"
			),
			array( "%d" )
		);
		
		echo $update;
		die;
	}

	protected static function createInputFields( $extra_field_ct, $_POST)
	{
	
		$input_fields = array
		(
			array(
				"field"	=> "First Name",
				"required" => TRUE
			),
			array(
				"field" => "Last Name",
				"required" => TRUE
			),
			array(
				"field" => "Street Address",
				"required" => TRUE
			),
			array(
				"field" => "City",
				"required" => TRUE
			),
			array(
				"field" => "State",
				"required" => TRUE
			),
			array(
				"field" => "Zip",
				"required" => TRUE
			),
			array( 
				"field" => "email",
				"required" => TRUE
			),
			array(
				"field" => "phone",
				"required" => TRUE,
			),
			array(
				"field" => "birthdate",
				"required" => TRUE
			)
		);
		
		if( $extra_field_ct > 0 )
		{
			for( $i = 0 ; $i < $extra_field_ct ; $i++ )
			{
				if( isset( $_POST['ef_type_' . $i] ) )
				{
					$required = 0;
	
					if( isset( $_POST['ef_req_' . $i] ) )
					{
						$required = 1;
					}
				
					switch( $_POST['ef_type_' . $i] )
					{
						case "text":
						
							$input_fields[] = array(
								"field" 	=> $_POST['ef_text_' . $i],
								"required"	=> $required,
								"type"		=> "text"
							);
							
						break;
						case "checkbox":
							
							$checkbox_items = array( );
							
							$ef_value = $_POST['ef_value_' . $i];
							$ef_label = $_POST['ef_value_' . $i];
							
							for( $c = 0 ; $c < count( $ef_label ) ; $c++ )
							{
								$checkbox_items[] = array(
									"name" 	=> $ef_value[$c],
									"label" => $ef_label[$c]
								);
							}
						
							$input_fields[] = array(
								"field" 	=> $_POST['ef_text_' . $i],
								"required"	=> $required,
								"type"		=> "checkbox",
								"inputs"	=> $checkbox_items
							);
						
						break;
						case "radio":
							
							$radio_items = array( );
							
							$ef_value = $_POST['ef_value_' . $i];
							$ef_label = $_POST['ef_value_' . $i];
							
							for( $r = 0 ; $r < count( $ef_label ) ; $r++ )
							{
								$radio_items[] = array(
									"name" 	=> $ef_value[$r],
									"label" => $ef_label[$r]
								);
							}
						
							$input_fields[] = array(
								"field" 	=> $_POST['ef_text_' . $i],
								"required"	=> $required,
								"type"		=> "radio",
								"inputs"	=> $radio_items
							);
						
						break;
						case "select":
						
							$dropdown_items = array( );
						
							$ef_value = $_POST['ef_value_' . $i];
							$ef_label = $_POST['ef_value_' . $i];
							
							for( $d = 0 ; $d < count( $ef_label ) ; $d++ )
							{
								$dropdown_items[] = array(
									"name" 	=> $ef_value[$d],
									"label" => $ef_label[$d]
								);
							}
						
							$input_fields[] = array(
								"field" 	=> $_POST['ef_text_' . $i],
								"required"	=> $required,
								"type"		=> "select",
								"inputs"	=> $dropdown_items
							);
							
						break;
						case "multi_select":
						
							$multiselect_items = array( );
						
							$ef_value = $_POST['ef_value_' . $i];
							$ef_label = $_POST['ef_value_' . $i];
							
							for( $d = 0 ; $d < count( $ef_label ) ; $d++ )
							{
								$multiselect_items[] = array(
									"name" 	=> $ef_value[$d],
									"label" => $ef_label[$d]
								);
							}
						
							$input_fields[] = array(
								"field" 	=> $_POST['ef_text_' . $i],
								"required"	=> $required,
								"type"		=> "multiselect",
								"inputs"	=> $multiselect_items
							);
						
						break;
						case "textarea":
						
							$input_fields[] = array(
								"field" 	=> $_POST['ef_text_' . $i],
								"required"	=> $required,
								"type"		=> "textarea"
							);
							
						break;
					}
				}
			}
		}
		
		return $input_fields;
	}
	
	public function pulldownad_delete_video( )
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad";
		
		$videotype 	= $_GET['vType'];
		$id 		= $_GET['row_id'];
		
		$ad_content = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$table_name} WHERE id = %d", $id ) );
		
		$content = unserialize( $ad_content );

		$video_assets = unserialize( $content['videos'] );
		
		for( $i = 0 ; $i < count( $video_assets ) ; $i++ )
		{
			if( $video_assets[$i]["type"] == "video/" . $videotype )
			{
				unset( $assets[$i] );
			}
		}
		
		$content['videos'] = serialize( $video_assets );
				
		$update = $wpdb->update(
			$table_name,
			array( "content" => serialize( $content ) ),
			array( "id" => $id),
			array( "%s" ),
			array( "%d" )
		);
		
		return $update;
		die;
	}	
	
	public function change_pulldownad_asset( )
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad";
	
		if( isset( $_FILES['imagechange']['name'] ) )
		{
			//Form ID being passed
			$form_id 			= $_POST['id_passed'];
			//Section of the image being replaced - image ( main image ) or parallax
			$section			= $_POST['old_image_section'];
			
			$query = "";
			
			//Section passed was for a solo, main, richmedia ad
			if( $section == "bg_image" )
			{
				//Check if errors uploaded.  If so, throw error
				
				//Retrieve parallax assets from richmedia record.
				$content = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$table_name} WHERE id = %d", $form_id ) );
				
				$filemove = wp_handle_upload($_FILES['imagechange'], array('test_form' => false) );
				
				$content = unserialize( $content );
				
				$content['bg_image']['path'] = $filemove['url'];
				
				$content = serialize( $content );
				
				//Update image name in database
				$update = $wpdb->update(
					$table_name,
					array('content' => $content),
					array('id' => $form_id),
					array('%s'),
					array('%d')
				);
						
				//Return JSON acknowledging image has been replaced.
				//$json = json_encode( array( "errors" => 0,  "message" => "The image has been changed.", "section" => "image" ) );
						
				echo $update; 
				die;
			}
			else if( $section == "tag_image" ) //Section is from the parallax assets
			{
				$new_tag_image = wp_handle_upload($_FILES['imagechange'], array('test_form' => false) );
				
				$update = $wpdb->update(
					$table_name,
					array('tag_image' => $new_tag_image['url']),
					array('id' => $form_id),
					array('%s'),
					array('%d')
				);
					
				echo $update; 
				die;
			}
		}
		else if( isset( $_FILES['videochange']['name'] ) )
		{
			//Form ID being passed
			$form_id 			= $_POST['id_passed'];
			//Type of the old video
			$old_video_type 	= $_POST['vid_type'];
			//Name of the old video
			$old_video_name  	= $_POST['vid_name'];
			
			$video_type			= $_FILES['videochange']['type'];
			
			//Check that the video being uploaded is the same type as the video being replaced.
			if( strtolower( "video/" . $old_video_type ) != strtolower( $video_type ) )
			{
				echo "0,The type of video uploaded does not match the designated type.";
				die;
			}
			
			$newvideo = wp_handle_upload($_FILES['videochange'], array('test_form'=>false) );
		
			//Retrieve video assets from database
			$content = $wpdb->get_var( $wpdb->prepare("SELECT content FROM {$table_name} WHERE id = %d", $form_id ) );
			
			$content = unserialize( $content );
			
			$assets = unserialize( $content['videos'] );
			
			/* 
			* Loop through assets until the one with the correct type is found - 
			* $assets will be a multidimensional array with each of the initial arrays containing an associative
			* array.  The keys of the associative array will be 'type' and 'name'
			*/
			
			$asset_count = 0;
			foreach( $assets as $asset )
			{
				if(  $asset['type'] == $newvideo['type'] )
				{
					$assets[$asset_count] = $newvideo;
					$asset_count++;
				}
			}
			
			//Re-serialize assets
			$content['videos'] = serialize( $assets );
			
			$content = serialize( $content );
		
			//Update Richmedia Ad in Db
			$update = $wpdb->update(
				$table_name,
				array('content' => $content),
				array('id' => $form_id),
				array('%s'),
				array('%d')
			);
			
			echo $update;
			die;
		}
	}
	
	/**
	 * AJAX Callback to get the richmedia ad and send it to the Frontend
	 *
	 * @since    1.0.0
	 */
	public function pulldownad_get_ad( $atts, $content = null )
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad";
		
		$params = shortcode_atts(array(
				"id" => 0,
				"testdate" => date('Y') . "/" . date('d') . "/" . date('m'),
				"testtime" => date('H') . ":" . date('i')
		), $atts);
		
		$query = "";

		if( isset( $params['id'] ) && $params['id'] != 0 )
		{
			$query = "SELECT id, title_client, position, content, tag_image
					  FROM {$table_name}
					  WHERE id = %d";
					  
			$pullDownAdRec = $wpdb->get_row( $wpdb->prepare( $query, $params['id'] ) );
		}	
		else
		{
			list( $year, $day, $month ) = split("/", $params['testdate'] );
			list( $hour, $minute ) = split(":", $params['testtime'] );
		
			$time_string = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":00";
			
			$query = "SELECT id, title_client, position, content, tag_image
					  FROM {$table_name}
					  WHERE start_date <= %s AND end_date >= %s";
					  
			$pullDownAdRec = $wpdb->get_row( $wpdb->prepare( $query, $time_string, $time_string) );
		}
	
		$pulldownad;
		
		if( $pullDownAdRec )
		{
			$content = unserialize( $pullDownAdRec->content );
			
			$written_content 	= $content['content'];
			
			$bg_color			= $content['bg_color'];
			
			$height				= $content['height'];
			
			$drop_height		= $content['drop_height'];
			
			$link				= $content['link'];
			
			$bg_image			= $content['bg_image']['path'];
			
			$bg_image_repeat	= $content['bg_image']['repeat'];
			
			$video_out 			= "";
			
			$has_entry 			= $content['has_entry'];
			
			$has_video			= FALSE;
			
			$form_fields		= array( );
			
			$extra_fields		= unserialize( $content['form_data'] );
			
			
			
			$videos 			= unserialize( $content['videos'] );
			
			if( count( $videos ) > 0 )
			{
				$has_video = TRUE;
			}
			
			foreach( $videos as $video )
			{
				if( empty( $video_out ) )
				{
					$video_out = $video['url'] . "|" . $video['type'];
				} 
				else
				{
					$video_out .= "," . $video['url'] . "|" . $video['type'];
				}
			}
			
			$form_out = "";
			
			if( $has_entry )
			{
				$form_out .= "<form name='pulldownadForm_" . $pullDownAdRec->id . "' action='/wp-admin/admin-ajax.php' type='post' >";
				$form_out .= "<input type='hidden' name='action' value='pulldownad_formentry' />";
				$form_out .= "<input type='hidden' name='id' value='" . $pullDownAdRec->id . "' />";
				
				$form_out .= "<table cellspacing='5' cellpadding='5'>";
				$form_out .= "<tr><td>";
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_first_name_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_first_name'>";
				$form_out .= "First Name";
				$form_out .= "</label><br />";
				$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_first_name' size='35' />";
				$form_out .= "</div>";	
				$form_out .= "<div id='pulldownform_first_name_error' class='sub_error' />";
				$form_out .= "</td><td>";
				
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_street_address_1_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_street_address_1'>";
				$form_out .= "Street Address";
				$form_out .= "</label><br />";
				$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_street_address_1' size='45' />";
				$form_out .= "</div>";
				$form_out .= "<div id='pulldownform_street_address_1_error' class='sub_error'/>";
				$form_out .= "</td>";
				$form_out .= "</tr><tr>";
				$form_out .= "<td>";
	
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_last_name_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_last_name'>";
				$form_out .= "Last Name";
				$form_out .= "</label><br />";
				$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_last_name' size='35' />";
				$form_out .= "</div>";	
				$form_out .= "<div id='pulldownform_last_name_error' class='sub_error'/>";
				$form_out .= "</td><td>";
				
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_street_address_2_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_street_address_2'>";
				$form_out .= "Street Address 2";
				$form_out .= "</label><br />";
				$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_street_address_2' size='45' />";
				$form_out .= "</div>";
				
				$form_out .= "</td>";
				$form_out .= "</tr><tr>";
				$form_out .= "<td>";
								
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_email_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_email'>";
				$form_out .= "E-Mail Address";
				$form_out .= "</label><br />";
				$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_email' size='35' />";
				$form_out .= "</div>";
				$form_out .= "<div id='pulldownform_email_error' class='sub_error'/>";
				$form_out .= "</td><td>";
				
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_city_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_city'>";
				$form_out .= "City";
				$form_out .= "</label><br />";
				$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_city' size='25' />";
				$form_out .= "</div>";
				$form_out .= "<div id='pulldownform_city_error' class='sub_error' />";
				
				$form_out .= "</td>";
				$form_out .= "</tr><tr>";
				$form_out .= "<td>";
				
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_phone_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_phone_ac'>";
				$form_out .= "Phone Number<br />";
				$form_out .= "</label>";
				$form_out .= "(<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_phone_ac' size='3'>)-<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_phone_f3' size='3'>-<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_phone_l4' size='4' />";
				$form_out .= "</div>";
				$form_out .= "<div id='pulldownform_phone_error' class='sub_error' />";
				
				$form_out .= "</td><td>";
				
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_state_container'>";
				$form_out .= "<table><tr>";
				$form_out .= "<td><label for='pulldownform_" . $pullDownAdRec->id . "_first_name'>";
				$form_out .= "State";
				$form_out .= "</label><br />";
				$form_out .= "<select name='pulldownform_" . $pullDownAdRec->id . "_state'>";
				$form_out .= self::getStates( );
				$form_out .= "</select>";
				$form_out .= "</td><td>";
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_zip_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_zip'>";
				$form_out .= "Zip Code";
				$form_out .= "</label><br />";
				$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_zip'>";
				$form_out .= "</div>";
				$form_out .= "<div id='pulldownform_zip_error' class='sub_error' />";
				$form_out .= "</td></tr></table>";
				$form_out .= "</div>";
				
				$form_out .= "</td>";
				$form_out .= "</tr><tr>";
				$form_out .= "<td colspan='2'>";
				
				$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_dob_container'>";
				$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_dob_month'>";
				$form_out .= "Date of Birth<br />";
				$form_out .= "</label>";
				$form_out .= "<select name='pulldownform_" . $pullDownAdRec->id . "_dob_month'>";
				
				for( $m = 1 ; $m < 13 ; $m++ )
				{
					if ( $m < 10 )
					{
						$form_out .= "<option value='0" . $m . "'>0" . $m . "</option>";
					}
					else
					{
						$form_out .= "<option value='" . $m . "'>" . $m . "</option>";
					}
				}
				
				$form_out .= "</select>";
				$form_out .= "&nbsp;/&nbsp;";
				$form_out .= "<select name='pulldownform_" . $pullDownAdRec->id . "_dob_day'>";
				
				for( $d = 1 ; $d < 32 ; $d++ )
				{
					if( $d < 10 )
					{
						$form_out .= "<option value='0" . $d . "'>0" . $d . "</option>";
					}
					else
					{
						$form_out .= "<option value='" . $d . "'>" . $d . "</option>";
					}
					
				}
				$form_out .= "</select>";
				$form_out .= "&nbsp;/&nbsp;";
				$form_out .= "<select name='pulldownform_" . $pullDownAdRec->id . "_dob_year'>";
				
				for( $y = (int)date('Y') ; $y >= 1900 ; $y-- )
				{
					$form_out .= "<option value='" . $y . "'>" . $y . "</option>";
				}
				
				$form_out .= "</select>";
				$form_out .= "</div>";
				
				$form_out .= "</td>";
				$form_out .= "</tr>";

			
				if( count( $extra_fields ) > 0) 
				{
					$form_field_count = 0;
				
					foreach( $extra_fields as $field )
					{	
						if( isset( $field['type'] ) )
						{
							if( $form_field_count == 0 )
							{
								$form_out .= "<tr><td>";
							}
							else
							{
								if( $form_field_count%2 == 0 )
								{
									$form_out .= "</td></tr><tr>";
								}
								else
								{
									$form_out .= "</td><td>";
								}
							}
						
							$form_out .= "<div id='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "_container'>";		
					
							$form_out .= "<label for='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "'>";
							$form_out .= $field["field"] . "<br />";
							$form_out .= "</label>";
						
							if( $field['type'] != "text" )
							{
								if( $field["type"] == "select" )
								{
									$form_out .= "<select name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "'>"; 		
									$form_out .= "<option value=''>Please Select One...</option>";
								}
								else if( $field["type"] == "multi_select" )
								{
									$form_out .= "<select multiple name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "'>"; 		
								}
							
								if( isset( $field['inputs'] ) )
								{
									foreach( $field['inputs'] as $input )
									{
										switch( $field["type"] )
										{
											case "radio":
												$form_out .= $input["label"] . "<input type='radio' name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "' value='" . $input["name"] . "' /> &nbsp;";	
											break;
											case "checkbox":
												$form_out .= $input["label"] . "<input type='checkbox' name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "' value='" + $input["name"] . "' /> &nbsp;";
											break;
											case "select":
											case "multi_select":
												$form_out .= "<option value='" . $input["name"] . "'>" . $input["label"] . "</option>";
											break;	
										}
									}														
								}
								
								if( $field["type"] == "select" || $field["type"] == "multi_select" )
								{
									$form_out .= "</select>"; 		
								}
								
								
							}
							else
							{
								$form_out .= "<input type='text' name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "' />";
							}
							
							$form_out .= "</div>";		
							
							$form_out .= "<div id='pulldownform_ef_" . $form_field_count . "_error' class='sub_error'></div>";				
							
							$form_out .= "<input type='hidden' 	name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "_label' value='" . $field["field"] . "' />";
							$form_out .= "<input type='hidden' name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "_type' value='" . $field["type"] . "' />";
							
							if( $field['required'] == 1 )
							{
								$form_out .= "<input type='hidden' name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "_required' value='1' />";	
							}
							else
							{
								$form_out .= "<input type='hidden' name='pulldownform_" . $pullDownAdRec->id . "_ef_" . $form_field_count . "_required' value='0' />";
							}
							
							$form_field_count++;
						}
					}
					
					if( $form_field_count%2 == 0 )
					{
						$form_out .= "</td></tr></table>";
					}
					else
					{
						$form_out .= "</td><td></td></tr></table>";
					}
				}
				else
				{
					$form_out .= "</table>";
				}
				
				$form_out .= "<input type='hidden' name='ef_count' value='" . $form_field_count . "' />";
				$form_out .= "<div id='button_progress'>";
				$form_out .= "	<input type='submit' name='sub_pulldownform' value='Submit' id='sub_pulldownform' />";
				$form_out .= "	<img src='/wp-content/plugins/pulldown-ad/assets/ajax-loader.gif' id='spinner' style='display:none;'/>";
				$form_out .= "	<div id='progress_verbiage'></div>";
				$form_out .= "</div>";
				$form_out .= "</form>";
				$form_out .= "</div>";
				
				
				$written_content = str_replace("<__FORM__>", $form_out, $written_content);
			}
			
			if( ! empty( $video_out ) )
			{
				$written_content = str_replace("<__VIDEO__>", "<div id='pulldownad_video'></div>", $written_content);
			}
			
			$pulldownad = array( "pulldownad" => TRUE, "id" => $pullDownAdRec->id, "name" => $pullDownAdRec->title_client, "img" => $bg_image, "img_repeat" => $bg_image_repeat, "height" => $height, "drop_height" => $drop_height, "link" => $link, "content" => stripslashes( $written_content ), "has_video" => $has_video, "video_assets" => $video_out, "tag_image" => $pullDownAdRec->tag_image, "position" => $pullDownAdRec->position, "has_entry" => $has_entry );
		}
		else
		{
			$pulldownad = array( "pulldownad" => FALSE );		
		}

		
		$json =  json_encode( $pulldownad );
		
		echo "<script type='text/javascript'>
				var pulldownad_data = " . $json . ";\n
				</script>";
	
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery', 'jqueryForms' ), $this->version );
		
	
	}
	
	public function pulldownad_formentry( )
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad_entries";
	
		$id = $_POST['id'];
		
		$first_name = $_POST['pulldownform_' . $id . '_first_name'];
		$last_name 	= $_POST['pulldownform_' . $id . '_last_name'];
		$email 		= $_POST['pulldownform_' . $id . '_email'];
		$address_1	= $_POST['pulldownform_' . $id . '_street_address_1'];
		$address_2  = $_POST['pulldownform_' . $id . '_street_address_2'];
		$city		= $_POST['pulldownform_' . $id . '_city'];
		$state 		= $_POST['pulldownform_' . $id . '_state'];
		$zip		= $_POST['pulldownform_' . $id . '_zip'];
		$phone		= "(" . $_POST['pulldownform_' . $id . '_phone_ac'] . ")" . $_POST['pulldownform_' . $id . '_phone_f3'] . "-" . $_POST['pulldownform_' . $id . '_phone_l4'];
		$dob		= $_POST['pulldownform_' . $id . '_dob_year'] . "-" . $_POST['pulldownform_' . $id . '_dob_month'] . "-" . $_POST['pulldownform_' . $id . '_dob_day'];
		
		$ef_count   = $_POST['ef_count'];
		
		$extra_fields = array( );
		
		for( $i = 0 ; $i < $ef_count ; $i++ )
		{
			
			$extra_fields[$_POST['pulldownform_' . $id . '_ef_' . $i . '_label']] = array(
				"value" => $_POST['pulldownform_' . $id . '_ef_' . $i],
				"type"  => $_POST['pulldownform_' . $id . '_ef_' . $i . '_type']
			);
			
		}
		
		$insert_entry = $wpdb->insert(
			$table_name,
			array(
				"pulldown_ad_id" 	=> $id,
				"first_name" 		=> $first_name,
				"last_name"			=> $last_name,
				"street_address"	=> $address_1,
				"street_address_2"	=> $address_2,
				"city"				=> $city,
				"state"				=> $state,
				"zip"				=> $zip,
				"email_address"		=> $email,
				"phone"				=> $phone,
				"dob"				=> $dob,
				"extra_fields"		=> serialize( $extra_fields )
			),
			array(
				"%d",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s",
				"%s"
			)
		);
		
		if( $insert_entry == 0 )
		{
			$wpdb->print_error();
		}
		else
		{
			echo $insert_entry;	
		}
		die;

	}
	
	public function pulldownad_get_template( )
	{
		$template_file = sanitize_text_field( $_GET['file'] ); 
		
		$p_info = pathinfo(__FILE__);
		
		$content = file_get_contents($p_info['dirname'] . "/assets/templates/" . $template_file);
		
		echo $content;
		die;
		
	}
	
	protected static function getStates( )
	{
		$states = "";
		
		$states .= "<option value='AL'>AL</option>";
		$states .= "<option value='AK'>AK</option>";
		$states .= "<option value='AZ'>AZ</option>";
		$states .= "<option value='AR'>AR</option>";
		$states .= "<option value='CA'>CA</option>";
		$states .= "<option value='CO'>CO</option>";
		$states .= "<option value='CT'>CT</option>";
		$states .= "<option value='DE'>DE</option>";
		$states .= "<option value='DC'>DC</option>";
		$states .= "<option value='FL'>FL</option>";
		$states .= "<option value='GA'>GA</option>";
		$states .= "<option value='HI'>HI</option>";
		$states .= "<option value='ID'>ID</option>";
		$states .= "<option value='IL'>IL</option>";
		$states .= "<option value='IN'>IN</option>";
		$states .= "<option value='IA'>IA</option>";
		$states .= "<option value='KS'>KS</option>";
		$states .= "<option value='KY'>KY</option>";
		$states .= "<option value='LA'>LA</option>";
		$states .= "<option value='ME'>ME</option>";
		$states .= "<option value='MD'>MD</option>";
		$states .= "<option value='MA'>MA</option>";
		$states .= "<option value='MI'>MI</option>";
		$states .= "<option value='MN'>MN</option>";
		$states .= "<option value='MS'>MS</option>";
		$states .= "<option value='MO'>MO</option>";
		$states .= "<option value='MT'>MT</option>";
		$states .= "<option value='NE'>NE</option>";
		$states .= "<option value='NV'>NV</option>";
		$states .= "<option value='NH'>NH</option>";
		$states .= "<option value='NJ'>NJ</option>";
		$states .= "<option value='NM'>NM</option>";
		$states .= "<option value='NY'>NY</option>";
		$states .= "<option value='NC'>NC</option>";
		$states .= "<option value='ND'>ND</option>";
		$states .= "<option value='OH'>OH</option>";
		$states .= "<option value='OK'>OK</option>";
		$states .= "<option value='OR'>OR</option>";
		$states .= "<option value='PA'>PA</option>";
		$states .= "<option value='RI'>RI</option>";
		$states .= "<option value='SC'>SC</option>";
		$states .= "<option value='SD'>SD</option>";
		$states .= "<option value='TN'>TN</option>";
		$states .= "<option value='TX'>TX</option>";
		$states .= "<option value='UT'>UT</option>";
		$states .= "<option value='VT'>VT</option>";
		$states .= "<option value='VA'>VA</option>";
		$states .= "<option value='WA'>WA</option>";
		$states .= "<option value='WV'>WV</option>";
		$states .= "<option value='WI'>WI</option>";
		$states .= "<option value='WY'>WY</option>";
		
		return $states;
	}
	
	public function pulldownad_delete_ad( )
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "pull_down_ad";;
		
		$id = $_GET['id'];
		
		$wpdb->delete($table_name, array('id' => $id), array('%d') );
		
		die;
		
	}
}
