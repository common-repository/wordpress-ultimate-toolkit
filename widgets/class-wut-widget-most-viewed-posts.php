<?php
/**
 * Widget: WUT_Widget_Most_Viewed_Posts class.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage widgets
 */

/**
 * Define a widget show most viewed posts in home, post and page.
 *
 * This plugin need WP PostViews plugin installed first.
 *
 * @link https://wordpress.org/plugins/wp-postviews/
 */
class WUT_Widget_Most_Viewed_Posts extends WP_Widget {

	/**
	 * The helper to build widget form.
	 *
	 * @var WUT_Form_Helper
	 */
	protected $helper;
	/**
	 * Set the name and description of the widget.
	 */
	public function __construct() {
		$widget_ops = array(
			'description'                 => __( 'NEW! List most viewed posts. This need WP Postviews installed first.', 'wordpress-ultimate-toolkit' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( '', __( 'WUT-Most Viewed Posts', 'wordpress-ultimate-toolkit' ), $widget_ops );
		$this->helper = new WUT_Form_Helper( $this );
	}

	/**
	 * Generate this widget.
	 *
	 * @param array $args Inherit from parent class.
	 * @param array $instance Settings of this widget instance.
	 */
	public function widget( $args, $instance ) {
		$title = $instance['title'];

		$tag_args = array(
			'limit'      => $instance['number'],
			'offset'     => 0,
			'before'     => '<li>',
			'after'      => '</li>',
			'type'       => 'post', // 'post' or 'page' or 'both'.
			'skips'      => '', // comma seperated post_ID list.
			'none'       => 'No Posts.', // tips to show when results is empty.
			'password'   => 'hide', // show password protected post or not.
			'xformat'    => '<a href="%permalink%" title="View:%title%(Posted on %postdate%)">%title%</a>',
			'time_range' => $instance['time_range'] < 0 ? $instance['custom_range'] : $instance['time_range'],
			'echo'       => 0,
		);

		if ( $instance['show_view_count'] ) {
			$tag_args['xformat'] .= ' (%viewcount% views)';
		}

		if ( $instance['show_date'] ) {
			if ( $instance['date_front'] ) {
				$tag_args['xformat'] = '%postdate% ' . $tag_args['xformat'];
			} else {
				$tag_args['xformat'] .= ' %postdate%';
			}
		}

		if ( isset( $instance['date_format'] ) ) {
			if ( 'custom' === $instance['date_format'] ) {
				$tag_args['date_format'] = $instance['custom_format'];
			} else {
				$tag_args['date_format'] = $instance['date_format'];
			}
		} else {
			$tag_args['date_format'] = get_option( 'date_format' );
		}

		$this->helper->print_widget( $args, $title, '<ul>' . wut_most_viewed_posts( $tag_args ) . '</ul>' );
	}

	/**
	 * Update the settings of this instance.
	 *
	 * @param array $new_instance New set settings.
	 * @param array $old_instance Original set settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['title']           = sanitize_text_field( $new_instance['title'] );
		$instance['number']          = intval( $new_instance['number'] );
		$instance['show_date']       = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['date_front']      = isset( $new_instance['date_front'] ) ? (bool) $new_instance['date_front'] : false;
		$instance['excerpt_words']   = intval( $new_instance['excerpt_words'] );
		$instance['time_range']      = intval( $new_instance['time_range'] );
		$instance['custom_range']    = intval( $new_instance['custom_range'] );
		$instance['show_view_count'] = isset( $new_instance['show_view_count'] ) ? (bool) $new_instance['show_view_count'] : false;
		$instance['date_format']     = sanitize_text_field( $new_instance['date_format'] );
		$instance['custom_format']   = sanitize_text_field( $new_instance['custom_format'] );
		return $instance;
	}

	/**
	 * The widget panel in admin page.
	 *
	 * @param array $instance The settings of this widget instance.
	 */
	public function form( $instance ) {
		$title            = $this->helper->default( $instance, 'title', 'string', '' );
		$number           = $this->helper->default( $instance, 'number', 'uint', 10 );
		$show_date        = $this->helper->default( $instance, 'show_date', 'bool', false );
		$date_front       = $this->helper->default( $instance, 'date_front', 'bool', false );
		$excerpt_words    = $this->helper->default( $instance, 'excerpt_words', 'uint', 15 );
		$time_range       = $this->helper->default( $instance, 'time_range', 'int', 365 );
		$custom_range     = $this->helper->default( $instance, 'custom_range', 'uint', 365 );
		$show_view_count  = $this->helper->default( $instance, 'show_view_count', 'bool', true );
		$site_date_format = get_option( 'date_format' );
		$date_format      = $this->helper->default( $instance, 'date_format', 'string', $site_date_format );
		$custom_format    = $this->helper->default( $instance, 'custom_format', 'string', $site_date_format );

		$this->helper->text( 'title', $title, __( 'Title:' ) );
		$this->helper->text( 'number', $number, __( 'Number of posts to show:' ), 'number', 'tiny-text' );
		$this->helper->text( 'excerpt_words', $excerpt_words, __( 'Maximum title length:', 'wordpress-ultimate-toolkit' ), 'number', 'tiny-text' );
		$this->helper->checkbox( 'show_date', $show_date, __( 'Display post date?', 'wordpress-ultimate-toolkit' ) );
		$this->helper->checkbox( 'date_front', $date_front, __( 'Put date in front of post title?', 'wordpress-ultimate-toolkit' ) );
		?>
		<p>
			<span><?php _e( 'Time frame of posts:', 'wordpress-ultimate-toolkit' ); ?></span><br/>
			<label>
				<input type="radio" name="<?php echo $this->get_field_name( 'time_range' ); ?>"<?php checked( $time_range, 7 ); ?> value="7"/>
				<span><?php _e( 'Past Week', 'wordpress-ultimate-toolkit' ); ?></span>
			</label><br/>
			<label>
				<input type="radio" name="<?php echo $this->get_field_name( 'time_range' ); ?>"<?php checked( $time_range, 30 ); ?> value="30"/>
				<span><?php _e( 'Past Month', 'wordpress-ultimate-toolkit' ); ?></span>
			</label><br/>
			<label>
				<input type="radio" name="<?php echo $this->get_field_name( 'time_range' ); ?>"<?php checked( $time_range, 365 ); ?> value="365"/>
				<span><?php _e( 'Past Year', 'wordpress-ultimate-toolkit' ); ?></span>
			</label><br/>
			<label>
				<input type="radio" name="<?php echo $this->get_field_name( 'time_range' ); ?>"<?php checked( $time_range, -1 ); ?> value="-1"/>
				<span><?php _e( 'Custom', 'wordpress-ultimate-toolkit' ); ?></span>
				<input class="small-text" id="<?php echo $this->get_field_id( 'custom_range' ); ?>" name="<?php echo $this->get_field_name( 'custom_range' ); ?>" type="number" step="1" min="1" value="<?php echo $custom_range; ?>" size="4" />
			</label>
		</p>
		<?php

		$this->helper->checkbox( 'show_view_count', $show_view_count, __( 'Show view count?', 'wordpress-ultimate-toolkit' ) );

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
