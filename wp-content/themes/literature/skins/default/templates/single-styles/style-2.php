<?php
/**
 * The "Style 2" template to display the post header of the single post or attachment:
 * featured image and title placed in the post header
 *
 * @package LITERATURE
 * @since LITERATURE 1.75.0
 */

if ( apply_filters( 'literature_filter_single_post_header', literature_is_singular( 'post' ) || literature_is_singular( 'attachment' ) ) ) {
	$literature_post_format = str_replace( 'post-format-', '', get_post_format() );

	// Featured image
	ob_start();
	literature_show_post_featured_image( array(
		'thumb_bg'  => true,
	) );
	$literature_post_header = ob_get_contents();
	ob_end_clean();

	$literature_with_featured_image = literature_is_with_featured_image( $literature_post_header );

	// Post title and meta
	ob_start();
	literature_show_post_title_and_meta( array(
										'content_wrap'  => true,
										'share_type'    => 'list',
										'show_labels'   => true,
										'author_avatar' => false,
										'add_spaces'    => false,
										'cat_sep' 	    => false,
										)
									);
	$literature_post_header .= ob_get_contents();
	ob_end_clean();

	if ( strpos( $literature_post_header, 'post_featured' ) !== false
		|| strpos( $literature_post_header, 'post_title' ) !== false
		|| strpos( $literature_post_header, 'post_meta' ) !== false
	) {
		?>
		<div class="post_header_wrap post_header_wrap_in_header post_header_wrap_style_<?php
			echo esc_attr( literature_get_theme_option( 'single_style' ) );
			if ( $literature_with_featured_image ) {
				echo ' with_featured_image';
			}
		?>">
			<?php
			do_action( 'literature_action_before_post_header' );
			literature_show_layout( $literature_post_header );
			do_action( 'literature_action_after_post_header' );
			?>
		</div>
		<?php
	}
}
