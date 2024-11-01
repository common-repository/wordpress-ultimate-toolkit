<?php
/**
 * Widget: WUT_Widget_Recent_Comments class.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage widgets
 */

/**
 * Define a widget to show recent comments on index, single, and page.
 */
class WUT_Widget_Recent_Comments extends WP_Widget {

	/**
	 * Help to create form control.
	 *
	 * @var WUT_Form_Helper
	 */
	protected $helper;

	/**
	 * To set the name and description of this widget.
	 */
	public function __construct() {
		$widget_ops = array(
			'description'                 => __( 'NEW! List recent comments.', 'wordpress-ultimate-toolkit' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( '', __( 'WUT-Recent Comments', 'wordpress-ultimate-toolkit' ), $widget_ops );
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
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$avatar_size = $this->helper->default( $instance, 'avatar_size', 'uint', 16 );
		$tag_args    = array(
			'limit'       => $instance['number'],
			'before'      => '<li>',
			'after'       => '</li>',
			'length'      => 50,
			'posttype'    => 'post',
			'commenttype' => 'comment',
			'skipusers'   => '',
			'avatarsize'  => $avatar_size,
			'none'        => __( 'No comments.', 'wordpress-ultimate-toolkit' ),
			'password'    => 'hide',
			'xformat'     => '<a class="commentator" href="%permalink%" >%commentauthor%</a>',
			'echo'        => 0,
		);

		$show_avatar = $this->helper->default( $instance, 'show_avatar', 'bool', false );
		if ( $show_avatar ) {
			$tag_args['xformat'] = '%gravatar%' . $tag_args['xformat'];
		}

		$show_content = $this->helper->default( $instance, 'show_content', 'bool', true );
		if ( $show_content ) {
			$tag_args['xformat'] .= ' : %commentexcerpt%';
		} else {
			$tag_args['xformat'] .= __( ' on ', 'wordpress-ultimate-toolkit' ) . '<<%posttile>>';
		}

		$this->helper->print_widget( $args, $title, '<ul>' . wut_recent_comments( $tag_args ) . '</ul>' );
	}

	/**
	 * Update the settings of this instance.
	 *
	 * @param array $new_instance New set settings.
	 * @param array $old_instance Original set settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = sanitize_text_field( $new_instance['title'] );
		$instance['number']       = intval( $new_instance['number'] );
		$instance['show_content'] = (bool) $new_instance['show_content'];
		$instance['show_avatar']  = (bool) $new_instance['show_avatar'];
		$instance['avatar_size']  = intval( $new_instance['avatar_size'] );
		return $new_instance;
	}

	/**
	 * The widget panel in admin page.
	 *
	 * @param array $instance The settings of this widget instance.
	 */
	public function form( $instance ) {
		$title        = $this->helper->default( $instance, 'title', 'string', '' );
		$number       = $this->helper->default( $instance, 'number', 'uint', 5 );
		$show_content = $this->helper->default( $instance, 'show_content', 'bool', true );
		$show_avatar  = $this->helper->default( $instance, 'show_avatar', 'bool', false );
		$avatar_size  = $this->helper->default( $instance, 'avatar_size', 'uint', 16 );
		$this->helper->text( 'title', $title, __( 'Title:' ) );
		$this->helper->text( 'number', $number, __( 'Number of comments to show:', 'wordpress-ultimate-toolkit' ), 'number', 'tiny-text' );
		$this->helper->checkbox( 'show_content', $show_content, __( 'Display comment content?', 'wordpress-ultimate-toolkit' ) );
		$this->helper->checkbox( 'show_avatar', $show_avatar, __( 'Display avatar?', 'wordpress-ultimate-toolkit' ) );
		$this->helper->text( 'avatar_size', $avatar_size, __( 'The size of avatar: ', 'wordpress-ultimate-toolkit' ), 'number', 'tiny-text' );
	}
}
