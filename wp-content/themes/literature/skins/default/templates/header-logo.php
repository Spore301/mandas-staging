<?php
/**
 * The template to display the logo or the site name and the slogan in the Header
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

$literature_args = get_query_var( 'literature_logo_args' );

// Site logo
$literature_logo_type   = isset( $literature_args['type'] ) ? $literature_args['type'] : '';
$literature_logo_image  = literature_get_logo_image( $literature_logo_type );
$literature_logo_text   = literature_is_on( literature_get_theme_option( 'logo_text' ) ) ? get_bloginfo( 'name' ) : '';
$literature_logo_slogan = get_bloginfo( 'description', 'display' );
if ( ! empty( $literature_logo_image['logo'] ) || ! empty( $literature_logo_text ) ) {
	?><a class="sc_layouts_logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php
		if ( ! empty( $literature_logo_image['logo'] ) ) {
			if ( empty( $literature_logo_type ) && function_exists( 'the_custom_logo' ) && is_numeric( $literature_logo_image['logo'] ) && (int) $literature_logo_image['logo'] > 0 ) {
				the_custom_logo();
			} else {
				$literature_attr = literature_getimagesize( $literature_logo_image['logo'] );
				echo '<img src="' . esc_url( $literature_logo_image['logo'] ) . '"'
						. ( ! empty( $literature_logo_image['logo_retina'] ) ? ' srcset="' . esc_url( $literature_logo_image['logo_retina'] ) . ' 2x"' : '' )
						. ' alt="' . esc_attr( $literature_logo_text ) . '"'
						. ( ! empty( $literature_attr[3] ) ? ' ' . wp_kses_data( $literature_attr[3] ) : '' )
						. '>';
			}
		} else {
			literature_show_layout( literature_prepare_macros( $literature_logo_text ), '<span class="logo_text">', '</span>' );
			literature_show_layout( literature_prepare_macros( $literature_logo_slogan ), '<span class="logo_slogan">', '</span>' );
		}
		?>
	</a>
	<?php
}
