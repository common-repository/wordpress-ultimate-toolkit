<?php
/**
 * Widget: WUT_Widget_Recent_Posts class.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage widgets
 */

/**
 * Define a widget show recent posts in home, post and page.
 */
class WUT_Widget_Recent_Posts extends WP_Widget {

	/**
	 * The form helper.
	 *
	 * @var WUT_Form_Helper the form helper to generate the form control.
	 */
	protected $helper;

	/**
	 * Set the name and description of the widget.
	 */
	public function __construct() {
		$widget_ops = array(
			'description'                 => __( 'NEW! List the recent posts and provide some advanced options', 'wordpress-ultimate-toolkit' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( '', __( 'WUT-Recent Posts', 'wordpress-ultimate-toolkit' ), $widget_ops );
		$this->helper = new WUT_Form_Helper( $this );
	}

	/**
	 * Generate this widget.
	 *
	 * @param array $args Inherit from parent class.
	 * @param array $instance Settings of this widget instance.
	 */
	public function widget( $args, $instance ) {
		$title            = $instance['title'];
		$title            = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$site_date_format = get_option( 'date_format' );
		$date_format      = $this->helper->default( $instance, 'date_format', 'string', $site_date_format );
		if ( 'custom' === $date_format ) {
			$date_format = $instance['custom_format'];
		}

		$tag_args = array(
			'limit'       => $instance['number'],
			'date_format' => $date_format,
			'xformat'     => '<a href="%permalink%" title="View:%title%(Posted on %postdate%)">%title%</a>',
			'echo'        => 0,
		);

		if ( is_singular() ) {
			$tag_args['skips'] = get_the_ID();
		}

		if ( 'posts' === get_option( 'show_on_front' ) && is_home() ) {
			$page = get_query_var( 'paged' );
			if ( 0 === $page ) {
				$tag_args['offset'] = get_option( 'posts_per_page' );
			}
		}

		$instance['exclude_categories'] = $this->helper->default( $instance, 'exclude_categories', 'string', '' );
		$instance['show_date']          = $this->helper->default( $instance, 'show_date', 'bool', false );
		$instance['show_comment_count'] = $this->helper->default( $instance, 'show_comment_count', 'bool', false );
		$instance['date_before_title']  = $this->helper->default( $instance, 'date_before_title', 'bool', false );
		$tag_args['xformat']           .= ( $instance['show_comment_count'] ? ' (%commentcount%)' : '' );
		$tag_args['exclude_categories'] = $instance['exclude_categories'];
		if ( $instance['show_date'] ) {
			if ( $instance['date_before_title'] ) {
				$tag_args['xformat'] = '<span class="post-date">%postdate%</span>&nbsp;' . $tag_args['xformat'];
			} else {
				$tag_args['xformat'] .= '&nbsp;<span class="post-date">%postdate%</span>';
			}
		}

		$this->helper->print_widget( $args, $title, '<ul>' . wut_recent_posts( $tag_args ) . '</ul>' );
	}

	/**
	 * Update the settings of this instance.
	 *
	 * @param array $new_instance New set settings.
	 * @param array $old_instance Original set settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                       = $old_instance;
		$instance['title']              = $this->helper->default( $new_instance, 'title', 'string', '' );
		$instance['number']             = $this->helper->default( $new_instance, 'number', 'uint', 5 );
		$instance['show_date']          = $this->helper->default( $new_instance, 'show_date', 'bool', false );
		$instance['date_before_title']  = $this->helper->default( $new_instance, 'date_before_title', 'bool', false );
		$site_date_format               = get_option( 'date_format' );
		$instance['date_format']        = $this->helper->default( $new_instance, 'date_format', 'string', $site_date_format );
		$instance['custom_format']      = $this->helper->default( $new_instance, 'custom_format', 'string', $site_date_format );
		$instance['show_comment_count'] = $this->helper->default( $new_instance, 'show_comment_count', 'bool', false );
		$instance['exclude_categories'] = $this->helper->default( $new_instance, 'exclude_categories', 'string', '' );
		return $instance;
	}

	/**
	 * The widget panel in admin page.
	 *
	 * @param array $instance The settings of this widget instance.
	 */
	public function form( $instance ) {
		$chooser = new WUT_Category_Chooser();
		$chooser->enqueue_scripts();

		$title              = $this->helper->default( $instance, 'title', 'string', '' );
		$number             = $this->helper->default( $instance, 'number', 'uint', get_option( 'posts_per_page' ) );
		$show_date          = $this->helper->default( $instance, 'show_date', 'bool', false );
		$date_before_title  = $this->helper->default( $instance, 'date_before_title', 'bool', false );
		$site_date_format   = get_option( 'date_format' );
		$date_format        = $this->helper->default( $instance, 'date_format', 'string', $site_date_format );
		$custom_format      = $this->helper->default( $instance, 'custom_format', 'string', $site_date_format );
		$show_comment_count = $this->helper->default( $instance, 'show_comment_count', 'bool', false );
		$exclude_categories = $this->helper->default( $instance, 'exclude_categories', 'string', '' );
		$this->helper->text( 'title', $title, __( 'Title:' ) );
		$this->helper->text( 'number', $number, __( 'Number of posts to show:' ), 'number', 'tiny-text' );
		$this->helper->checkbox( 'show_date', $show_date, __( 'Display post date?' ) );
		$this->helper->checkbox( 'date_before_title', $date_before_title, __( 'Show date before title?', 'wordpress-ultimate-toolkit' ) );
		$this->helper->date_format_chooser(
			array(
				'date_format_property'   => 'date_format',
				'date_format_value'      => $date_format,
				'date_format_default'    => $site_date_format,
				'custom_format_property' => 'custom_format',
				'custom_format_value'    => $custom_format,
			)
		);
		$this->helper->checkbox( 'show_comment_count', $show_comment_count, __( 'Display comment count?', 'wordpress-ultimate-toolkit' ) );
		$this->helper->text( 'exclude_categories', $exclude_categories, __( 'Category IDs to exclude:', 'wordpress-ultimate-toolkit' ), 'text', 'medium-text' );
		$chooser->inject_link( $this->get_field_id( 'exclude_categories' ) );
	}
}
