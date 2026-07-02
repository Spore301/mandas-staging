<?php
/**
 * The template to display single post
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

// Full post loading
$full_post_loading          = literature_get_value_gp( 'action' ) == 'full_post_loading';

// Prev post loading
$prev_post_loading          = literature_get_value_gp( 'action' ) == 'prev_post_loading';
$prev_post_loading_type     = literature_get_theme_option( 'posts_navigation_scroll_which_block', 'article' );

// Position of the related posts
$literature_related_position   = literature_get_theme_option( 'related_position', 'below_content' );

// Type of the prev/next post navigation
$literature_posts_navigation   = literature_get_theme_option( 'posts_navigation' );
$literature_prev_post          = false;
$literature_prev_post_same_cat = (int)literature_get_theme_option( 'posts_navigation_scroll_same_cat', 1 );

// Rewrite style of the single post if current post loading via AJAX and featured image and title is not in the content
if ( ( $full_post_loading 
		|| 
		( $prev_post_loading && 'article' == $prev_post_loading_type )
	) 
	&& 
	! in_array( literature_get_theme_option( 'single_style' ), array( 'style-6' ) )
) {
	literature_storage_set_array( 'options_meta', 'single_style', 'style-6' );
}

do_action( 'literature_action_prev_post_loading', $prev_post_loading, $prev_post_loading_type );

get_header();

while ( have_posts() ) {

	the_post();

	// Type of the prev/next post navigation
	if ( 'scroll' == $literature_posts_navigation ) {
		$literature_prev_post = get_previous_post( $literature_prev_post_same_cat );  // Get post from same category
		if ( ! $literature_prev_post && $literature_prev_post_same_cat ) {
			$literature_prev_post = get_previous_post( false );                    // Get post from any category
		}
		if ( ! $literature_prev_post ) {
			$literature_posts_navigation = 'links';
		}
	}

	// Override some theme options to display featured image, title and post meta in the dynamic loaded posts
	if ( $full_post_loading || ( $prev_post_loading && $literature_prev_post ) ) {
		literature_sc_layouts_showed( 'featured', false );
		literature_sc_layouts_showed( 'title', false );
		literature_sc_layouts_showed( 'postmeta', false );
	}

	// If related posts should be inside the content
	if ( strpos( $literature_related_position, 'inside' ) === 0 ) {
		ob_start();
	}

	// Display post's content
	get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/content', 'single-' . literature_get_theme_option( 'single_style' ) ), 'single-' . literature_get_theme_option( 'single_style' ) );

	// If related posts should be inside the content
	if ( strpos( $literature_related_position, 'inside' ) === 0 ) {
		$literature_content = ob_get_contents();
		ob_end_clean();

		ob_start();
		do_action( 'literature_action_related_posts' );
		$literature_related_content = ob_get_contents();
		ob_end_clean();

		if ( ! empty( $literature_related_content ) ) {
			$literature_related_position_inside = max( 0, min( 9, literature_get_theme_option( 'related_position_inside' ) ) );
			if ( 0 == $literature_related_position_inside ) {
				$literature_related_position_inside = mt_rand( 1, 9 );
			}

			$literature_p_number         = 0;
			$literature_related_inserted = false;
			$literature_in_block         = false;
			$literature_content_start    = strpos( $literature_content, '<div class="post_content' );
			$literature_content_end      = strrpos( $literature_content, '</div>' );

			for ( $i = max( 0, $literature_content_start ); $i < min( strlen( $literature_content ) - 3, $literature_content_end ); $i++ ) {
				if ( $literature_content[ $i ] != '<' ) {
					continue;
				}
				if ( $literature_in_block ) {
					if ( strtolower( substr( $literature_content, $i + 1, 12 ) ) == '/blockquote>' ) {
						$literature_in_block = false;
						$i += 12;
					}
					continue;
				} else if ( strtolower( substr( $literature_content, $i + 1, 10 ) ) == 'blockquote' && in_array( $literature_content[ $i + 11 ], array( '>', ' ' ) ) ) {
					$literature_in_block = true;
					$i += 11;
					continue;
				} else if ( 'p' == $literature_content[ $i + 1 ] && in_array( $literature_content[ $i + 2 ], array( '>', ' ' ) ) ) {
					$literature_p_number++;
					if ( $literature_related_position_inside == $literature_p_number ) {
						$literature_related_inserted = true;
						$literature_content = ( $i > 0 ? substr( $literature_content, 0, $i ) : '' )
											. $literature_related_content
											. substr( $literature_content, $i );
					}
				}
			}
			if ( ! $literature_related_inserted ) {
				if ( $literature_content_end > 0 ) {
					$literature_content = substr( $literature_content, 0, $literature_content_end ) . $literature_related_content . substr( $literature_content, $literature_content_end );
				} else {
					$literature_content .= $literature_related_content;
				}
			}
		}

		literature_show_layout( $literature_content );
	}

	// Comments
	do_action( 'literature_action_before_comments' );
	comments_template();
	do_action( 'literature_action_after_comments' );

	// Related posts
	if ( 'below_content' == $literature_related_position
		&& ( 'scroll' != $literature_posts_navigation || (int)literature_get_theme_option( 'posts_navigation_scroll_hide_related', 0 ) == 0 )
		&& ( ! $full_post_loading || (int)literature_get_theme_option( 'open_full_post_hide_related', 1 ) == 0 )
	) {
		do_action( 'literature_action_related_posts' );
	}

	// Post navigation: type 'scroll'
	if ( 'scroll' == $literature_posts_navigation && ! $full_post_loading ) {
		?>
		<div class="nav-links-single-scroll"
			data-post-id="<?php echo esc_attr( get_the_ID( $literature_prev_post ) ); ?>"
			data-post-link="<?php echo esc_attr( get_permalink( $literature_prev_post ) ); ?>"
			data-post-title="<?php the_title_attribute( array( 'post' => $literature_prev_post ) ); ?>"
			data-cur-post-link="<?php echo esc_attr( get_permalink() ); ?>"
			data-cur-post-title="<?php the_title_attribute(); ?>"
			<?php do_action( 'literature_action_nav_links_single_scroll_data', $literature_prev_post ); ?>
		></div>
		<?php
	}
}

get_footer();
