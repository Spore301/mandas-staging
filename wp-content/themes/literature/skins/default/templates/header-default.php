<?php
/**
 * The template to display default site header
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

$literature_header_css   = '';
$literature_header_image = get_header_image();
$literature_header_video = literature_get_header_video();
if ( ! empty( $literature_header_image ) && literature_trx_addons_featured_image_override( literature_is_singular() || literature_storage_isset( 'blog_archive' ) || is_category() ) ) {
	$literature_header_image = literature_get_current_mode_image( $literature_header_image );
}
?><header class="top_panel top_panel_default
	<?php
	echo ! empty( $literature_header_image ) || ! empty( $literature_header_video ) ? ' with_bg_image' : ' without_bg_image';
	if ( '' != $literature_header_video ) {
		echo ' with_bg_video';
	}
	if ( '' != $literature_header_image ) {
		echo ' ' . esc_attr( literature_add_inline_css_class( 'background-image: url(' . esc_url( $literature_header_image ) . ');' ) );
	}
	if ( literature_is_singular() && has_post_thumbnail() ) {
		echo ' with_featured_image';
	}
	?>
">
	<?php

	// Background video
	if ( ! empty( $literature_header_video ) ) {
		get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/header-video' ) );
	}

	// Main menu
	get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/header-navi' ) );

	// Page title and breadcrumbs area
	if ( ! literature_is_single() ) {
		get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/header-title' ) );
	}
	?>
</header>
