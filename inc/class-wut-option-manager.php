<?php
/**
 * Options manager.
 *
 * @package WordPress_Ultimate_Toolkit
 */

/**
 * Option manager.
 *
 * This class is used to manage options of this plugin. The class should not
 * depend on any admin classes because of is will be used in front end of WP.
 */
class WUT_Option_Manager {

	const OPTION_KEY = 'wordpress-ultimate-toolkit-options';

	const SUBKEY_EXCERPTION = 'excerpt';

	const SUBKEY_RELATED_LIST = 'admin-related-posts-list';

	const SUBKEY_CUSTOM_CODE = 'customcode';

	const SUBKEY_META_INFO = 'metainfo';

	/**
	 * The options array.
	 *
	 * @var array Options key value table.
	 */
	protected $options = array();

	/**
	 * Default options.
	 *
	 * @var array
	 */
	public $defaults = array();

	/**
	 * Singleton handler
	 *
	 * @var WUT_Option_Manager
	 */
	protected static $me;

	/**
	 * Get an instance of this class.
	 *
	 * @return WUT_Option_Manager
	 */
	public static function me() {
		if ( is_null( self::$me ) ) {
			self::$me = new WUT_Option_Manager();
		}
		return self::$me;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_KEY );
		if ( empty( $this->options ) ) {
			$this->options = $this->get_defaults();
		}
	}

	/**
	 * Return default options.
	 *
	 * @param string $key The sub key of options.
	 * @return array
	 */
	public function get_defaults( $key = '' ) {
		$defaults = array(
			self::SUBKEY_CUSTOM_CODE  => array(),
			self::SUBKEY_EXCERPTION   => array(
				'enabled'      => true,
				'paragraphs'   => 3,
				'words'        => 250,
				'tip_template' => '<br/><br/><span class="readmore"><a href="%permalink%" title="%title%">Continue Reading--%total_words% words totally</a></span>',
			),
			self::SUBKEY_RELATED_LIST => array(
				'enabled'            => false,
				'title'              => __( 'Related Posts', 'wordpress-ultimate-toolkit' ),
				'number'             => 5,
				'show_comment_count' => true,
			),
			self::SUBKEY_META_INFO    => array(
				'enabled'          => true,
				'site_description' => '',
				'site_keywords'    => '',
			),
		);

		if ( ! empty( $key ) ) {
			return $defaults[ $key ];
		}
		return $defaults;
	}

	/**
	 * Retrieve a part of options by key.
	 * This method will always return value.
	 *
	 * @param string $key The option key.
	 * @return array
	 */
	public function get_options_by_key( $key ) {
		if ( isset( $this->options[ $key ] )
			&& ! empty( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
		} else {
			return $this->get_defaults( $key );
		}
	}

	/**
	 * Update a part of options by key.
	 *
	 * @param string $key The option key.
	 * @param array  $new New option value.
	 * @return void
	 */
	public function set_options_by_key( $key, $new ) {
		$this->options[ $key ] = $new;
	}

	/**
	 * Register defaults which could be retrieved by get_defaults().
	 *
	 * @param string $key The option key.
	 * @param array  $value The defaults.
	 * @return void
	 */
	public function register_defaults( $key, $value ) {
		$this->defaults[ $key ] = $value;
	}

	/**
	 * Save all options.
	 * Three ways will cause failure:
	 *   1. empty options.
	 *   2. no value changed.
	 *   3. database error.
	 *
	 * @return bool Return true if options saved successfully.
	 */
	public function save_options() {
		if ( isset( $this->options['hide-pages'] ) ) {
			unset( $this->options['hide-pages'] );
		}
		delete_option( 'wut-widget-recent-posts' );
		delete_option( 'wut-widget-recent-comments' );
		delete_option( 'wut-widget-related-posts' );
		delete_option( 'wut-widget-active-commentators' );
		delete_option( 'wut-widget-recent-commentators' );
		if ( isset( $this->options['other'] ) ) {
			unset( $this->options['other'] );
		}
		if ( isset( $this->options['widgets'] ) ) {
			unset( $this->options['widgets'] );
		}
		return update_option( 'wordpress-ultimate-toolkit-options', $this->options );
	}

	/**
	 * Delete all options to clean the site database.
	 *
	 * @return void
	 */
	public static function delete_options() {
		delete_option( 'wordpress-ultimate-toolkit-options' );
		delete_option( 'wut-widget-recent-posts' );
		delete_option( 'wut-widget-random-posts' );
		delete_option( 'wut-widget-related-posts' );
		delete_option( 'wut-widget-posts-by-category' );
		delete_option( 'wut-widget-most-commented-posts' );
		delete_option( 'wut-widget-recent-comments' );
		delete_option( 'wut-widget-active-commentators' );
		delete_option( 'wut-widget-recent-commentators' );
		$widget = new WUT_Widget_Recent_Posts();
		delete_option( $widget->option_name );
		$widget = new WUT_Widget_Recent_Comments();
		delete_option( $widget->option_name );
		$widget = new WUT_Widget_Most_Viewed_Posts();
		delete_option( $widget->option_name );
		$widget = new WUT_Widget_Related_Posts();
		delete_option( $widget->option_name );
	}
}
