<?php
/**
 * The template to display custom header from the ThemeREX Addons Layouts
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.06
 */

$literature_header_css   = '';
$literature_header_image = get_header_image();
if ( ! empty( $literature_header_image ) && literature_trx_addons_featured_image_override( literature_is_singular() || literature_storage_isset( 'blog_archive' ) || is_category() ) ) {
	$literature_header_image = literature_get_current_mode_image( $literature_header_image );
}

$literature_header_id = literature_get_custom_header_id();
$literature_header_meta = literature_get_custom_layout_meta( $literature_header_id );
if ( ! empty( $literature_header_meta['margin'] ) ) {
	literature_add_inline_css( sprintf( '.page_content_wrap{padding-top:%s}', esc_attr( literature_prepare_css_value( $literature_header_meta['margin'] ) ) ) );
	literature_storage_set( 'custom_header_margin', literature_prepare_css_value( $literature_header_meta['margin'] ) );
}

?><header class="top_panel top_panel_custom top_panel_custom_<?php echo esc_attr( $literature_header_id ); ?> top_panel_custom_<?php echo esc_attr( sanitize_title( get_the_title( $literature_header_id ) ) ); ?>
				<?php
				echo ! empty( $literature_header_image )
					? ' with_bg_image'
					: ' without_bg_image';
				if ( '' != $literature_header_image ) {
					echo ' ' . esc_attr( literature_add_inline_css_class( 'background-image: url(' . esc_url( $literature_header_image ) . ');' ) );
				}
				if ( literature_is_single() && has_post_thumbnail() ) {
					echo ' with_featured_image';
				}
				?>
">
	<?php

	// Custom header's layout
	do_action( 'literature_action_show_layout', $literature_header_id );

	?>
</header>
