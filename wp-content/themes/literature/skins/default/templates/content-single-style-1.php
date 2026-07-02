<?php
/**
 * The "Style 1" template to display the content of the single post or attachment:
 * featured image, title and meta are placed inside the content area
 *
 * @package LITERATURE
 * @since LITERATURE 1.75.0
 */
?>
<article id="post-<?php the_ID(); ?>"
	<?php
	post_class( 'post_item_single'
		. ' post_type_' . esc_attr( get_post_type() ) 
		. ' post_format_' . esc_attr( str_replace( 'post-format-', '', get_post_format() ) )
	);
	literature_add_seo_itemprops();
	?>
>
<?php

	do_action( 'literature_action_before_post_data' );

	literature_add_seo_snippets();

	// Single post thumbnail and title
	if ( apply_filters( 'literature_filter_single_post_header', is_singular( 'post' ) || is_singular( 'attachment' ) ) ) {
		// Post title and meta
		ob_start();
		literature_show_post_title_and_meta( array( 
			'author_avatar' => false,
			'show_labels'   => true,
			'share_type'    => 'list',
			'add_spaces'    => false,
			'cat_sep' 	    => false,
		) );
		$literature_post_header = ob_get_contents();
		ob_end_clean();
		// Featured image
		ob_start();
		literature_show_post_featured_image( array(
			'thumb_bg' => false,
		) );
		$literature_post_header .= ob_get_contents();
		ob_end_clean();
		$literature_with_featured_image = literature_is_with_featured_image( $literature_post_header );

		if ( strpos( $literature_post_header, 'post_featured' ) !== false
			|| strpos( $literature_post_header, 'post_title' ) !== false
			|| strpos( $literature_post_header, 'post_meta' ) !== false
		) {
			?>
			<div class="post_header_wrap post_header_wrap_in_content post_header_wrap_style_<?php
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

	do_action( 'literature_action_before_post_content' );

	// Post content
	?>
	<div class="post_content post_content_single entry-content"<?php
		if ( literature_is_on( literature_get_theme_option( 'seo_snippets' ) ) ) {
			?> itemprop="mainEntityOfPage"<?php
		}
	?>>
		<?php
		the_content();
		?>
	</div><!-- .entry-content -->
	<?php
	do_action( 'literature_action_after_post_content' );
	
	// Post footer: Tags, likes, share, author, prev/next links and comments
	do_action( 'literature_action_before_post_footer' );
	?>
	<div class="post_footer post_footer_single entry-footer">
		<?php
		literature_show_post_pagination();
		if ( is_single() && ! is_attachment() ) {
			literature_show_post_footer();
		}
		?>
	</div>
	<?php
	do_action( 'literature_action_after_post_footer' );
	?>
</article>
