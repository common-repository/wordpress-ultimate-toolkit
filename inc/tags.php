<?php
/**
 * Template tags.
 *
 * @package WordPress_Ultimate_Toolkit
 */

/**
 * Template tag: Recent posts.
 *
 * @param array $args arguments to control the template tag.
 */
function wut_recent_posts( $args = array() ) {
	$defaults = array(
		'limit'       => 5,
		'offset'      => 0,
		'before'      => '<li>',
		'after'       => '</li>',
		'skips'       => '',
		'none'        => __( 'No Posts.', 'wordpress-ultimate-toolkit' ),
		'password'    => 'hide',
		'orderby'     => 'post_date',
		'xformat'     => '<a href="%permalink%" title="View:%title%(Posted on %postdate%)">%title%</a>(%commentcount%)',
		'date_format' => '',
		'echo'        => 1,
	);
	$r        = wp_parse_args( $args, $defaults );

	$query = new WP_Query(
		array(
			'posts_per_page'      => $r['limit'],
			'offset'              => $r['offset'],
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'category__not_in'    => explode( ',', $r['exclude_categories'] ),
			'ignore_sticky_posts' => true,
			'post__not_in'        => array_filter( explode( ',', $r['skips'] ) ),
			'orderby'             => $r['orderby'],
			'has_password'        => ! 'hide' === $r['password'],
		)
	);

	$html = '';
	if ( ! $query->have_posts() ) {
		$html = $r['before'] . $r['none'] . $r['after'];
	} else {
		foreach ( $query->posts as $post ) {
			$record   = wut_private_render_template_by_post( $r['xformat'], $post, $r['date_format'] );
			$sanitize = apply_filters( 'wut_recent_post_item', $record, $post );
			$html    .= $r['before'] . $sanitize . $r['after'] . "\n";
		}
	}
	if ( $r['echo'] ) {
		wut_print_html( $html );
	} else {
		return $html;
	}
}

/**
 * Template tag print most viewed posts list.
 *
 * @param array $args Arguments array.
 * @return string|void
 */
function wut_most_viewed_posts( $args = array() ) {
	$defaults = array(
		'limit'    => 5,
		'offset'   => 0,
		'before'   => '<li>',
		'after'    => '</li>',
		'skips'    => '',
		'none'     => __( 'No Posts.', 'wordpress-ultimate-toolkit' ),
		'password' => 'hide',
		'xformat'  => '<a href="%permalink%" title="View:%title%(Posted on %postdate%)">%title%</a>(%viewcount%)',
		'echo'     => 1,
	);
	$r        = wp_parse_args( $args, $defaults );

	$date  = date_create()->sub( new DateInterval( 'P' . $r['time_range'] . 'D' ) );
	$query = new WP_Query(
		array(
			'posts_per_page'      => $r['limit'],
			'offset'              => $r['offset'],
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'post__not_in'        => array_filter( explode( ',', $r['skips'] ) ),
			'meta_key'            => 'views',
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'has_password'        => ! 'hide' === $r['password'],
			'date_query'          => array(
				array(
					'after' => $date->format( 'Y-m-d H:i:s' ),
				),
			),
		)
	);

	$html = '';
	if ( ! $query->have_posts() ) {
		$html = $r['before'] . $r['none'] . $r['after'];
	} else {
		foreach ( $query->posts as $post ) {
			$record   = wut_private_render_template_by_post(
				$r['xformat'],
				$post,
				$r['date_format'],
				function( $template ) use ( $post ) {
					return str_replace(
						'%viewcount%',
						get_post_meta( $post->ID, 'views', true ),
						$template
					);
				}
			);
			$sanitize = apply_filters( 'wut_most_viewed_item', $record, $post );
			$html    .= $r['before'] . $sanitize . $r['after'] . "\n";
		}
	}
	if ( $r['echo'] ) {
		wut_print_html( $html );
	} else {
		return $html;
	}
}

