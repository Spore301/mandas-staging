<?php
/**
 * The template to display default site footer
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.10
 */

$literature_footer_id = literature_get_custom_footer_id();
$literature_footer_meta = literature_get_custom_layout_meta( $literature_footer_id );
if ( ! empty( $literature_footer_meta['margin'] ) ) {
	literature_add_inline_css( sprintf( '.page_content_wrap{padding-bottom:%s}', esc_attr( literature_prepare_css_value( $literature_footer_meta['margin'] ) ) ) );
}
?>
<footer class="footer_wrap footer_custom footer_custom_<?php echo esc_attr( $literature_footer_id ); ?> footer_custom_<?php echo esc_attr( sanitize_title( get_the_title( $literature_footer_id ) ) ); ?>">
	<?php
	// Custom footer's layout
	do_action( 'literature_action_show_layout', $literature_footer_id );
	?>
</footer>