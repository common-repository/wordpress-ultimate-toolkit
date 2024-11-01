<?php
/**
 * Features other than widgets.
 *
 * @package WordPress_Ultimate_Toolkit
 */

/**
 * Utils class.
 */
class WUT_Utils {

	/**
	 * Constructor function
	 */
	public function __construct() {
	}

	/**
	 * The auto excerption method.
	 *
	 * This method will hook to `the_content` filter and `the_excerpt` filter.
	 * I found that some of themes will not use the_excerpt() template tag in
	 * home page, like `twentyfifteen`. `the_content()` is used instead.
	 *
	 * When `the_content()` is used, and no <!-- wp:more --> tag is inserted,
	 * WordPress will not excerpt your posts on home page. In this situation,
	 * I'll hook `the_content` filter, try to auto excerpt the content of your
	 * post.
	 *
	 * When `the_excerpt()` is used, and author had not typed in any digest
	 * manually, $post->post_excerpt will be empty, nothing will be output.
	 *
	 * This feature will fix these two situation and give a consistent output.
	 *
	 * @see the_excerpt()
	 * @see the_content()
	 *
	 * @param string $content This argument will be the content or post_excerpt.
	 * @return string
	 */
	public static function auto_excerption( $content ) {
		if ( ! is_home() ) {
			return $content;
		}

		global $post;
		$excerpt = '';
		$options = WUT_Option_Manager::me()->get_options_by_key( WUT_Option_Manager::SUBKEY_EXCERPTION );

		// Custom excerpt will be first priority.
		if ( ! empty( $post->post_excerpt ) ) {
			$excerpt = $post->post_excerpt . '<br/>';
		} else {
			// If the content contains <!--more--> tag, use 'teaser' as excerpt.
			if ( preg_match( '/<!--more(.*?)?-->/', $post->post_content, $matches ) ) {
				// This api will automatic trim the content before more tag.
				$excerpt = get_the_content( '', false, $post ) . '<br/>';
			} else { // else, trim content by paragraph and words limit.
				$text = strip_shortcodes( $post->post_content );
				if ( has_blocks( $text ) ) {
					$text = excerpt_remove_blocks( $text );
				}
				$text = str_replace( ']]>', ']]&gt;', $text );
				$p8s  = array_filter( explode( "\n", $text ) );

				$num = 0;
				$len = count( $p8s );
				foreach ( $p8s as $p ) {
					$excerpt .= $p . "\n\n";
					$num ++;
					if ( ( mb_strlen( $excerpt, 'UTF-8' ) >= $options['words'] )
						|| ( $num >= min( $len, $options['paragraphs'] ) ) ) {
						break;
					}
				}

				$excerpt = force_balance_tags( substr( $excerpt, 0, -2 ) );
			}
		}

		// add tips.
		$tips = '';
		if ( mb_strlen( $excerpt, 'UTF-8' ) < mb_strlen( $post->post_content, 'UTF-8' ) ) {
			$title     = wp_strip_all_tags( get_the_title() );
			$total_num = self::words_count(
				preg_replace(
					'/\s/',
					'',
					html_entity_decode( wp_strip_all_tags( $post->post_content ) )
				)
			);
			$tips      = str_replace(
				array( '%permalink%', '%title%', '%total_words%' ),
				array( get_permalink(), $title, $total_num ),
				stripcslashes( $options['tip_template'] )
			);
		}
		return $excerpt . $tips;

	}

	protected function _select_code_snippets( $hook ) {
		$codesnippets = WUT_Option_Manager::me()->get_options_by_key( WUT_Option_Manager::SUBKEY_CUSTOM_CODE );
		if ( ! is_array( $codesnippets ) || empty( $codesnippets ) ) {
			return '';
		}
		$codetoprint = '';

		if ( 'wp_head' === $hook ) :
			foreach ( $codesnippets as $cs ) {
				if ( ( isset( $cs['hookto'] ) && 'wp_head' === $cs['hookto'] )
					|| ( 'wp_head' === $cs['hook'] ) ) {
					$codetoprint .= $cs['source'];
				}
			}
		elseif ( 'wp_footer' === $hook ) :
			foreach ( $codesnippets as $cs ) {
				if ( ( isset( $cs['hookto'] ) && 'wp_footer' === $cs['hookto'] )
					|| ( 'wp_footer' === $cs['hook'] ) ) {
					$codetoprint .= $cs['source'];
				}
			}
		endif;
		return $codetoprint;
	}