/**
 * Template tag to output ramdom posts.
 *
 * @param array $args Control info.
 * @return string
 */
function wut_random_posts( $args = array() ) {
	if ( ! isset( $args['orderby'] ) || empty( $args['orderby'] ) ) {
		$args['orderby'] = 'RAND(' . wp_rand() . ')';
	}
	return wut_recent_posts( $args );
}

/**
 * Template tag to output related posts.
 *
 * @param array $args Control arguments.
 */
function wut_related_posts( $args = array() ) {
	$defaults = array(
		'postid'     => false,
		'limit'      => 10,
		'offset'     => 0,
		'before'     => '<li>',
		'after'      => '</li>',
		'skips'      => '',
		'leastshare' => 1,
		'password'   => 'hide',
		'orderby'    => 'post_date',
		'order'      => 'DESC',
		'xformat'    => '<a href="%permalink%" title="View:%title%(Posted on %postdate%)">%title%</a>(%commentcount%)',
		'none'       => __( 'No posts.', 'wordpress-ultimate-toolkit' ),
		'echo'       => 1,
	);
	$r        = wp_parse_args( $args, $defaults );

	$r['password'] = 'hide' === $r['password'] ? 0 : 1;

	$post_ID = $r['postid'];
	if ( false === $post_ID ) {
		global $post;
		$post_ID = $post->ID;
	}
	$r['skips'] .= $post_ID;

	$tag_ids = array_map(
		function( $tag ) {
			return $tag->term_id;
		},
		wp_get_object_terms( $post_ID, 'post_tag' )
	);

	$query = new WP_Query(
		array(
			'posts_per_page'      => $r['limit'],
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'post__not_in'        => array_filter( explode( ',', $r['skips'] ) ),
			'orderby'             => $r['orderby'],
			'has_password'        => ! 'hide' === $r['password'],
			'tag__in'             => $tag_ids,
		)
	);

	$html = '';
	if ( ! $query->have_posts() ) {
		$html = $r['before'] . $r['none'] . $r['after'];
	} else {
		foreach ( $query->posts as $p ) {
			$record = wut_private_render_template_by_post( $r['xformat'], $p );
			$record = apply_filters( 'wut_related_post_item', $record, $p );
			$html  .= $r['before'] . $record . $r['after'] . "\n";
		}
	}
	if ( $r['echo'] ) {
		wut_print_html( $html );
	} else {
		return $html;
	}
}

/**
 * Template tag to display posts list in a category.
 *
 * @param array $args config.
 * @return string
 */
function wut_posts_by_category( $args = array() ) {
	$defaults = array(
		'postid'   => false,
		'orderby'  => 'rand',
		'order'    => 'asc',
		'before'   => '<li>',
		'after'    => '</li>',
		'limit'    => 5,
		'offset'   => 0,
		'skips'    => '',
		'password' => 'hide',
		'none'     => 'No Posts.',
		'xformat'  => '<a href="%permalink%" title="View:%title%(Posted on %postdate%)">%title%</a>(%commentcount%)',
		'echo'     => 1,
	);

	$r = wp_parse_args( $args, $defaults );

	if ( ! isset( $r['postid'] ) || ! $r['postid'] ) {
		global $post;
		$r['postid'] = $post->ID;
	}

	if ( empty( $r['skips'] ) ) {
		$r['skips'] = $r['postid'];
	} else {
		$r['skips'] .= ',' . $r['postid'];
	}

	$categories   = wp_get_object_terms( $r['postid'], 'category' );
	$category_ids = array();
	foreach ( $categories as $category ) {
		$category_ids[] = $category->term_taxonomy_id;
	}

	$query = new WP_Query(
		array(
			'posts_per_page'      => $r['limit'],
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'category__in'        => $category_ids,
			'post__not_in'        => array_filter( explode( ',', $r['skips'] ) ),
			'orderby'             => $r['orderby'],
			'has_password'        => ! 'hide' === $r['password'],
		)
	);

	$html = '';
	if ( ! $query->have_posts() ) {
		$html = $r['before'] . $r['none'] . $r['after'];
	} else {
		foreach ( $query->posts as $p ) {
			$record = wut_private_render_template_by_post( $r['xformat'], $p );
			$record = apply_filters( 'wut_same_classified_post_item', $record, $p );
			$html  .= $r['before'] . $record . $r['after'] . "\n";
		}
	}
	if ( $r['echo'] ) {
		wut_print_html( $html );
	} else {
		return $html;
	}
}

