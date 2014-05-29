<?php
/**
 * Go – Responsive Portfolio for WP
 *
 * @package   Go – Responsive Portfolio for WP
 * @author    Granth <granthweb@gmail.com>
 * @link      http://granthweb.com
 * @copyright 2013 Granth
 */

/**
 * Plugin main class
 *
 * @package   Go - Portfolio
 * @author    Granth <granthweb@gmail.com>
 */
 
class GW_Go_Portfolio {

	protected static $plugin_version = '1.0.0';
	protected $plugin_slug = 'go-portfolio';
	protected static $plugin_prefix = 'gw_go_portfolio';	
	protected static $instance = null;
	protected $screen_hooks = null;


	/**
	 * Initialize the plugin
	 */
	
	private function __construct() {

		/* Set the constants */
		add_action( 'init', array( $this, 'define_constants' ) );

		/* Load plugin text domain */
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		
		/* Load the functions files */
		add_action( 'init', array( $this, 'load_includes' ) );	
		
		/* Register post types */
		add_action( 'init',  array( $this, 'register_custom_post_types' ) );
		
		/* Meta boxes */
		add_action( 'init',  array( $this, 'create_meta_box' ) );
		
		/* Admin notices */
		add_action( 'admin_notices', array( $this, 'print_admin_notices' ) );
		
		/* Add the options page and menu item */
		add_action( 'admin_menu', array( $this, 'register_menu_pages' ) );

		/* Load admin styles and js */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		/* Load public styles and js */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Shortcode */
		add_shortcode( 'go_portfolio', array( $this, 'go_portfolio_shortcode' ) );

		/* Ajax hooks */
		add_action( 'wp_ajax_nopriv_go_portfolio_plugin_menu_page', array( $this, 'ajax_nopriv' ) );
		add_action( 'wp_ajax_go_portfolio_plugin_menu_page', array( $this, 'plugin_menu_page' ) );
		add_action( 'wp_ajax_nopriv_go_portfolio_reset_template_style', array( $this, 'ajax_nopriv' ) );
		add_action( 'wp_ajax_go_portfolio_reset_template_style', array( $this, 'reset_template_style' ) );						
	}


	/**
	 * Return an instance of this class
	 */
	 
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	

	/**
	 * Fired when the plugin is activated 
	 */
	 
	public static function activate( $network_wide ) {
		
		/* Load template files and save to db */
		$templates = get_option( self::$plugin_prefix . '_templates' );
		if ( !$templates ) {
			$templates = self::load_templates();
			if ( $templates ) { update_option ( self::$plugin_prefix . '_templates', $templates ); }
		}

		/* Load style files and save to db */
		$styles = get_option( self::$plugin_prefix . '_styles' );
		if ( !$styles ) {
			$styles = self::load_styles();
			if ( $styles ) { update_option ( self::$plugin_prefix . '_styles', $styles ); }	
		}
		
		/* Create general settings db data with default values */
		$general_settings = get_option( self::$plugin_prefix . '_general_settings' );
		if ( !$general_settings ) {
			
			/* Set default values */
			$general_settings['responsivity']=1;
			$general_settings['colw-min']='130px';
			$general_settings['colw-max']='';
			$general_settings['size1-min']='768px';
			$general_settings['size1-max']='959px';
			$general_settings['size2-min']='480px';
			$general_settings['size2-max']='767px';
			$general_settings['size3-min']='';
			$general_settings['size3-max']='479px';
			$general_settings['max-width']='400px';
			update_option( self::$plugin_prefix . '_general_settings', $general_settings );
		}
				
		/* Save version info to db and generate static css file */
		$saved_version = get_option( self::$plugin_prefix . '_version' );
		if ( $saved_version ) {
			if ( version_compare( $saved_version, self::$plugin_version, "!=" ) ) {
				update_option ( self::$plugin_prefix . '_version', self::$plugin_version );
				self::generate_styles();
			}
		} else {
			update_option ( self::$plugin_prefix . '_version', self::$plugin_version );
			self::generate_styles();
		}			
		
		/* Update notices notices */
		if ( isset( $notices ) ) { self::update_admin_notices ( $notices ); }	
		
		/* Flush rewrite rules */
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
				
	}
	
	
	/**
	 * Fired when the plugin is deactivated 
	 */
	 
	public static function deactivate() {

	}	


	/**
	 * Fired when the plugin is uninstalled
	 */
	 
	public static function uninstall( $network_wide ) {

		/* Delete db data */
		delete_option( self::$plugin_prefix . '_general_settings' );
		delete_option( self::$plugin_prefix . '_cpts' );
		delete_option( self::$plugin_prefix . '_cpts_hash' );		
		delete_option( self::$plugin_prefix . '_portfolios' );
		delete_option( self::$plugin_prefix . '_templates' );				
		delete_option( self::$plugin_prefix . '_styles' );
		delete_option( self::$plugin_prefix . '_version' );	
		delete_option( self::$plugin_prefix . '_notices' );								

		/* Flush rewrite rules */
		global $wp_rewrite;
		$wp_rewrite->flush_rules();

	}


	/**
	 * Define constants
	 */
	 
	public function define_constants() {

		/* Set constant path to the plugin directory */
		define( 'GW_GO_PORTFOLIO_DIR', plugin_dir_path( __FILE__ ) );

		/* Set the constant path to the plugin directory URI */
		define( 'GW_GO_PORTFOLIO_URI', plugin_dir_url( __FILE__ ) );

		/* Set the constant path to the includes directory */
		define( 'GW_GO_PORTFOLIO_INCLUDES', GW_GO_PORTFOLIO_DIR . trailingslashit( 'includes' ) );
		
	}


	/**
	 * Loads the initial files needed by the plugin
	 */
	 
	public function load_includes() {
		
		require_once( GW_GO_PORTFOLIO_INCLUDES . 'functions.php' );
		require_once( GW_GO_PORTFOLIO_INCLUDES . 'class_gw_metabox.php' );

	}
	

	/**
	 * Load the plugin text domain for translation
	 */
	 