	public function inject_to_head() {
		echo "\n\n <!--This Piece of Code is Injected by WUT Custom Code-->\n";
		echo $this->_select_code_snippets( 'wp_head' );
		echo "\n<!--The End of WUT Custom Code-->\n";
	}

	public function inject_to_footer() {
		echo "\n\n <!--This Piece of Code is Injected by WUT Custom Code-->\n";
		echo $this->_select_code_snippets( 'wp_footer' );
		echo "\n<!--The End of WUT Custom Code-->\n";
	}

	public function add_wordcount_manage_columns( $post_columns ) {
		$post_columns['wordcount'] = __( 'Words', 'wordpress-ultimate-toolkit' );
		return $post_columns;
	}

	/**
	 * Display a column contains post length.
	 *
	 * @param string $column_name The name of control column.
	 * @return void
	 */
	public function display_wordcount( $column_name ) {
		global $post;
		if ( 'wordcount' === $column_name ) {
			$content = wp_strip_all_tags( $post->post_content );
			$len     = self::words_count( $content );
			$style   = '';
			if ( $len > 1000 ) {
				$style = 'color:#00f;font-weight:bold';
			}
			if ( $len > 2000 ) {
				$style = 'color:#f00;font-weight:bold';
			}
			echo '<span style="' , $style , '">' , $len , '</span>';
		}
	}

	/**
	 * Control the column width.
	 *
	 * @return void
	 */
	public function set_column_width() {
		?>
		<style type="text/css">
			.column-wordcount { width:6%; }
		</style>
		<?php
	}

	/**
	 * Count the words in a string.
	 *
	 * This function treat a multibyte charactor as 1, and a English like
	 * language WORD as 1.
	 *
	 * So, this function can count mixed Chinese and English relatively exactly.
	 *
	 * @since 1.0.0
	 * @param string $content The content to stats its length.
	 * @return int the number of words in this string
	 * @access private
	 */
	public static function words_count( $content ) {
		$matches = array();
		preg_match_all( '~[-a-z0-9,.!?\'":;@/ ()\+\_]+~im', $content, $matches );
		$content       = preg_replace( '~[-a-z0-9,.!?\'":;@/ ()\+\_]+~im', '', $content );
		$ch_char_count = mb_strlen( trim( $content ) );
		$en_word_count = 0;
		foreach ( $matches[0] as $str ) {
			$str = trim( $str, ',.!?;:@ \'"/()' );
			if ( ! empty( $str ) ) {
				$temp           = explode( ' ', $str );
				$en_word_count += count( $temp );
			}
		}
		return $ch_char_count + $en_word_count;
	}

	/**
	 * This will be hooked to wp_link_pages filter
	 *
	 * @param string $output HTML output of paginated posts' page links.
	 * @param array  $args   An array of arguments.
	 * @return string
	 */
	public static function display_related_posts( $output, $args = array() ) {
		if ( ! is_single() ) {
			return $output;
		}

		$options = WUT_Option_Manager::me()->get_options_by_key( WUT_Option_Manager::SUBKEY_RELATED_LIST );

		if ( ! $options['enabled'] ) {
			return $output;
		}

		$tag_args = array(
			'limit'      => $options['number'],
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
			'echo'       => false,
		);

		// TODO: Related psots list should support feature image layout.

		$html = '';
		if ( ! empty( $options['title'] ) ) {
			$html = $options['title'];
		}
		$html .= '<ul>' . wut_related_posts( $tag_args ) . '</ul>';
		return $output . $html;
	}

	public static function add_meta_info() {
		if ( ! is_home() ) {
			return;
		}

		$options = WUT_Option_Manager::me()->get_options_by_key( WUT_Option_Manager::SUBKEY_META_INFO );

		if ( false === $options['enabled'] ) {
			return;
		}

		if ( ! empty( $options['site_description'] ) ) {
			?>
			<meta name="description" content="<?php echo esc_attr( $options['site_description'] ); ?>"/>
			<?php
		}

		if ( ! empty( $options['site_keywords'] ) ) {
			?>
			<meta name="keywords" content="<?php echo esc_attr( $options['site_keywords'] ); ?>" />
			<?php
		}

	}
}

