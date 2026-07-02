<?php
/**
 * Skin Demo importer
 *
 * @package LITERATURE
 * @since LITERATURE 1.76.0
 */


// Theme storage
//-------------------------------------------------------------------------

literature_storage_set( 'theme_demo_url', '//literature.axiomthemes.com' );


//------------------------------------------------------------------------
// One-click import support
//------------------------------------------------------------------------

// Set theme specific importer options
if ( ! function_exists( 'literature_skin_importer_set_options' ) ) {
	add_filter( 'trx_addons_filter_importer_options', 'literature_skin_importer_set_options', 9 );
	function literature_skin_importer_set_options( $options = array() ) {
		if ( is_array( $options ) ) {
			$demo_type = function_exists( 'literature_skins_get_current_skin_name' ) ? literature_skins_get_current_skin_name() : 'default';
			if ( 'default' != $demo_type ) {
				$options['demo_type'] = $demo_type;
				$options['files'][ $demo_type ] = $options['files']['default'];	// Copy all settings from 'default' to the new demo type
				unset($options['files']['default']);
			}
			// Override some settings in the new demo type
			$theme_slug = get_template();
			$theme_name = wp_get_theme( $theme_slug )->get( 'Name' );
			$options['files'][ $demo_type ]['title'] = sprintf( esc_html__( '%s Demo', 'literature' ), $theme_name )
				. ( $demo_type != 'default'
					? '. ' . sprintf( esc_html__( 'Skin %s', 'literature' ), ucfirst( str_replace( array( '-', '_' ), ' ', $demo_type ) ) )
					: ''
					);
			$options['files'][ $demo_type ]['domain_dev']  = ''; // Developers domain, example: literature_add_protocol( '//literature.dev.axiomthemes.com' );
			$options['files'][ $demo_type ]['domain_demo'] = literature_add_protocol( literature_storage_get( 'theme_demo_url' ) ); // Demo-site domain
		}
		return $options;
	}
}