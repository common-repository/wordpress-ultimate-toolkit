<?php
/**
 * Widget: WUT_Widget_Related_Posts class.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage widgets
 */

/**
 * Define a widget show related posts in single post and page.
 *
 * This related posts are calculated by taxonomy data.
 */
class WUT_Widget_Related_Posts extends WP_Widget {

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
			'description'                 => __( 'NEW! List the related posts in SINGLE POST PAGE ONLY.', 'wordpress-ultimate-toolkit' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( '', __( 'WUT-Related Posts', 'wordpress-ultimate-toolkit' ), $widget_ops );
		$this->helper = new WUT_Form_Helper( $this );
	}

	/**
	 * Printed related posts list on single page.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance User set arguments.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// related posts list is shown only in single page.
		if ( ! is_single() ) {
			return;
		}

		$title    = $instance['title'];
		$tag_args = array(
			'limit'      => $instance['number'],
			'before'     => '<li>',
			'after'      => '</li>',
			'type'       => 'post',
			'skips'      => '',
			'leastshare' => true,
			'password'   => 'hide',
			'orderby'    => 'post_date',
			'order'      => 'DESC',
			'xformat'    => '<a href="%permalink%" title="View:%title%(Posted on %postdate%)">%title%</a>(%commentcount%)',
			'none'       => __( 'No related posts.', 'wordpress-ultimate-toolkit' ),
			'echo'       => 0,
		);

		$this->helper->print_widget( $args, $title, '<ul>' . wut_related_posts( $tag_args ) . '</ul>' );
	}

	/**
	 * Update arguments of this widget.
	 *
	 * @param array $new_instance New submitted arguments.
	 * @param array $old_instance Original arguments.
	 * @return array Filtered arguments to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                       = $old_instance;
		$instance['title']              = $this->helper->default( $new_instance, 'title', 'string', '' );
		$instance['number']             = $this->helper->default( $new_instance, 'number', 'uint', $old_instance['number'] );
		$instance['show_comment_count'] = $this->helper->default( $new_instance, 'show_comment_count', 'bool', $old_instance['show_comment_count'] );
		$instance['show_date']          = $this->helper->default( $new_instance, 'show_date', 'bool', $old_instance['show_date'] );
		$instance['date_front']         = $this->helper->default( $new_instance, 'date_front', 'bool', $old_instance['date_front'] );
		$instance['date_format']        = $this->helper->default( $new_instance, 'date_format', 'string', $old_instance['date_format'] );
		$instance['custom_format']      = $this->helper->default( $new_instance, 'custom_format', 'string', $old_instance['custom_format'] );

		return $new_instance;
	}

	/**
	 * Widget panel.
	 *
	 * @param array $instance Current arguments.
	 * @return void
	 */
	public function form( $instance ) {
		$title              = $this->helper->default( $instance, 'title', 'string', '' );
		$number             = $this->helper->default( $instance, 'number', 'uint', 5 );
		$show_comment_count = $this->helper->default( $instance, 'show_comment_count', 'bool', true );
		$show_date          = $this->helper->default( $instance, 'show_date', 'bool', false );
		$date_front         = $this->helper->default( $instance, 'date_front', 'bool', false );
		$site_date_format   = get_option( 'date_format' );
		$date_format        = $this->helper->default( $instance, 'date_format', 'string', $site_date_format, false );
		$custom_format      = $this->helper->default( $instance, 'custom_format', 'string', $site_date_format );

		$this->helper->text( 'title', $title, __( 'Title:' ) );
		$this->helper->text( 'number', $number, __( 'Number of posts to show:' ), 'number', 'tiny-text' );
		$this->helper->checkbox( 'show_comment_count', $show_comment_count, __( 'Show comment count:', 'wordpress-ultimate-toolkit' ) );
		$this->helper->checkbox( 'show_date', $show_date, __( 'Display post date?', 'wordpress-ultimate-toolkit' ) );
		$this->helper->checkbox( 'date_front', $date_front, __( 'Show date before title?', 'wordpress-ultimate-toolkit' ) );
		$this->helper->date_format_chooser(
			array(
				'date_format_property'   => 'date_format',
				'date_format_value'      => $date_format,
				'date_format_default'    => $site_date_format,
				'custom_format_property' => 'custom_format',
				'custom_format_value'    => $custom_format,
			)
		);
	}
}
