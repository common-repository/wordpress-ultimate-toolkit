<?php
/**
 * WordPress Ultimate Toolkit
 *
 * @package WordPress_Ultimate_Toolkit
 */

/**
 * Core class of this plugin.
 *
 * This is a container object of the whole plugin, it will maintain all
 * global variables, manage all hooks plugged into WordPress, and register
 * all source code files.
 */
class WUT {

	/**
	 * The plugin entry file path.
	 *
	 * @var string The file path of the only entry file.
	 */
	public $plugin_file;

	/**
	 * The root path of the plugin's directory.
	 *
	 * @var string The root dir path of this plugin with slash / appended.
	 */
	public $root_dir;

	/**
	 * The root url path of the plugin's directory.
	 *
	 * @var string The root url of this plugin base path.
	 */
	public $root_url;

	/**
	 * Utils functionality instance.
	 *
	 * @var WUT_Utils
	 */
	public $utils;

	/**
	 * The constructor.
	 *
	 * @param string $plugin_file The entry file of this plugin.
	 * @param string $root_dir The plugin root directory.
	 * @param string $root_url The plugin root path.
	 */
	protected function __construct( $plugin_file, $root_dir, $root_url ) {
		$this->plugin_file = $plugin_file;
		$this->root_dir    = $root_dir;
		$this->root_url    = $root_url;
	}

	/**
	 * The main function of this plugin.
	 *
	 * @param string $file_path the plugin entry file path.
	 */
	public static function run( $file_path ) {
		$dir = plugin_dir_path( $file_path );
		$url = plugins_url( '', $file_path );
		$wut = new WUT( $file_path, $dir, $url );
		$wut->load_files();
		add_action( 'plugins_loaded', array( $wut, 'register' ) );
	}

	/**
	 * Require all files the plugin needed.
	 */
	public function load_files() {
		require $this->root_dir . 'inc/class-wut-option-manager.php';
		require $this->root_dir . 'inc/class-wut-utils.php';
		require $this->root_dir . 'inc/tags.php';
		require $this->root_dir . 'inc/class-wut-html-helper.php';
		require $this->root_dir . 'inc/class-wut-form-helper.php';
		require $this->root_dir . 'inc/class-wut-category-chooser.php';
		require $this->root_dir . 'widgets/class-wut-widget-recent-posts.php';
		require $this->root_dir . 'widgets/class-wut-widget-recent-comments.php';
		require $this->root_dir . 'widgets/class-wut-widget-most-viewed-posts.php';
		require $this->root_dir . 'widgets/class-wut-widget-related-posts.php';
		if ( is_admin() ) {
			require $this->root_dir . 'inc/class-wut-admin-panel.php';
			require $this->root_dir . 'inc/class-wut-admin-excerption.php';
			require $this->root_dir . 'inc/class-wut-admin-related-list.php';
			require $this->root_dir . 'inc/class-wut-admin-custom-code.php';
			require $this->root_dir . 'inc/class-wut-admin-meta-info.php';
			require $this->root_dir . 'inc/class-wut-admin-about.php';
			require $this->root_dir . 'inc/class-wut-admin.php';
		}
	}

	/**
	 * Main hook to WordPress.
	 */
	public function register() {
		$this->utils = new WUT_Utils();

		add_action(
			'widgets_init',
			function () {
				register_widget( 'WUT_Widget_Recent_Posts' );
				register_widget( 'WUT_Widget_Recent_Comments' );
				register_widget( 'WUT_Widget_Related_Posts' );
				if ( in_array( 'wp-postviews/wp-postviews.php', get_option( 'active_plugins' ), true ) ) {
					register_widget( 'WUT_Widget_Most_Viewed_Posts' );
				}
			}
		);

		$excerpt = WUT_Option_Manager::me()->get_options_by_key( WUT_Option_Manager::SUBKEY_EXCERPTION );
		if ( ! isset( $excerpt['enabled'] ) ) {
			$excerpt['enabled'] = true;
		}

		if ( $excerpt['enabled'] ) {
			// the priority should be 9, before the official `wp_trim_excerpt` filter.
			add_filter( 'get_the_excerpt', array( 'WUT_Utils', 'auto_excerption' ), 9 );
			add_filter( 'the_content', array( 'WUT_Utils', 'auto_excerption' ), 10 );
		}

		// add custom code.
		add_action( 'wp_head', array( $this->utils, 'inject_to_head' ) );
		add_action( 'wp_footer', array( $this->utils, 'inject_to_footer' ) );
		add_action( 'wp_head', array( 'WUT_Utils', 'add_meta_info' ) );

		if ( is_admin() ) {
			// add admin menus.
			$wut_admin = new WUT_Admin();
			$wut_admin->register_admin_entry();

			// add word count.
			add_filter( 'manage_posts_columns', array( $this->utils, 'add_wordcount_manage_columns' ) );
			add_filter( 'manage_pages_columns', array( $this->utils, 'add_wordcount_manage_columns' ) );
			add_action( 'manage_posts_custom_column', array( $this->utils, 'display_wordcount' ) );
			add_action( 'manage_pages_custom_column', array( $this->utils, 'display_wordcount' ) );
			add_action( 'admin_head', array( $this->utils, 'set_column_width' ) );

			// register category chooser components.
			add_action( 'wp_ajax_wut_category_chooser', array( 'WUT_Category_Chooser', 'show_category_tree' ) );
		}

		// Add related posts list to end of a post or page.
		add_filter( 'wp_link_pages', array( 'WUT_Utils', 'display_related_posts' ), 10, 2 );

		// Register uninstall feature.
		register_uninstall_hook( $this->root_dir . '/wordpress-ultimate-toolkit.php', array( 'WUT_Option_Manager', 'delete_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_static_files' ) );
	}

	/**
	 * Register new admin component Category Chooser to help choose exclueded categories easily.
	 *
	 * @return void
	 */
	public function register_static_files() {
		// Register static files.
		wp_register_style( 'jstree', $this->root_url . '/static/themes/default/style.min.css', array(), '3.3.11' );
		wp_register_script( 'jstree', $this->root_url . '/static/jstree.min.js', array( 'jquery' ), '3.3.11', true );
		wp_register_style( 'wut-category-chooser', $this->root_url . '/static/wut-category-chooser.css', array(), '1.0.2' );
		wp_register_script( 'wut-category-chooser', $this->root_url . '/static/wut-category-chooser.js', array( 'jstree' ), '1.0.2', true );
	}

	/**
	 * Global helpers: Output debug log.
	 *
	 * @return void
	 */
	public static function log() {
		if ( ! WP_DEBUG ) {
			return;
		}

		$args = func_get_args();
		$msg  = '';
		foreach ( $args as $arg ) {
			if ( is_string( $arg ) || is_numeric( $arg ) ) {
				$msg .= $arg;
			} else {
				$msg .= var_export( $arg, true );
			}
		}

		$trace = debug_backtrace();
		$file  = basename( $trace[0]['file'] );
		$line  = $trace[0]['line'];
		$func  = $trace[1]['function'];

		error_log( "[$file][$func][$line]:" . $msg );
	}
}