	public function load_plugin_textdomain() {
		
		load_plugin_textdomain( 'go_portfolio_textdomain', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	
	}


	/**
	 * Register and enqueue admin styles
	 */
	 
	public function enqueue_admin_styles() {

		if ( ! isset( $this->screen_hooks ) ) { return; }

		$screen = get_current_screen();
		
		if ( in_array( $screen->id, $this->screen_hooks ) ) {
			
			global $wp_version;
			
			/* Load colorpicker with fallback for old versions */
			if ( version_compare( $wp_version, 3.5, ">=" ) ) {				
				wp_enqueue_style( 'wp-color-picker' );				
			} else {
				wp_enqueue_style( 'farbtastic' );					
			}

			/* Load plugin styles */
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', GW_GO_PORTFOLIO_URI . 'admin/css/go_portfolio_admin_styles.css', array(), self::$plugin_version );
		}
	}


	/**
	 * Register and enqueue admin js
	 */
	 
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->screen_hooks ) ) { return; }

		$screen = get_current_screen();
		
		if ( in_array( $screen->id, $this->screen_hooks ) ) {
    		wp_enqueue_script( $this->plugin_slug .'-admin-scripts', GW_GO_PORTFOLIO_URI . 'admin/js/go_portfolio_admin_scripts.js', array( 'jquery', 'wp-color-picker' ), self::$plugin_version );
			wp_enqueue_script( 'thickbox' );
		}
	}


	/**
	 * Register and enqueue public styles
	 */
	 
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug .'-magnific-popup-styles', GW_GO_PORTFOLIO_URI . 'assets/plugins/magnific-popup/magnific-popup.css', array(), self::$plugin_version );
		wp_enqueue_style( $this->plugin_slug .'-styles', GW_GO_PORTFOLIO_URI . 'assets/css/go_portfolio_styles.css', array(), self::$plugin_version );
	}


	/**
	 * Register and enqueues public js
	 */
	 
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-magnific-popup-script', plugins_url( 'assets/plugins/magnific-popup/jquery.magnific-popup.min.js', __FILE__ ), array( 'jquery' ), self::$plugin_version );
		wp_enqueue_script( $this->plugin_slug . '-isotope-script', plugins_url( 'assets/plugins/jquery.isotope.min.js', __FILE__ ), array( 'jquery' ), self::$plugin_version );
		wp_enqueue_script( $this->plugin_slug . '-caroufredsel-script', plugins_url( 'assets/plugins/jquery.carouFredSel-6.2.1-packed.js', __FILE__ ), array( 'jquery' ), self::$plugin_version );
		wp_enqueue_script( $this->plugin_slug . '-script', plugins_url( 'assets/js/go_portfolio_scripts.js', __FILE__ ), array( 'jquery' ), self::$plugin_version );
	}


	/**
	 * Register the administration menus for this plugin
	 */
	 
	public function register_menu_pages() {

		/* Main menu page */
		$this->screen_hooks[] = add_menu_page( 
			__( 'Go - Portfolio', 'go_portfolio_textdomain' ),
			__( 'Go Portfolio', 'go_portfolio_textdomain' ), 
			'manage_options', 
			$this->plugin_slug, 
			array( $this, 'plugin_menu_page' ), 
			plugin_dir_url( __FILE__ ) . 'admin/images/icon_wp_nav.png'
		);
		
		/* Submenu page - Custom Post Types */
		$this->screen_hooks[] = add_submenu_page( 
			$this->plugin_slug,
			__( 'Custom Post Types', 'go_portfolio_textdomain' ) . ' | ' . __( 'Go - Portfolio', 'go_portfolio_textdomain' ),
			__( 'Custom Post Types', 'go_portfolio_textdomain' ),
			'manage_options',
			$this->plugin_slug . '-custom-post-types', 
			array( $this, 'plugin_submenu_page_ctps' )
		);

		/* Submenu page - General Settings */
		$this->screen_hooks[] = add_submenu_page(
			$this->plugin_slug,
			__( 'General Settings', 'go_portfolio_textdomain' ) . ' | ' . __( 'Go - Portfolio', 'go_portfolio_textdomain' ),
			__( 'General Settings', 'go_portfolio_textdomain' ),
			'manage_options',
			$this->plugin_slug . '-settings',
			array( $this, 'plugin_submenu_page_general_settings' )
		);
		
		/* Submenu page - Template & Style Editor */
		$this->screen_hooks[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Template & Style Editor', 'go_portfolio_textdomain' ) . ' | ' . __( 'Go - Portfolio', 'go_portfolio_textdomain' ),
			__( 'Template & Style Editor', 'go_portfolio_textdomain' ),
			'manage_options',
			$this->plugin_slug . '-editor',
			array( $this, 'plugin_submenu_page_editor' )
		);	

		/* Submenu page - Import & Export */
		$this->screen_hooks[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Import & Export', 'go_portfolio_textdomain' ) . ' | ' . __( 'Go - Portfolio', 'go_portfolio_textdomain' ),
			__( 'Import & Export', 'go_portfolio_textdomain' ),
			'manage_options',
			$this->plugin_slug . '-import-export',
			array( $this, 'plugin_submenu_page_import_export' )
		);		

	}


	/**
	 * Main menu page
	 */
	 
	public function plugin_menu_page() {
		include_once( GW_GO_PORTFOLIO_INCLUDES. 'menu_page.php' );
	}

	
	/**
	 * Submenu page for Custom Post Types
	 */
	 
	public function plugin_submenu_page_ctps() {
		include_once( GW_GO_PORTFOLIO_INCLUDES. 'submenu_page_ctps.php' );
	}

	
	/**
	 * Submenu page for General settings
	 */
	
	public function plugin_submenu_page_general_settings() {
		include_once( GW_GO_PORTFOLIO_INCLUDES. 'submenu_page_general_settings.php' );
	}

	
	/**
	 * Submenu page for Template & Style Editor
	 */
	
	public function plugin_submenu_page_editor() {
		include_once( GW_GO_PORTFOLIO_INCLUDES. 'submenu_page_editor.php' );
	}	


	/**
	 * Submenu page for Import & Export
	 */
	
	public function plugin_submenu_page_import_export() {
		include_once( GW_GO_PORTFOLIO_INCLUDES. 'submenu_page_import_export.php' );
	}


	/**
	 * Print admin notices
	 */
	 	
	public function print_admin_notices() {

		$new_current_notices = $current_notices = get_option( self::$plugin_prefix . '_notices', array() ); 
		if ( $current_notices && !empty ( $current_notices ) ) {
			foreach ( $current_notices as $nkey => $current_notice ) {
				$output='<div class="' . ( isset( $current_notice['success'] ) && $current_notice['success'] == true ? 'updated' : 'error' ) . '">';
				$output.='<p>' . ( isset( $current_notice['message'] ) ? $current_notice['message'] : '' ) . '</p>';
				$output.='</div>';
				echo $output;
				if ( isset( $current_notice['permanent'] ) && $current_notice['permanent'] == false ) {
					unset( $new_current_notices[$nkey] );
				}
			}	
		}
		
		if ( $new_current_notices != $current_notices ) {
			update_option ( self::$plugin_prefix . '_notices', $new_current_notices );  
		}
	}	


	/**
	 * Update admin notices
	 */
	 
	public static function update_admin_notices( $notices = array() ) {

		if ( $notices && is_array( $notices ) && !empty( $notices ) ) {
			$current_notices = get_option( self::$plugin_prefix . '_notices', array() ); 
			$new_current_notices = array_merge( $notices, $current_notices );
			if ( $new_current_notices != $current_notices ) {
				update_option ( self::$plugin_prefix . '_notices', $new_current_notices );  
			}
		}
		
	}


	/**
	 * Generate static css file from file & db data
	 */
	 
	public static function generate_styles() {

		ob_start();
		$css_file = plugin_dir_path( __FILE__ ) . 'assets/css/go_portfolio_dynamic_styles.php';
		$css_file_exists = is_file( $css_file );
		
		if ( !$css_file_exists ) {
			$notices[] = array ( 
				'success' => false,
				'permanent' => false,
				'message' => sprintf( __( 'The "%1$s" file doesn\' t exist.', 'go_portfolio_textdomain' ), $css_file )
			);
			if ( isset( $notices ) ) { self::update_admin_notices ( $notices ); }	
			return false;
		} 
	
		require_once( $css_file );
		$file_data = ob_get_clean();
		$write_success = @file_put_contents( plugin_dir_path( __FILE__ ) . 'assets/css/go_portfolio_styles.css', $file_data );
		if ( $write_success === false ) {
			$notices[] = array ( 
				'success' => false,
				'permanent' => false,
				'message' => __( 'The "go_portfolio_styles.css" file couldn\'t be created in "assets/css" folder lack of write permission. <strong>Please set this folder\'s chmod to 777 and activate the plugin again.</strong>', 'go_portfolio_textdomain' )
			);
		}
		
		if ( isset( $notices ) ) { self::update_admin_notices ( $notices ); }	
		
	}	


	/**
	 * Load templates from files
	 */
	
	public static function load_templates( $template_file=null ) {
		
		/* Get param if set - read one certain json file or all files */
		$template_file = $template_file ? $template_file : '*.json';

		/* Read template files */
		$directory = plugin_dir_path( __FILE__ ) . 'templates/templates/';
		
		if ( is_dir( $directory ) ) {
			foreach ( glob( $directory . $template_file ) as $filename ) {
				$json_data = json_decode( file_get_contents( $filename ) ) ? json_decode( file_get_contents( $filename ) ) : null;
				if ( $json_data ) {
					$templates[$json_data->id] = array (
						'name' => $json_data->name,
						'description' => $json_data->name,
						'json_file'	=> basename( $filename ),
						'tpl_file' => $json_data->tpl_file
					);
				}

				if ( file_exists( $directory . $json_data->tpl_file ) && is_file( $directory . $json_data->tpl_file ) ) {
					$data = file_get_contents ( $directory . $json_data->tpl_file );
					$templates[$json_data->id]['data'] = $data;
				};				
			}
		}
		
		return isset( $templates ) ? $templates : null;
	}


	/**
	 * Load styles from files
	 */
	
	public static function load_styles( $style_file=null ) {
		
		/* Get param if set - read one certain json file or all files */
		$style_file = $style_file ? $style_file : '*.json';

		/* Read style files */
		$directory = plugin_dir_path( __FILE__ ) . 'templates/styles/';

		if ( is_dir( $directory ) ) {
			foreach ( glob( $directory . $style_file ) as $filename ) {
				$json_data = json_decode( file_get_contents( $filename ) ) ? json_decode( file_get_contents( $filename ) ) : null;
				if ( $json_data ) {
					$styles[$json_data->id] = array (
						'name' => $json_data->name,
						'description' => $json_data->name,
						'json_file'	=> basename( $filename ),
						'css_file' => $json_data->css_file,
						'class' => $json_data->class
					);

					if ( file_exists( $directory . $json_data->css_file ) && is_file( $directory . $json_data->css_file ) ) {
						$data = file_get_contents ( $directory . $json_data->css_file );
						$styles[$json_data->id]['data'] = $data;
					};		
					
					if ( isset($json_data->effects) && is_array( $json_data->effects ) ) {
						foreach ( $json_data->effects as $effect ) {
							$styles[$json_data->id]['effects'][$effect->id] = $effect->name;
						}
					}
				}
			}
		}
		
		return isset( $styles ) ? $styles : null;		
	}


	/**
	 * General AJAX callback function for users that are not logged in
	 */
	 
	public function ajax_nopriv() {
		die ( __( 'Oops, authorized persons only!', 'go_portfolio_textdomain' ) );
	}


	/**
	 * Reset a template or a style via AJAX
	 */
	 
	public function reset_template_style() {
		
		/* Reset a template */
		$template = isset( $_GET['template'] ) ? $_GET['template'] : null;
		if ( $template ) {		
			$templates = get_option( self::$plugin_prefix . '_templates' );
			if ( isset( $templates[$template] ) ) {
				print_r( $templates[$template]['data'] );
				exit;
			}
		}
		
		/* Reset a style */
		$style = isset( $_GET['style'] ) ? $_GET['style'] : null;
		if ( $style ) {		
			$styles = get_option( self::$plugin_prefix . '_styles' );
			if ( isset( $styles[$style] ) ) {
				print_r( $styles[$style]['data'] );
				exit;
			}
		}		
		exit;
	}


	/**
	 * Register custom post types
	 */
	 
	public function register_custom_post_types() {

		/* Get custom post types from db */
		$custom_post_types = get_option( self::$plugin_prefix . '_cpts' );
		$cpts_hash = get_option( self::$plugin_prefix . '_cpts_hash' );
		$new_cpts_hash = '';
	
		/* Register cpts & custom taxonomy if enabled */
		if ( function_exists( 'register_post_type' ) && function_exists( 'register_taxonomy' ) ) { 
			if ( isset( $custom_post_types ) && !empty( $custom_post_types ) ) {
				foreach ( $custom_post_types as $custom_post_type ) {
	
					$cpt_labels = array(
						'name' => $custom_post_type['name'],
						'singular_name' => $custom_post_type['singular_name'],
						'add_new' => __( 'Add New', 'go_portfolio_textdomain' ),
						'add_new_item' => sprintf( __( 'Add New %s', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'edit_item' => sprintf( __( 'Edit %s', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'new_item' => sprintf( __( 'New %s', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'all_items' => sprintf( __( 'All %s', 'go_portfolio_textdomain' ), $custom_post_type['name'] ),
						'view_item' => sprintf( __( 'View %s', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'search_items' => sprintf( __( 'Search %s', 'go_portfolio_textdomain' ), $custom_post_type['name'] ),
						'not_found' =>  sprintf( __( 'No %s found', 'go_portfolio_textdomain' ), $custom_post_type['name'] ),
						'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'go_portfolio_textdomain' ), $custom_post_type['name'] ), 
						'parent_item_colon' => '',
						'menu_name' => $custom_post_type['name']
					  );
					
					$cpt_args = array(
						'labels' 			=> $cpt_labels,
						'hierarchical'      => false,
						'public' 			=> true,
						'has_archive' 		=> true,
						'supports' 			=> array( 'title', 'editor', 'thumbnail', 'custom-fields','comments','page-attributes', 'excerpt' )
					);
				
					$ctax_cat_labels = array(
						'name'              => sprintf( __( '%s Categories', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'singular_name' 	=> sprintf( __( '%s Category', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'search_items'      => sprintf( __( 'Search %s Categories', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'all_items'         => sprintf( __( 'All %s Categories', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'parent_item'       => sprintf( __( 'Parent %s Category', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'edit_item'         => sprintf( __( 'Edit %s Category', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'update_item'       => sprintf( __( 'Update %s Category', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'add_new_item'      => sprintf( __( 'Add New %s Category', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'new_item_name'     => sprintf( __( 'New %s Category Name', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'menu_name'         => sprintf( __( '%s Category', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] )
					);
					
					$ctax_cat_args = array(
						'labels' 			=> $ctax_cat_labels,
						'hierarchical'      => true,
						'public' 			=> true,
						'query_var'         => true,
						'update_count_callback' => '_update_post_term_count'
					);
					
					$ctax_tag_labels = array(
						'name'              => sprintf( __( '%s Tags', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'singular_name' 	=> sprintf( __( '%s Tag', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'search_items'      => sprintf( __( 'Search %s Tags', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'all_items'         => sprintf( __( 'All %s Tags', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'parent_item'       => sprintf( __( 'Parent %s Tag', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'parent_item_colon' => sprintf( __( 'Parent %s Tag:', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'edit_item'         => sprintf( __( 'Edit %s Tag', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'update_item'       => sprintf( __( 'Update %s Tag', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'add_new_item'      => sprintf( __( 'Add New %s Tag', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'new_item_name'     => sprintf( __( 'New %s Tag Name', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] ),
						'menu_name'         => sprintf( __( '%s Tag', 'go_portfolio_textdomain' ), $custom_post_type['singular_name'] )
					);
					
					$ctax_tag_args = array(
						'labels' 			=> $ctax_tag_labels,
						'hierarchical'      => false,
						'public' 			=> true,
						'query_var'         => true,
						'update_count_callback' => '_update_post_term_count'
					);				
									
					if ( isset( $custom_post_type['enabled'] ) ) { 
						register_post_type( $custom_post_type['slug'], $cpt_args );
						
						/* Check if taxonomy is already registered */
						$all_tax = get_taxonomies(); 
						if ( isset( $all_tax ) && is_array( $all_tax ) ) {
							
							/* Register category */
							if ( !in_array( $custom_post_type['slug'] . '-cat', $all_tax ) ) {
								register_taxonomy( $custom_post_type['slug'] . '-cat', array( $custom_post_type['slug'] ), $ctax_cat_args );
							}
	
							/* Register tag */
							if ( !in_array( $custom_post_type['slug'] . '-tag', $all_tax ) ) {
								register_taxonomy( $custom_post_type['slug'] . '-tag',  array( $custom_post_type['slug'] ), $ctax_tag_args );
							}					
						}
						
						add_filter( 'manage_edit-'.$custom_post_type['slug'].'_columns', array ( $this, 'cpt_edit_columns' ) );
						add_action( 'manage_'.$custom_post_type['slug'].'_posts_custom_column',  array ( $this, 'cpt_custom_columns' ) );
	
					}
	
					/* Create hash from slugs */
					$new_cpts_hash .= $custom_post_type['slug'];
				}
				
				/* Do flush rewrite if cpts has benn changed */
				$new_cpts_hash = md5( $new_cpts_hash );
				
				if ( !$cpts_hash || $cpts_hash != $new_cpts_hash ) {
					update_option( self::$plugin_prefix . '_cpts_hash', $new_cpts_hash );
					global $wp_rewrite;
					$wp_rewrite->flush_rules();
				}
			}
		}
	
	}


	/**
	 * Colum header settings for custom post types
	 */
	
	public function cpt_edit_columns( $columns ) { 
	
		$columns = array( 
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Title', 'go_portfolio_textdomain' ),
			'featured_image' => __( 'Featured Image', 'go_portfolio_textdomain' ),
			'description' => __( 'Description', 'go_portfolio_textdomain' ),
			'cat' => __( 'Categories', 'go_portfolio_textdomain' ),
			'tag' => __( 'Tags', 'go_portfolio_textdomain' ),
			'date' => __( 'Date', 'go_portfolio_textdomain' ),
			'comments' => '<div title="Comments" class="comment-grey-bubble"></div>'				
		); 
		 
		return $columns;  
	}


	/**
	 * Column settings for custom post types
	 */
	
	public function cpt_custom_columns( $column ) { 
		global $post;
		$cat_list='';
		$tag_list='';
		$taxonomies = get_object_taxonomies( $post->post_type );
		
		if ( !empty( $taxonomies ) ) {
			foreach( $taxonomies as $taxonomy ) {
				
				/* Get categories */
				if ( preg_match('/-cat$/', $taxonomy ) ) {
					$cat_list = get_the_term_list( $post->ID, $taxonomy, '', ', ','' );
				}
				
				/* Get tags */			
				if ( preg_match('/-tag$/', $taxonomy ) ) {
					$tag_list = get_the_term_list( $post->ID, $taxonomy, '', ', ','' );
				}
				
			}
		}
	
		switch ( $column ) {
	
			case 'description': 
				$content = $post->post_content; 
				$content = apply_filters( 'get_the_excerpt', '', 12 );
				$content = apply_filters( 'the_content', $content );
				echo $content;
				break;
			case 'featured_image': 
				echo get_the_post_thumbnail( $post->ID, array( 50, 50 ) ); 
				break; 
			case 'cat':
				echo $cat_list;
				break;
	
			case 'tag':
				echo $tag_list;
				break;			
		}  
	}


	/**
	 * Create metabox
	 */
	 
	public function create_meta_box() {

		$post_types = array();
		$custom_post_types = get_option( self::$plugin_prefix . '_cpts' );
		$general_settings = get_option( self::$plugin_prefix . '_general_settings' );
		$args = array(
		   'public'   => true,
		   '_builtin' => false,  
		);			
		$output = 'objects';
		$operator = 'and';
		$all_custompost_types = get_post_types( $args, $output, $operator );
		$post_type_list=array();
		
		$custom_post_type_list=array();
		/* Add plugin post types */
		if ( $custom_post_types ) {
			foreach ( $custom_post_types as $custom_post_type ) {
				$post_type_list[] = $custom_post_type['slug']; 
				$custom_post_type_list[] = $custom_post_type['slug']; 
			}
		}
		
		/* Add other cpt is enabled */
		if ( $all_custompost_types ) {
			foreach ( $all_custompost_types as $all_cpt_key => $all_custompost_type ) {
				if ( post_type_supports( $all_cpt_key, 'thumbnail' ) && !in_array ( $all_cpt_key, $custom_post_type_list ) && isset( $general_settings['enable_post_type'][$all_cpt_key] ) ) {
					$post_type_list[] = $all_cpt_key; 
				}
			}
		}		
		
		/* Add regular blog post if enabled */
		if ( isset( $general_settings['enable_post_type']['post'] ) ) { $post_type_list[] = 'post'; }
		
		/* Create meta box fields */
		$meta_box_fields = array( 
			
			/* Thumbnail options */
			array( 
				'name' => __( 'Thumbnail type', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumb_type',
				'type' => 'select',
				'desc' => __( 'Select thumbnail type.', 'go_portfolio_textdomain' ),
				'options' => array( 
					array( 'name' => __( 'Image', 'go_portfolio_textdomain' ), 'value' => 'image', 'data-children'=> 'image' ),
					array( 'name' => __( 'Video', 'go_portfolio_textdomain' ), 'value' => 'video', 'data-children'=> 'video' ),	
					array( 'name' => __( 'Audio', 'go_portfolio_textdomain' ), 'value' => 'audio', 'data-children'=> 'audio' ),		
				),
				'class' => 'regular-text',		
				'data-parent' => 'thumbnail-type'
			),
			array( 
				'name' => __( 'Video thumbnail type', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumb_video_type',
				'type' => 'select',
				'desc' => __( 'Select video type.', 'go_portfolio_textdomain' ),
				'options' => array( 
					array( 'name' => __( 'Youtube video', 'go_portfolio_textdomain' ), 'value' => 'youtube_video', 'data-children'=> 'youtube-video' ),
					array( 'name' => __( 'Vimeo video', 'go_portfolio_textdomain' ), 'value' => 'vimeo_video', 'data-children'=> 'vimeo-video' ),
					array( 'name' => __( 'Screenr video', 'go_portfolio_textdomain' ), 'value' => 'screenr_video', 'data-children'=> 'screenr-video' ),	
					array( 'name' => __( 'Dailymotion video', 'go_portfolio_textdomain' ), 'value' => 'dailymotion_video', 'data-children'=> 'dailymotion-video' ),
					array( 'name' => __( 'Metacafe video', 'go_portfolio_textdomain' ), 'value' => 'metacafe_video', 'data-children'=> 'metacafe-video' )
				),
				'class' => 'regular-text',
				'data-parent' => 'thumbnail-video-type',
				'wrapper-data-parent' => 'thumbnail-type',
				'wrapper-data-children' => 'video'
			),

			/* Audio thumbail */
			array( 
				'name' => __( 'Audio thumbnail type', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_audio_type',
				'type' => 'select',
				'desc' => __( 'Select audio type.', 'go_portfolio_textdomain' ),
				'options' => array( 
					array( 'name' => __( 'Soundcloud audio', 'go_portfolio_textdomain' ), 'value' => 'soundcloud_audio', 'data-children'=> 'soundcloud-audio' ),
					array( 'name' => __( 'Mixcloud audio', 'go_portfolio_textdomain' ), 'value' => 'mixcloud_audio', 'data-children'=> 'mixcloud-audio' ),
					array( 'name' => __( 'Beatport audio', 'go_portfolio_textdomain' ), 'value' => 'beatport_audio', 'data-children'=> 'beatport-audio' ),							
				),
				'class' => 'regular-text',
				'data-parent' => 'thumbnail-audio-type',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-type',
				'wrapper-data-children' => 'audio'
			),
			
			/* Youtube video thumbnail */
			array( 
				'name' => __( 'Youtube video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_youtube_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'youtube-video'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_youtube_video_h',
				'default' => '',
				'desc' => __( 'Height of the video (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'youtube-video'
			),
			
			/* Vimeo video thumbnail */
			array( 
				'name' => __( 'Vimeo video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_vimeo_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'vimeo-video'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_vimeo_video_h',
				'default' => '',
				'desc' => __( 'Height of the video (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'vimeo-video'
			),	
			array( 
				'name' =>  __( 'Color', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_vimeo_video_c',
				'default' => '',
				'desc' => __( 'Vimeo control colors (if the video allows).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'small-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'vimeo-video'
			),			
		
			/* Screenr video thumbnail */
			array( 
				'name' => __( 'Screenr video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_screenr_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'screenr-video'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_screenr_video_h',
				'default' => '',
				'desc' => __( 'Height of the video (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'screenr-video'
			),
			
			/* Dailymotion video thumbnail */
			array( 
				'name' => __( 'Dailymotion video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_dailymotion_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'dailymotion-video'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_dailymotion_video_h',
				'default' => '',
				'desc' => __( 'Height of the video (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'dailymotion-video'
			),

			/* Metacafe video thumbnail */
			array( 
				'name' => __( 'Metacafe video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_metacafe_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'metacafe-video'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_metacafe_video_h',
				'default' => '',
				'desc' => __( 'Height of the video (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-video-type',
				'wrapper-data-children' => 'metacafe-video'
			),						

			/* Soundcloud audio thumbnail */
			array( 
				'name' => __( 'Soundcloud track ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_soundcloud_audio_id',
				'default' => '',
				'desc' => __( 'Track ID of the audio.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'soundcloud-audio'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_soundcloud_audio_h',
				'default' => '',
				'desc' => __( 'Height of the audio (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'soundcloud-audio'
			),
			array( 
				'name' =>  __( 'Color', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_soundcloud_audio_c',
				'default' => '',
				'desc' => __( 'Color of the player.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'small-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'soundcloud-audio'
			),						

			/* Mixcloud audio lightbox */
			array( 
				'name' => __( 'Mixcloud track URL', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_mixcloud_audio_id',
				'default' => '',
				'desc' => __( 'URL of the audio.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'mixcloud-audio'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_mixcloud_audio_h',
				'default' => '',
				'desc' => __( 'Height of the audio (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'mixcloud-audio'
			),
			array( 
				'name' =>  __( 'Color', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_mixcloud_audio_c',
				'default' => '',
				'desc' => __( 'Color of the player.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'small-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'mixcloud-audio'
			),						
			
			/* Beatport audio thumbnail */
			array( 
				'name' => __( 'Beatport track ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_beatport_audio_id',
				'default' => '',
				'desc' => __( 'Track ID of the audio.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'beatport-audio'		
			),
			array( 
				'name' =>  __( 'Height', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_thumbnail_beatport_audio_h',
				'default' => '',
				'desc' => __( 'Height of the audio (optional).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type thumbnail-audio-type',
				'wrapper-data-children' => 'beatport-audio'
			),			
			
			/* Lightbox options */
			array( 
				'name' => __( 'Hide overlay?', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_hide_overlay',
				'desc' => __( 'Whether to hide overlay.', 'go_portfolio_textdomain' ),		
				'type' => 'checkbox',
				'wrapper-data-parent' => 'thumbnail-type',
				'wrapper-data-children' => 'image'		
			),	
			array( 
				'name' => __( 'Lighbox type', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lighbox_type',
				'type' => 'select',
				'desc' => __( 'Select lighbox type.', 'go_portfolio_textdomain' ),
				'options' => array( 
					array( 'name' => __( 'Image', 'go_portfolio_textdomain' ), 'value' => 'image' ),
					array( 'name' => __( 'Video', 'go_portfolio_textdomain' ), 'value' => 'video', 'data-children'=> 'video_lb' ),
					array( 'name' => __( 'Audio', 'go_portfolio_textdomain' ), 'value' => 'audio', 'data-children'=> 'audio_lb' ),		
				),
				'class' => 'regular-text',		
				'data-parent' => 'lightbox-type',
				'wrapper-data-parent' => 'thumbnail-type',
				'wrapper-data-children' => 'image'		
			),
			
			/* Video lighbox */
			array( 
				'name' => __( 'Video lightbox type', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_video_type',
				'type' => 'select',
				'desc' => __( 'Select video type.', 'go_portfolio_textdomain' ),
				'options' => array( 
					array( 'name' => __( 'Youtube video', 'go_portfolio_textdomain' ), 'value' => 'youtube_video', 'data-children'=> 'youtube-video' ),
					array( 'name' => __( 'Vimeo video', 'go_portfolio_textdomain' ), 'value' => 'vimeo_video', 'data-children'=> 'vimeo-video' ),
					array( 'name' => __( 'Screenr video', 'go_portfolio_textdomain' ), 'value' => 'screenr_video', 'data-children'=> 'screenr-video' ),
					array( 'name' => __( 'Dailymotion video', 'go_portfolio_textdomain' ), 'value' => 'dailymotion_video', 'data-children'=> 'dailymotion-video' ),
					array( 'name' => __( 'Metacafe video', 'go_portfolio_textdomain' ), 'value' => 'metacafe_video', 'data-children'=> 'metacafe-video' )		
				),
				'class' => 'regular-text',
				'data-parent' => 'lightbox-video-type',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type',
				'wrapper-data-children' => 'video_lb'
			),
			
			/* Audio lighbox */
			array( 
				'name' => __( 'Audio lightbox type', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_audio_type',
				'type' => 'select',
				'desc' => __( 'Select audio type.', 'go_portfolio_textdomain' ),
				'options' => array( 
					array( 'name' => __( 'Soundcloud audio', 'go_portfolio_textdomain' ), 'value' => 'soundcloud_audio', 'data-children'=> 'soundcloud-audio' ),
					array( 'name' => __( 'Mixcloud audio', 'go_portfolio_textdomain' ), 'value' => 'mixcloud_audio', 'data-children'=> 'mixcloud-audio' ),
					array( 'name' => __( 'Beatport audio', 'go_portfolio_textdomain' ), 'value' => 'beatport_audio', 'data-children'=> 'beatport-audio' ),							
				),
				'class' => 'regular-text',
				'data-parent' => 'lightbox-audio-type',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type',
				'wrapper-data-children' => 'audio_lb'
			),			
			
			/* Youtube video lightbox */
			array( 
				'name' => __( 'Youtube video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_youtube_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-video-type',
				'wrapper-data-children' => 'youtube-video'		
			),
			
			/* Vimeo video lightbox */
			array( 
		
				'name' => __( 'Vimeo video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_vimeo_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-video-type',
				'wrapper-data-children' => 'vimeo-video'		
			),
			array( 
				'name' =>  __( 'Color', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_vimeo_video_c',
				'default' => '',
				'desc' => __( 'Vimeo control colors (if the video allows).', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'small-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-video-type',
				'wrapper-data-children' => 'vimeo-video'
			),			
		
			/* Screenr video lightbox */
			array( 
				'name' => __( 'Screenr video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_screenr_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-video-type',
				'wrapper-data-children' => 'screenr-video'		
			),
			
			/* Dailymotion video lightbox */
			array( 
				'name' => __( 'Dailymotion video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_dailymotion_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-video-type',
				'wrapper-data-children' => 'dailymotion-video'		
			),
			
			/* Metacafe video lightbox */
			array( 
				'name' => __( 'Metacafe video ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_metacafe_video_id',
				'default' => '',
				'desc' => __( 'ID of the video.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-video-type',
				'wrapper-data-children' => 'metacafe-video'		
			),
			
			/* Soundcloud audio lightbox */
			array( 
				'name' => __( 'Soundcloud track ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_soundcloud_audio_id',
				'default' => '',
				'desc' => __( 'Track ID of the audio.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-audio-type',
				'wrapper-data-children' => 'soundcloud-audio'		
			),
			array( 
				'name' =>  __( 'Color', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_soundcloud_audio_c',
				'default' => '',
				'desc' => __( 'Color of the player.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'small-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-audio-type',
				'wrapper-data-children' => 'soundcloud-audio'
			),
			
			/* Mixcloud audio lightbox */
			array( 
				'name' => __( 'Mixcloud track URL', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_mixcloud_audio_id',
				'default' => '',
				'desc' => __( 'URL of the audio.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-audio-type',
				'wrapper-data-children' => 'mixcloud-audio'		
			),
			array( 
				'name' =>  __( 'Color', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_mixcloud_audio_c',
				'default' => '',
				'desc' => __( 'Color of the player.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'small-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-audio-type',
				'wrapper-data-children' => 'mixcloud-audio'
			),
			
			/* Beatport audio lightbox */
			array( 
				'name' => __( 'Beatport track ID', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_lightbox_beatport_audio_id',
				'default' => '',
				'desc' => __( 'Track ID of the audio.', 'go_portfolio_textdomain' ),
				'type' => 'text',
				'class' => 'regular-text',
				'wrapper-data-parent' => 'thumbnail-type lightbox-type lightbox-audio-type',
				'wrapper-data-children' => 'beatport-audio'		
			),								
			
			/* Lightbox button options */	
			array( 
				'name' => __( 'Hide lightbox button on overlay?', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_hide_lightbox_button',
				'desc' => __( 'Whether to hide the lightbox button or circle on overlay.', 'go_portfolio_textdomain' ),		
				'type' => 'checkbox',
				'wrapper-data-parent' => 'thumbnail-type',
				'wrapper-data-children' => 'image'		
			),
			array( 
				'name' => __( 'Hide read more button on overlay?', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_hide_link_button',
				'desc' => __( 'Whether to hide the read more button or circle on overlay.', 'go_portfolio_textdomain' ),		
				'type' => 'checkbox',
				'wrapper-data-parent' => 'thumbnail-type',
				'wrapper-data-children' => 'image'			
			),
			array( 
				'name' => __( 'Custom post link', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_post_link',
				'default' => '',
				'desc' => __( 'Whether to replace the default links which redirect to the post.', 'go_portfolio_textdomain' ),		
				'type' => 'text',
				'class' => 'regular-text'		
			),
			array( 
				'name' => __( 'Open link in new window?', 'go_portfolio_textdomain' ),
				'id' => self::$plugin_prefix . '_post_link_target',
				'desc' => __( 'Whether to open the link in new window.', 'go_portfolio_textdomain' ),		
				'type' => 'checkbox'		
			),			
		 );
		
		/* Add new metaboxes */
		$add_nex_meta_boxes = new GW_Meta_Box( self::$plugin_prefix . '_options', __( 'Go Portfolio Options', 'go_portfolio_textdomain' ), $meta_box_fields, $post_type_list );

	}
	
	
	/**
	 * Shortcode function
	 */

	public function go_portfolio_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array( 
			'id' 	=> null,
			'margin_top' => '0',
			'margin_bottom' => '0'
		 ), $atts ) );

		$shortcode_content = null;
	
		/* Check the id */
		if ( !isset( $id ) ) { 
		
			/* If id is missing */
			return '<p>' .  __( 'You must set a portfolio id.', 'go_portfolio_textdomain' ) . '</p>';
		
		} else {
			
			/* If id is ok */
			$id = sanitize_key( $id );

			/* Get data from db */
			$portfolios = get_option( self::$plugin_prefix . '_portfolios' );
			$custom_post_types = get_option( self::$plugin_prefix . '_cpts' );
			$templates = get_option( self::$plugin_prefix . '_templates' );
			$styles = get_option( self::$plugin_prefix . '_styles' );
			
			/* Check if portfolio exists and really registered */
			if ( !empty( $portfolios ) ) {
					foreach ( $portfolios as $portfolio_key => $portfolio ) {
		
					/* Check if given id exist in plugin db */
					if ( $portfolio['id'] == $id ) {
							
							/* Check if post type is registered */
							$post_types = get_post_types( '', 'objects' );
							if ( isset( $post_types[$portfolio['post-type']] ) ) {
								$query_post_type = $portfolio['post-type'];
														
								global $wp_query, $post;
								$new_wp_query = null;
								$new_wp_query = new WP_Query();
		
								/* Set query post type */
								$arg_post_type = isset( $portfolio['post-type'] ) && !empty( $portfolio['post-type'] ) ? $portfolio['post-type'] : 'post';

								/* Set query taxonomy & terms */
								$arg_tax = isset ( $portfolio['post-tax'][$arg_post_type] ) && !empty( $portfolio['post-tax'][$arg_post_type] ) ? $portfolio['post-tax'][$arg_post_type] : array();
								$arg_terms = isset( $portfolio['post-term'][$arg_post_type][$arg_tax] ) && !empty( $portfolio['post-term'][$arg_post_type][$arg_tax] ) && !in_array( 'all', $portfolio['post-term'][$arg_post_type][$arg_tax] ) ? $portfolio['post-term'][$arg_post_type][$arg_tax] : array(); 

								/* Set query args */
								$new_wp_query_args = array (
									'post_type' => $portfolio['post-type'],
									'posts_per_page' => isset( $portfolio['post-count'] ) && !empty( $portfolio['post-count'] ) ? $portfolio['post-count'] : '-1',
									'cache_results' => false,
									'orderby' => $portfolio['orderby'], 
									'order' => $portfolio['order'],
								);
								
								/* Modify query args - exlude current */
								if ( is_singular() ) {
									if ( isset( $portfolio['post-type'] ) && !empty( $portfolio['post-type'] ) && $portfolio['post-type'] == $post->post_type ) {
										$new_wp_query_args['post__not_in'] = array( $post->ID );
									}
								}
								
								/* Modify query args - taxnomy */
								if ( isset( $arg_tax ) && !empty( $arg_tax ) && isset( $arg_terms ) && !empty( $arg_terms )  ) {
									$new_wp_query_args['tax_query'] = array(
										array(
											'taxonomy' => $arg_tax,
											'terms' => $arg_terms
										)
									);
								}
								$new_wp_query ->query( $new_wp_query_args );
							
								/* Get template */
								if ( isset( $portfolio['template'] ) && !empty( $portfolio['template'] ) ) {
									$template_type = $portfolio['template'];
									if ( isset( $portfolio['template-data'][$portfolio['template']] ) && !empty( $portfolio['template-data'][$portfolio['template']] ) ) {
										$template = stripslashes( $portfolio['template-data'] );
									} else {
										$template = stripslashes( $templates[$portfolio['template']]['data'] );
									} 
								} else {
									return '<p>' .  __( 'The template is missing.', 'go_portfolio_textdomain' ) . '</p>';
								}
		
								/* Set portfolio classes */
								$layout_type = isset( $portfolio['layout-type'] ) && !empty( $portfolio['layout-type'] ) ? $portfolio['layout-type']  : 'grid';
								
								/* 1. Slider layout */
								if ( $layout_type == 'slider' ) { 
									$slider_data['auto']['play'] = isset( $portfolio['slider-autoplay'] ) && !empty( $portfolio['slider-autoplay'] ) ? true : false;
									$slider_data['auto']['timeoutDuration'] = isset( $portfolio['slider-autoplay-timeout'] ) && !empty( $portfolio['slider-autoplay-timeout'] ) ? floatval( $portfolio['slider-autoplay-timeout'] ) : null;
									$slider_data['auto']['pauseOnHover'] = true;
									$slider_data['circular'] = isset( $portfolio['slider-infinite'] ) && !empty( $portfolio['slider-infinite'] ) ? true : false;
									$slider_data['infinite'] = isset( $portfolio['slider-infinite'] ) && !empty( $portfolio['slider-infinite'] ) ? true : false;
									$slider_data['direction'] = isset( $portfolio['slider-autoplay-direction'] ) && $portfolio['slider-autoplay-direction'] == 'right' ? 'right' : 'left';
									$post_classes[] = 'gw-gopf-slider-type';
								}
								
								/* 2. Grid layout */
								if ( $layout_type == 'grid' ) { $post_classes[] = 'gw-gopf-grid-type'; }
								if ( isset( $portfolio['column-layout'] ) && !empty( $portfolio['column-layout'] ) ) { $post_classes[]=$portfolio['column-layout']; }
								if ( isset( $portfolio['style'] ) && !empty( $portfolio['style'] ) ) { $post_classes[]=$styles[$portfolio['style']]['class']; }	
								if ( isset( $portfolio['style'] ) && !empty( $portfolio['style'] ) && isset( $portfolio['effect-data'] ) && !empty( $portfolio['effect-data'] ) ) { $post_classes[]=$styles[$portfolio['style']]['class'] . '-' . $portfolio['effect-data']; }
								ob_start();
								?>						
								<!-- portfolio -->

								<div id="<?php echo esc_attr( self::$plugin_prefix . '_' . $portfolio['id'] ); ?>" style="<?php echo esc_attr( ( isset( $margin_top ) ? 'margin-top:' . $margin_top . ';' : '' ) . ( isset( $margin_bottom ) ? 'margin-bottom:' . $margin_bottom . ';' : '' ) ); ?>">
									<!--[if lt IE 9]><div class="gw-gopf gw-gopf-ie <?php echo esc_attr( implode(' ', $post_classes ) ); ?>" data-url="<?php echo GW_GO_PORTFOLIO_URI; ?>" data-transenabled="<?php echo esc_attr( isset( $portfolio['trans-enabled'] ) ? 'true' : 'false' ); ?>"><![endif]-->
									<!--[if gte IE 9]> <!--><div class="gw-gopf <?php echo esc_attr( implode(' ', $post_classes ) ); ?>" data-url="<?php echo GW_GO_PORTFOLIO_URI; ?>" data-transenabled="<?php echo esc_attr( isset( $portfolio['trans-enabled'] ) ? 'true' : 'false' ); ?>"> <!--<![endif]-->
									<?php 
									/* Print portfolio filter */
									if ( isset( $portfolio['filterable'] ) && $layout_type == 'grid' ) : 
									$current_terms = get_terms( $arg_tax, 'include=' . implode ( ',', $arg_terms ) );
									if ( isset( $current_terms ) && !empty( $current_terms ) && !isset( $current_terms->errors ) ) :
									?>
									<!-- portfolio filter -->
									<div class="gw-gopf-filter gw-gopf-clearfix <?php echo ( isset( $portfolio['filter-align'] ) && !empty( $portfolio['filter-align'] ) ?  ' '. $portfolio['filter-align'] : '' ); ?>">
										<div class="gw-gopf-cats">
											<span class="gw-gopf-current"><a href="#"<?php echo ( isset( $portfolio['filter-current-tag-style'] ) && !empty( $portfolio['filter-current-tag-style'] ) ? 'class="' . $portfolio['filter-current-tag-style'] . '"' : '' ); ?>><?php echo ( isset( $portfolio['filter-all-text'] ) && !empty( $portfolio['filter-all-text'] ) ? $portfolio['filter-all-text'] : 'All' ); ?></a></span><?php 
											foreach ( $current_terms as $current_term ) :
											?><span data-filter="<?php echo esc_attr( $current_term->slug ); ?>"><a href="#"<?php echo ( isset( $portfolio['filter-tag-style'] ) && !empty( $portfolio['filter-tag-style'] ) ? 'class="' . $portfolio['filter-tag-style'] . '"' : '' ); ?>><?php echo $current_term->name; ?></a></span><?php 
											endforeach;		
											?>
										</div>
									</div>
									<div class="gw-gopf-clearfix"></div>
									<!-- /portfolio filter -->
									<?php 
									endif; 
									endif;
									?>
									
									<!-- portfolio posts (items) -->
									<div class="gw-gopf-posts-wrap">
										<?php 
										/* Print slider arrows */
										if ( $layout_type == 'slider' ) : 
										?>
										<div class="gw-gopf-slider-controls-wrap gw-gopf-clearfix<?php echo ( isset( $portfolio['slider-arrows-align'] ) && !empty( $portfolio['slider-arrows-align'] ) ?  ' '. $portfolio['slider-arrows-align'] : '' ); ?>">
											<div class="gw-gopf-slider-controls gw-gopf-clearfix">
												<div class="gw-gopf-control-prev"><a href="#"><img src="<?php echo GW_GO_PORTFOLIO_URI . '/assets/images/icon_prev.png'; ?>" alt=""></a></div>
												<div class="gw-gopf-control-next"><a href="#"><img src="<?php echo GW_GO_PORTFOLIO_URI . '/assets/images/icon_next.png'; ?>" alt=""></a></div>
											</div>
										</div>
										<?php endif; ?>							
										<div class="gw-gopf-posts-wrap-inner">
											<div class="gw-gopf-posts gw-gopf-clearfix"<?php echo ( isset( $portfolio['column-layout'] ) && !empty( $portfolio['column-layout'] ) ? ' data-col="' . preg_replace('/[^0-9]/', '', $portfolio['column-layout'] ) . '"' : '' ); ?><?php echo ( $layout_type == 'slider' ? ' data-slider="' . esc_js( json_encode( $slider_data ) ) . '"' : '' ); ?>>
												<?php 
												
												/* Get thumbs sizes */
												$thumbanail_size = isset( $portfolio['thumbnail-size'] ) && !empty( $portfolio['thumbnail-size'] ) ? $portfolio['thumbnail-size'] : 'full';
												$lightbox_size = isset( $portfolio['lightbox-size'] ) && !empty( $portfolio['lightbox-size'] ) ? $portfolio['lightbox-size'] : 'full';
												
												/* Loop */
												while( $new_wp_query->have_posts() ) : $new_wp_query->the_post();
												?>
												<!-- portfolio post (item) -->
												<?php
												
												/* Get post term list */
												$post_term_list = array();
												$post_terms= get_the_terms( $post->ID, $arg_tax ); 
												if ( isset( $post_terms ) && !empty( $post_terms ) ) {
													foreach ( $post_terms as $post_term ) {
														$post_term_list[] = $post_term->slug;
													}
												}
												
												/* Set post & thumbnail types */
												$post_meta = get_post_meta( $post->ID );
												$thumbnail_type = isset( $post_meta['gw_go_portfolio_thumb_type'][0] ) && !empty( $post_meta['gw_go_portfolio_thumb_type'][0] ) ? $post_meta['gw_go_portfolio_thumb_type'][0]  : 'image';
												$lighbox_type = isset( $post_meta['gw_go_portfolio_lighbox_type'][0] ) && !empty( $post_meta['gw_go_portfolio_lighbox_type'][0] ) ? $post_meta['gw_go_portfolio_lighbox_type'][0]  : 'image';
												$has_overlay = isset( $portfolio['overlay'] ) && $thumbnail_type == 'image' && !isset( $post_meta['gw_go_portfolio_hide_overlay'][0] ) ? true : false;
												$post_link = isset( $post_meta['gw_go_portfolio_post_link'][0] ) && !empty( $post_meta['gw_go_portfolio_post_link'][0] ) ? $post_meta['gw_go_portfolio_post_link'][0] : get_permalink();
												
												/* Get template data */
												$replaced_template = null;
												$has_lighbox = true;
												$force_img_thumb = true;
												?>
												<div id="<?php echo esc_attr( $post->ID ); ?>" class="gw-gopf-col-wrap" data-filter="<?php echo esc_attr( implode(' ', $post_term_list ) ); ?>">
													<div class="gw-gopf-post-col<?php echo ( $has_overlay ? ' gw-gopf-has-overlay' : '' ); ?><?php echo ( isset( $portfolio['overlay-hover'] ) && $portfolio['overlay-hover']=='2' ?  ' gw-gopf-post-overlay-hover' : '' ); ?>">
													<!-- template -->
													<?php											
													/* 1. Post link */
													if ( isset( $template_data['post_link'] ) ) { unset( $template_data['post_link'] ); }
													$template_data['post_link'] = $post_link;
													
													/* 2. Post media */
													if ( isset( $template_data['post_media'] ) ) { unset( $template_data['post_media'] ); }
													
													/* 3. Image thumbnail */
													if ( $thumbnail_type == 'image' ) {
														if ( has_post_thumbnail() ) {
															$tn_id = null;
															$img = null;
															$lightbox_img = null;
															$tn_id = get_post_thumbnail_id( $post->ID );
															$lightbox_img = wp_get_attachment_image_src( $tn_id, $lightbox_size );
															$lightbox_img_caption = get_post_field( 'post_excerpt', $tn_id );
															$img = wp_get_attachment_image_src( $tn_id, $thumbanail_size );
															
															if ( !empty( $tn_id ) ) {
																$width = $img[1];
																$height = $img[2];
			
																/* Set the default image ratio */
																$img_ratio = isset( $img[1] ) &&  $img[1] > 0 ? $img[2]/$img[1] : $img[2];
			
																/* Override image ratio if user set */
																if ( isset( $portfolio['width'] ) && !empty( $portfolio['width'] ) && floatval( $portfolio['width'] ) > 0 && isset( $portfolio['height'] ) && !empty( $portfolio['height'] ) && floatval( $portfolio['height'] ) > 0 ) {
																	$img_ratio = floatval( $portfolio['height'] ) / floatval( $portfolio['width'] );
																}
																
																/* Detect the image orientation  */
																if ($img[2]/$img[1]>1) {
																	if ($img_ratio > $img[2]/$img[1] )  {
																		$img_orientation='gw-gopf-landscape';
																		$style='left:'.(($img[1]*$img_ratio-$img[2])*0.5/($img[2])*-100).'%';
																	} else {
																		$img_orientation='gw-gopf-portrait';
																		$style='top:'.(($img[1]*$img_ratio-$img[2])*0.5/$img[1]*100).'%';															
																	}
																} else {
																	if ($img_ratio < $img[2]/$img[1] )  {
																		$img_orientation='gw-gopf-portrait';
																		$style='top:'.(($img[2]-$img[1]*$img_ratio)*0.5/($img[1]*$img_ratio)*-100).'%';
																	} else {
																		$img_orientation='gw-gopf-landscape';
																		$style='left:'.(($img[2]-$img[1]*$img_ratio)*0.5/$img[2]*100).'%';
																	}																	
																}
																
																$template_data['post_media'] = '<div class="gw-gopf-post-media-wrap ' . $img_orientation . '" style="padding-bottom:'. $img_ratio * 100 . '%; background-image:url(' . $img[0] . ')">';
																/* Fallback image for IE8 */
																$template_data['post_media'] .= get_the_post_thumbnail( $post->ID, $thumbanail_size , array( 'class' => 'gw-gopf-fallback-img ' . $img_orientation , 'style' => $style ) );
																$template_data['post_media'] .= '</div>';
															}
														}
													}
													
													/* 4. Video & audio thumbnail */
													if ( $thumbnail_type == 'video' || $thumbnail_type == 'audio' ) {
														
														$video_type = isset( $post_meta['gw_go_portfolio_thumb_type'][0] ) && $post_meta['gw_go_portfolio_thumb_type'][0] == 'video' ? $post_meta['gw_go_portfolio_thumb_video_type'][0] : null;
														$audio_type = isset( $post_meta['gw_go_portfolio_thumb_type'][0] ) && $post_meta['gw_go_portfolio_thumb_type'][0] == 'audio' ? $post_meta['gw_go_portfolio_thumb_video_type'][0] : null;
														$portfolio['width'] = isset( $portfolio['width'] ) && !empty( $portfolio['width'] ) ? floatval( $portfolio['height'] ) : null;

														/* Video types */
														if ( $video_type ) {
															$media_ratio = $portfolio['width'] && !empty( $portfolio['width'] ) && $portfolio['height'] && !empty( $portfolio['height'] ) ? $portfolio['height'] / $portfolio['width'] : 0.5625;
															if ( $video_type == 'youtube_video' ) {
																$post_meta['gw_go_portfolio_thumbnail_youtube_video_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_youtube_video_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_youtube_video_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_youtube_video_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_youtube_video_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_youtube_video_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_youtube_video_h'][0] : null;
																$media_embed='<iframe src="//www.youtube.com/embed/' . ( isset( $post_meta['gw_go_portfolio_thumbnail_youtube_video_id'][0] ) ?  $post_meta['gw_go_portfolio_thumbnail_youtube_video_id'][0] : '' ) . '?wmode=opaque" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
															} elseif ( $video_type == 'vimeo_video' ) {
																$post_meta['gw_go_portfolio_thumbnail_vimeo_video_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_vimeo_video_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_vimeo_video_h'][0] : null;																
																$color = isset( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_c'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_c'][0] ) ? 
																( mb_strlen( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_c'][0] = preg_replace( '/[^0-9a-f]/','', $post_meta['gw_go_portfolio_thumbnail_vimeo_video_c'][0] ) ) == 6 ? $post_meta['gw_go_portfolio_thumbnail_vimeo_video_c'][0] : '0' ) : '0';
																$media_embed='<iframe src="//player.vimeo.com/video/' . ( isset( $post_meta['gw_go_portfolio_thumbnail_vimeo_video_id'][0] ) ?  $post_meta['gw_go_portfolio_thumbnail_vimeo_video_id'][0] : '' ) . '?wmode=opaque&color=' . $color . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';													
															} elseif ( $video_type == 'screenr_video' ) {
																$post_meta['gw_go_portfolio_thumbnail_screenr_video_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_screenr_video_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_screenr_video_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_screenr_video_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_screenr_video_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_screenr_video_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_screenr_video_h'][0] : null;
																$media_embed='<iframe src="http://www.screenr.com/embed/' . ( isset( $post_meta['gw_go_portfolio_thumbnail_screenr_video_id'][0] ) ?  $post_meta['gw_go_portfolio_thumbnail_screenr_video_id'][0] : '' ) . '"?wmode=opaque" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';																									
															} elseif ( $video_type == 'dailymotion_video' ) {
																$post_meta['gw_go_portfolio_thumbnail_dailymotion_video_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_h'][0] : null;
																$video_embed='<iframe src="//www.dailymotion.com/embed/video/' . ( isset( $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_id'][0] ) ?  $post_meta['gw_go_portfolio_thumbnail_dailymotion_video_id'][0] : '' ) . '"?wmode=opaque" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';																									
															} elseif ( $video_type == 'metacafe_video' ) {
																$post_meta['gw_go_portfolio_thumbnail_metacafe_video_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_metacafe_video_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_metacafe_video_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_metacafe_video_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_metacafe_video_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_metacafe_video_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_metacafe_video_h'][0] : null;
																$media_embed='<iframe src="http://www.metacafe.com/embed/' . ( isset( $post_meta['gw_go_portfolio_thumbnail_metacafe_video_id'][0] ) ?  $post_meta['gw_go_portfolio_thumbnail_metacafe_video_id'][0] : '' ) . '"?wmode=opaque" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';																									
															}
														}
														
														/* Audio types */
														if ( $audio_type ) {
															$media_ratio = $portfolio['width'] && !empty( $portfolio['width'] ) && $portfolio['height'] && !empty( $portfolio['height'] ) ? $portfolio['height'] / $portfolio['width'] : 0.5625;
															if ( isset( $post_meta['gw_go_portfolio_thumbnail_audio_type'][0] ) && $post_meta['gw_go_portfolio_thumbnail_audio_type'][0]== 'soundcloud_audio' && isset( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_id'][0] ) ) {
																$post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_h'][0] : null;																
																$color = isset( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_c'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_c'][0] ) ? 
																( mb_strlen( $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_c'][0] = preg_replace( '/[^0-9a-f]/','', $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_c'][0] ) ) == 6 ? $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_c'][0] : '0' ) : '0';																		
																$media_embed = '<iframe src="//w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F'. $post_meta['gw_go_portfolio_thumbnail_soundcloud_audio_id'][0] . '&amp;color=' . $color . '&amp;show_artwork=true&amp;wmode=opaque" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
															} elseif ( isset( $post_meta['gw_go_portfolio_thumbnail_audio_type'][0] ) && $post_meta['gw_go_portfolio_thumbnail_audio_type'][0]== 'mixcloud_audio' && isset( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_id'][0] ) ) {
																$post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_h'][0] : null;																
																$color = isset( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_c'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_c'][0] ) ? 
																( mb_strlen( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_c'][0] = preg_replace( '/[^0-9a-f]/','', $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_c'][0] ) ) == 6 ? $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_c'][0] : '0' ) : '0';																		
																$media_embed = '<iframe src="//www.mixcloud.com/widget/iframe/?feed='. urlencode( trim( $post_meta['gw_go_portfolio_thumbnail_mixcloud_audio_id'][0], '/' ) ) . '%2F&amp;show_tracklist=&amp;stylecolor=' . $color . '&wmode=opaque" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
															} elseif ( isset( $post_meta['gw_go_portfolio_thumbnail_audio_type'][0] ) && $post_meta['gw_go_portfolio_thumbnail_audio_type'][0]== 'beatport_audio' && isset( $post_meta['gw_go_portfolio_thumbnail_beatport_audio_id'][0] ) ) {
																$post_meta['gw_go_portfolio_thumbnail_beatport_audio_h'][0] = isset( $post_meta['gw_go_portfolio_thumbnail_beatport_audio_h'][0] ) && !empty( $post_meta['gw_go_portfolio_thumbnail_beatport_audio_h'][0] ) ? floatval( $post_meta['gw_go_portfolio_thumbnail_beatport_audio_h'][0] ) : null;
																$height = $post_meta['gw_go_portfolio_thumbnail_beatport_audio_h'][0] && !empty( $post_meta['gw_go_portfolio_thumbnail_beatport_audio_h'][0] ) ? $post_meta['gw_go_portfolio_thumbnail_beatport_audio_h'][0] : null;																
																$media_embed = '<iframe src="http://embed.beatport.com/player?id=' . $post_meta['gw_go_portfolio_thumbnail_beatport_audio_id'][0] . '&amp;type=track&amp;wmode=opaque" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
															} 															
														}

														$media_mw_style = '';
														if ( $height ) {
															$media_mw_style = ' style="height:' . $height . 'px;"';
														} else {
															$media_mw_style = ' style="padding-bottom:' . $media_ratio * 100 . '%;"';
														}
														$template_data['post_media'] = '<div class="gw-gopf-post-media-wrap"' . $media_mw_style . '">' . $media_embed . '</div>';
													}
													
													/* 5. Post overlay */
													if ( $has_overlay ) {
														
															$template_data['post_overlay_buttons'] = '<div class="gw-gopf-post-overlay-bg"></div><div class="gw-gopf-post-overlay-inner">';
															$button_style_class = isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '2' ? 'gw-gopf-btn gw-gopf-post-overlay-btn' : 'gw-gopf-circle gw-gopf-post-overlay-circle';
															if ( isset( $portfolio['overlay-btn-style'] ) && !empty( $portfolio['overlay-btn-style'] ) ) { $button_style_class .= ' ' . $portfolio['overlay-btn-style']; }
															
															$popup_height = null;
															
															/* If the lightbox button is not disabled */
															if ( !isset( $post_meta['gw_go_portfolio_hide_lightbox_button'][0] ) ) {
																if ( $lighbox_type == 'image' ) {
																	$lighbox_link = isset( $lightbox_img[0] ) ? $lightbox_img[0] : '#';
																	$lighbox_class = 'gw-gopf-magnific-popup';
																	
																	if ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '2' ) {
																		$button_content = isset( $portfolio['overlay-btn-link-image'] ) ? $portfolio['overlay-btn-link-image'] : '';
																	} elseif ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '1' ) {
																		$button_content = apply_filters('the_content', get_the_content()); //'<img src="' . GW_GO_PORTFOLIO_URI . '/assets/images/icon_large.png" alt="">';
																	}
																} elseif ( $lighbox_type == 'video' || $lighbox_type == 'audio' ) {
																	$lighbox_link = '#';
																	$lighbox_class = 'gw-gopf-magnific-popup-html';

																	if ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '2' ) {
																		if ( $lighbox_type == 'video' ) {
																			$button_content = isset( $portfolio['overlay-btn-link-video'] ) ? $portfolio['overlay-btn-link-video'] : '';
																		} else {
																			$button_content = isset( $portfolio['overlay-btn-link-audio'] ) ? $portfolio['overlay-btn-link-audio'] : '';
																		}
																	} elseif ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '1' ) {
																		if ( $lighbox_type == 'video' ) {
																			$button_content = '<img src="' . GW_GO_PORTFOLIO_URI . '/assets/images/icon_video.png" alt="">';
																		} else {
																			$button_content = '<img src="' . GW_GO_PORTFOLIO_URI . '/assets/images/icon_audio.png" alt="">';
																		}
																	}
																	
																	/* Video types */
																	if ( isset( $post_meta['gw_go_portfolio_lightbox_video_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_video_type'][0]== 'youtube_video' && isset( $post_meta['gw_go_portfolio_lightbox_youtube_video_id'][0] ) ) {
																		$lighbox_link = '//www.youtube.com/watch?v=' . $post_meta['gw_go_portfolio_lightbox_youtube_video_id'][0];
																	} elseif ( isset( $post_meta['gw_go_portfolio_lightbox_video_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_video_type'][0]== 'vimeo_video' && isset( $post_meta['gw_go_portfolio_lightbox_vimeo_video_id'][0] ) ) {
																		$color = isset( $post_meta['gw_go_portfolio_lightbox_vimeo_video_c'][0] ) && !empty( $post_meta['gw_go_portfolio_lightbox_vimeo_video_c'][0] ) ? 
																		( mb_strlen( $post_meta['gw_go_portfolio_lightbox_vimeo_video_c'][0] = preg_replace( '/[^0-9a-f]/','', $post_meta['gw_go_portfolio_lightbox_vimeo_video_c'][0] ) ) == 6 ? $post_meta['gw_go_portfolio_lightbox_vimeo_video_c'][0] : '0' ) : '0';																		
																		$lighbox_link = '//vimeo.com/' . $post_meta['gw_go_portfolio_lightbox_vimeo_video_id'][0] . '?color=' . $color;
																	} elseif ( isset( $post_meta['gw_go_portfolio_lightbox_video_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_video_type'][0]== 'screenr_video' && isset( $post_meta['gw_go_portfolio_lightbox_screenr_video_id'][0] ) ) {
																		$lighbox_link = 'http://www.screenr.com/embed/' . $post_meta['gw_go_portfolio_lightbox_screenr_video_id'][0];
																	} elseif ( isset( $post_meta['gw_go_portfolio_lightbox_video_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_video_type'][0]== 'dailymotion_video' && isset( $post_meta['gw_go_portfolio_lightbox_dailymotion_video_id'][0] ) ) {
																		$lighbox_link = '//dailymotion.com/embed/video/' . $post_meta['gw_go_portfolio_lightbox_dailymotion_video_id'][0];
																	} elseif ( isset( $post_meta['gw_go_portfolio_lightbox_video_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_video_type'][0]== 'metacafe_video' && isset( $post_meta['gw_go_portfolio_lightbox_metacafe_video_id'][0] ) ) {
																		$lighbox_link = 'http://www.metacafe.com/embed/' . $post_meta['gw_go_portfolio_lightbox_metacafe_video_id'][0];
																	}
																	
																	/* Audio types */
																	if ( isset( $post_meta['gw_go_portfolio_lightbox_audio_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_audio_type'][0]== 'soundcloud_audio' && isset( $post_meta['gw_go_portfolio_lightbox_soundcloud_audio_id'][0] ) ) {
																		$color = isset( $post_meta['gw_go_portfolio_lightbox_soundcloud_audio_c'][0] ) && !empty( $post_meta['gw_go_portfolio_lightbox_soundcloud_audio_c'][0] ) ? 
																		( mb_strlen( $post_meta['gw_go_portfolio_lightbox_soundcloud_audio_c'][0] = preg_replace( '/[^0-9a-f]/','', $post_meta['gw_go_portfolio_lightbox_soundcloud_audio_c'][0] ) ) == 6 ? $post_meta['gw_go_portfolio_lightbox_soundcloud_audio_c'][0] : '0' ) : '0';																		
																		$lighbox_link = '//w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F'. $post_meta['gw_go_portfolio_lightbox_soundcloud_audio_id'][0] . '&amp;color=' . $color . '&amp;auto_play=true&amp;show_artwork=true';
																		$popup_height = 166;
																	} elseif ( isset( $post_meta['gw_go_portfolio_lightbox_audio_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_audio_type'][0]== 'mixcloud_audio' && isset( $post_meta['gw_go_portfolio_lightbox_mixcloud_audio_id'][0] ) ) {
																		$color = isset( $post_meta['gw_go_portfolio_lightbox_mixcloud_audio_c'][0] ) && !empty( $post_meta['gw_go_portfolio_lightbox_mixcloud_audio_c'][0] ) ? 
																		( mb_strlen( $post_meta['gw_go_portfolio_lightbox_mixcloud_audio_c'][0] = preg_replace( '/[^0-9a-f]/','', $post_meta['gw_go_portfolio_lightbox_mixcloud_audio_c'][0] ) ) == 6 ? $post_meta['gw_go_portfolio_lightbox_mixcloud_audio_c'][0] : '0' ) : '0';																		
																		$lighbox_link = '//www.mixcloud.com/widget/iframe/?feed='. urlencode( trim( $post_meta['gw_go_portfolio_lightbox_mixcloud_audio_id'][0], '/' ) ) . '%2F&amp;show_tracklist=&amp;stylecolor=' . $color;
																		$popup_height = 480;
																	} elseif ( isset( $post_meta['gw_go_portfolio_lightbox_audio_type'][0] ) && $post_meta['gw_go_portfolio_lightbox_audio_type'][0]== 'beatport_audio' && isset( $post_meta['gw_go_portfolio_lightbox_beatport_audio_id'][0] ) ) {
																		$lighbox_link = 'http://embed.beatport.com/player?id=' . $post_meta['gw_go_portfolio_lightbox_beatport_audio_id'][0] . '&type=track&auto=1';
																		$popup_height = 166;
																	} 																	
																	
																}
																$template_data['post_overlay_buttons'] .= '<a href="' . $lighbox_link . '" class="description ' . $lighbox_class . '"' . ( isset( $popup_height ) ? ' data-height="' . $popup_height . '"' : '' ) . '>' . $button_content . '</a>';														
															}
															
															/* If the read more button is not disabled $post_link*/
															if ( !isset( $post_meta['gw_go_portfolio_hide_link_button'][0] ) ) {
																if ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '2' ) {
																	$button_content = isset( $portfolio['overlay-btn-link-post'] ) ? $portfolio['overlay-btn-link-post'] : '';
																} elseif ( isset( $portfolio['overlay-style'] ) && $portfolio['overlay-style'] == '1' ) {
																	$button_content = '<img src="' . GW_GO_PORTFOLIO_URI . '/assets/images/icon_link.png" alt="">';
																}														
																$template_data['post_overlay_buttons'] .= '';//'<a href=" ' . $template_data['post_link'] . ' " class="' . $button_style_class . '">' . $button_content . '</a>';														
															}
															$template_data['post_overlay_buttons'] .= '</div>';
													
													}
													
													/* 6. Post title - Cut title if max length property is set */
													$template_data['post_title'] = esc_attr( trim( get_the_title() ) );
													$portfolio['title-length'] = floatval( $portfolio['title-length'] );
													if ( isset( $portfolio['title-length'] ) && !empty( $portfolio['title-length'] ) ) {
														if ( mb_strlen( $template_data['post_title'] ) > $portfolio['title-length'] ) { $template_data['post_title'] = mb_substr ( get_the_title(), 0,  $portfolio['title-length'] ) . ''; }
													}
		
													/* 7. Post date */
													$template_data['post_date'] = date_i18n( get_option( 'date_format' ), get_post_time( 'U', true ) );
													
													/* 8. Post excerpt - custom excerpt */
													$excerpt_src = isset( $portfolio['excerpt-src'] ) && !empty ( $portfolio['excerpt-src'] ) ? $portfolio['excerpt-src'] : 'content';
													$post_content_src = $excerpt_src == 'content' ? '' : get_the_excerpt();
													$loop_excerpt_length = isset( $portfolio['excerpt-length'] ) && !empty ( $portfolio['excerpt-length'] ) ? $portfolio['excerpt-length'] : null;
													
													if ( !strpos( $post->post_content, '<!--more-->') ) {
														/* Already without <!--more--> tag */
															$content = apply_filters( 'get_the_excerpt', $post_content_src, $loop_excerpt_length, '...' );
													} else { 
														/* Post without <!--more--> tag */
														$content = apply_filters( 'get_the_excerpt', $post_content_src, $loop_excerpt_length, '...' );		
													}
													$template_data['post_excerpt'] = $content;										
													
													/* 9. Post button text */
													if ( isset( $template_data['post_button_text'] ) ) { unset( $template_data['post_button_text'] ); }
													$template_data['post_button_text'] = $portfolio['post-button-text'];
													
													/* 10. Post button style */
													if ( isset( $template_data['post_button_style'] ) ) { unset( $template_data['post_button_style'] ); }
													$template_data['post_button_style'] = $portfolio['post_button_style'];													
													
													/* WooCommerce template parts */
													if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
													
														/* 11.1. Add to Cart button */
														$template_data['woo_add_to_cart'] = do_shortcode('[add_to_cart_url id="' . $post->ID . '"]');
														
														/* 11.2. Price */
														$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
														$sale_price = isset( $post_meta['_sale_price'][0] ) && !empty( $post_meta['_sale_price'][0] ) ? '<ins>' . $currency . $post_meta['_sale_price'][0].'</ins>' : '';
														$regular_price = isset( $post_meta['_regular_price'][0] ) && !empty( $post_meta['_regular_price'][0] ) ? ( $sale_price ? '<del>' . $currency . $post_meta['_regular_price'][0] . '</del>' : $currency . $post_meta['_regular_price'][0] ) : '';
														$template_data['woo_price'] = $regular_price . $sale_price;
														
														/* 11.3. On Sale */
														if ( !empty ( $regular_price ) ) {
															// for future use: $template_data['woo_on_sale'] = isset( $portfolio['woo_on_sale'] ) && !empty( $portfolio['woo_on_sale'] ) ? '<div class="gw-gopf-circle gw-gopf-woo-sale">' . $portfolio['woo_on_sale'] . '</div>': 'SALE';
															$template_data['woo_on_sale'] = '<div class="gw-gopf-woo-sale">' .  __( 'SALE', 'go_portfolio_textdomain' ) . '</div>';
														} else {
															$template_data['woo_on_sale'] = '';
														}
													}									
													/* Replace template */
													$replaced_template = $template;
													foreach( $template_data as $key => $value ) { 
														$replaced_template = preg_replace( '/(\{\{)\s?('.$key.'+\s?)(\}\})/', $value, $replaced_template );
													}
													
													echo $replaced_template;
													?>
													<!-- /template -->
													</div>
												</div>
												<!-- /portfolio post (item) -->
												<?php 
												endwhile;
												$new_wp_query = null;
												wp_reset_postdata();
												?>			
								
											</div>
										</div>								
									</div>
									<!-- /portfolio posts (items) -->		
								
								</div>
								</div>
								<!-- /portfolio -->										
								<?php
								
								/* return shorcode */
								$shortcode_content = ob_get_contents();
								$shortcode_content = do_shortcode( $shortcode_content );
								ob_end_clean();
								return $shortcode_content;									
								break;
								
							} else {
								
								/* If custom post type doesn't exist */
								return '<p>' . sprintf( __( 'Post type with a slug of "%s" is not registered.', 'go_portfolio_textdomain' ), $portfolio['post-type'] ) . '</p>';	
							}		
					} 
				}
			}

			/* If the id doesn't exist */
			return '<p>' . sprintf( __( 'Portfolio with an id of "%s" is not defined.', 'go_portfolio_textdomain' ), $id ) . '</p>';		
		}		
				
	}


}