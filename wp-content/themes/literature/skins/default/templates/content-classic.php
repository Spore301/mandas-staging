<?php
/**
 * The Classic template to display the content
 *
 * Used for index/archive/search.
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

$literature_template_args = get_query_var( 'literature_template_args' );

if ( is_array( $literature_template_args ) ) {
	$literature_columns       = empty( $literature_template_args['columns'] ) ? 1 : max( 1, $literature_template_args['columns'] );
	$literature_blog_style    = array( $literature_template_args['type'], $literature_columns );
	$literature_columns_class = literature_get_column_class( 1, $literature_columns, ! empty( $literature_template_args['columns_tablet']) ? $literature_template_args['columns_tablet'] : '', ! empty($literature_template_args['columns_mobile']) ? $literature_template_args['columns_mobile'] : '' );
} else {
	$literature_template_args = array();
	$literature_blog_style    = explode( '_', literature_get_theme_option( 'blog_style' ) );
	$literature_columns       = empty( $literature_blog_style[1] ) ? 1 : max( 1, $literature_blog_style[1] );
	$literature_columns_class = literature_get_column_class( 1, $literature_columns );
}
$literature_expanded   = ! literature_sidebar_present() && literature_get_theme_option( 'expand_content' ) == 'expand';

$literature_post_format = get_post_format();
$literature_post_format = empty( $literature_post_format ) ? 'standard' : str_replace( 'post-format-', '', $literature_post_format );

?><div class="<?php
	if ( ! empty( $literature_template_args['slider'] ) ) {
		echo ' slider-slide swiper-slide';
	} else {
		echo ( literature_is_blog_style_use_masonry( $literature_blog_style[0] )
			? 'masonry_item masonry_item-1_' . esc_attr( $literature_columns )
			: esc_attr( $literature_columns_class )
			);
	}
?>"><article id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>"
	<?php
	post_class(
		'post_item post_item_container post_format_' . esc_attr( $literature_post_format )
				. ' post_layout_classic post_layout_classic_' . esc_attr( $literature_columns )
				. ' post_layout_' . esc_attr( $literature_blog_style[0] )
				. ' post_layout_' . esc_attr( $literature_blog_style[0] ) . '_' . esc_attr( $literature_columns )
	);
	literature_add_blog_animation( $literature_template_args );
	?>
>
	<?php

	// Sticky label
	if ( is_sticky() && ! is_paged() ) {
		?><span class="post_label label_sticky"></span><?php
	}

	// Featured image
	$literature_hover      = ! empty( $literature_template_args['hover'] ) && ! literature_is_inherit( $literature_template_args['hover'] )
							? $literature_template_args['hover']
							: literature_get_theme_option( 'image_hover' );

	$literature_components = ! empty( $literature_template_args['meta_parts'] )
							? ( is_array( $literature_template_args['meta_parts'] )
								? $literature_template_args['meta_parts']
								: array_map( 'trim', explode( ',', $literature_template_args['meta_parts'] ) )
								)
							: literature_array_get_keys_by_value( literature_get_theme_option( 'meta_parts' ) );

	literature_show_post_featured( apply_filters( 'literature_filter_args_featured',
		array(
			'thumb_size' => ! empty( $literature_template_args['thumb_size'] )
								? $literature_template_args['thumb_size']
								: literature_get_thumb_size(
									strpos( literature_get_theme_option( 'body_style' ), 'full' ) !== false
										? ( $literature_columns > 2 ? 'big' : 'full' )
										: ( $literature_columns > 2
											? 'med'
											: ( $literature_expanded || $literature_columns == 1 ? 
												( $literature_expanded && $literature_columns == 1 ? 'huge' : 'big' ) 
												: 'med' 
												)
											)												
								),
			'hover'      => $literature_hover,
			'meta_parts' => $literature_components,
			'no_links'   => ! empty( $literature_template_args['no_links'] ),
		),
		'content-classic',
		$literature_template_args
	) );

	// Title and post meta
	$literature_show_title = get_the_title() != '';
	$literature_show_meta  = count( $literature_components ) > 0;

	if ( $literature_show_title ) {
		?><div class="post_header entry-header"><?php
			// Categories
			if ( apply_filters( 'literature_filter_show_blog_categories', $literature_show_meta && in_array( 'categories', $literature_components ), array( 'categories' ), 'classic' ) ) {
				do_action( 'literature_action_before_post_category' );
				?><div class="post_category"><?php
					literature_show_post_meta( apply_filters(
														'literature_filter_post_meta_args',
														array(
															'components' => 'categories',
															'seo'        => false,
															'echo'       => true,
															),
														'hover_' . $literature_hover, 1
														)
										);
				?></div><?php
				$literature_components = literature_array_delete_by_value( $literature_components, 'categories' );
				do_action( 'literature_action_after_post_category' );
			}
			// Post title
			if ( apply_filters( 'literature_filter_show_blog_title', true, 'classic' ) ) {
				do_action( 'literature_action_before_post_title' );
				if ( empty( $literature_template_args['no_links'] ) ) {
					the_title( sprintf( '<h3 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' );
				} else {
					the_title( '<h3 class="post_title entry-title">', '</h3>' );
				}
				do_action( 'literature_action_after_post_title' );
			}
		?></div><?php
	}
	
	// Post meta
	if ( apply_filters( 'literature_filter_show_blog_meta', $literature_show_meta, $literature_components, 'classic' ) ) {
		if ( count( $literature_components ) > 0 ) {
			do_action( 'literature_action_before_post_meta' );
			literature_show_post_meta(
				apply_filters(
					'literature_filter_post_meta_args', array(
						'components' => join( ',', $literature_components ),
						'seo'        => false,
						'echo'       => true,
						'author_avatar' => false,
					), $literature_blog_style[0], $literature_columns
				)
			);
			do_action( 'literature_action_after_post_meta' );
		}
	}

	// Post content
	ob_start();
	if ( apply_filters( 'literature_filter_show_blog_excerpt', ( ! isset( $literature_template_args['hide_excerpt'] ) || (int)$literature_template_args['hide_excerpt'] == 0 ) && (int)literature_get_theme_option( 'excerpt_length' ) > 0, 'classic' ) ) {
		literature_show_post_content( $literature_template_args, '<div class="post_content_inner">', '</div>' );
	}
	$literature_content = ob_get_contents();
	ob_end_clean();

	literature_show_layout( $literature_content, '<div class="post_content entry-content">', '</div>' );

		
	// More button
	if ( apply_filters( 'literature_filter_show_blog_readmore', ! $literature_show_title || ! empty( $literature_template_args['more_button'] ), 'classic' ) ) {
		if ( empty( $literature_template_args['no_links'] ) ) {
			do_action( 'literature_action_before_post_readmore' );
			literature_show_post_more_link( $literature_template_args, '<p>', '</p>' );
			do_action( 'literature_action_after_post_readmore' );
		}
	}

	?>

</article></div><?php
// Need opening PHP-tag above, because <div> is a inline-block element (used as column)!