/**
 * Templdate tag to display most commented posts list.
 *
 * @param array $args config.
 * @return string
 */
function wut_most_commented_posts( $args = array() ) {
	if ( ! isset( $args['orderby'] ) || empty( $args['orderby'] ) ) {
		$args['orderby'] = 'comment_count';
	}
	return wut_recent_posts( $args );
}

/**
 * Template tag to display recent comments list.
 *
 * @param array $args Config.
 * @return string
 */
function wut_recent_comments( $args = array() ) {
	$defaults = array(
		'limit'      => 5,
		'offset'     => 0,
		'before'     => '<li>',
		'after'      => '</li>',
		'length'     => 50,
		'skipusers'  => '',
		'avatarsize' => 16,
		'xformat'    => '%gravatar%<a class="commentator" href="%permalink%" >%commentauthor%</a> : %commentexcerpt%',
		'echo'       => 1,
	);
	$r        = wp_parse_args( $args, $defaults );

	$r['password'] = 'hide' === $r['password'] ? 0 : 1;

	$query = new WP_Comment_Query(
		array(
			'number'         => $r['limit'],
			'offset'         => $r['offset'],
			'author__not_in' => array_filter( explode( ',', $r['skipusers'] ) ),
			'orderby'        => 'comment_date',
			'type'           => 'comment',
		)
	);

	$comments = $query->get_comments();
	$html     = '';
	foreach ( $comments as $comment ) {
		$permalink = get_the_permalink( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID;
		$content   = mb_substr( wp_strip_all_tags( $comment->comment_content ), 0, $r['length'] ) . '...';
		$record    = $r['before'] . $r['xformat'] . $r['after'];
		$record    = str_replace(
			array(
				'%gravatar%',
				'%permalink%',
				'%commentauthor%',
				'%commentexcerpt%',
				'%posttitle%',
			),
			array(
				get_avatar( $comment->comment_author_email, $r['avatarsize'] ),
				$permalink,
				$comment->comment_author,
				$content,
				get_the_title( $comment->comment_post_ID ),
			),
			$record
		);
		$record    = apply_filters( 'wut_recent_comment_item', $record, $comment );
		$html     .= $record . "\n";
	}

	if ( $r['echo'] ) {
		wut_print_html( $html );
	} else {
		return $html;
	}
}

/**
 * Print a piece of HTML code.
 *
 * @param string $html HTML string.
 * @return void
 */
function wut_print_html( $html ) {
	echo $html;
}

/**
 * Render a template string by post object.
 *
 * @param string   $template Template string.
 * @param WP_Post  $post Post object.
 * @param string   $date_format Date format string.
 * @param Callable $custom Custom render.
 * @return string
 */
function wut_private_render_template_by_post( $template, $post, $date_format = 'Y-m-d', $custom = null ) {
	$result = str_replace(
		array(
			'%title%',
			'%postdate%',
			'%commentcount%',
			'%permalink%',
		),
		array(
			get_the_title( $post ),
			get_the_date( $date_format, $post->ID ),
			$post->comment_count,
			get_the_permalink( $post->ID ),
		),
		$template
	);

	if ( ! is_null( $custom ) && is_callable( $custom ) ) {
		$result = call_user_func( $custom, $result );
	}
	return $result;
}
