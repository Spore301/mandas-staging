<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

if ( literature_sidebar_present() ) {
	
	$literature_sidebar_type = literature_get_theme_option( 'sidebar_type' );
	if ( 'custom' == $literature_sidebar_type && ! literature_is_layouts_available() ) {
		$literature_sidebar_type = 'default';
	}
	
	// Catch output to the buffer
	ob_start();
	if ( 'default' == $literature_sidebar_type ) {
		// Default sidebar with widgets
		$literature_sidebar_name = literature_get_theme_option( 'sidebar_widgets' );
		literature_storage_set( 'current_sidebar', 'sidebar' );
		if ( is_active_sidebar( $literature_sidebar_name ) ) {
			dynamic_sidebar( $literature_sidebar_name );
		}
	} else {
		// Custom sidebar from Layouts Builder
		$literature_sidebar_id = literature_get_custom_sidebar_id();
		do_action( 'literature_action_show_layout', $literature_sidebar_id );
	}
	$literature_out = trim( ob_get_contents() );
	ob_end_clean();
	
	// If any html is present - display it
	if ( ! empty( $literature_out ) ) {
		$literature_sidebar_position    = literature_get_theme_option( 'sidebar_position' );
		$literature_sidebar_position_ss = literature_get_theme_option( 'sidebar_position_ss', 'below' );
		?>
		<div class="sidebar widget_area
			<?php
			echo ' ' . esc_attr( $literature_sidebar_position );
			echo ' sidebar_' . esc_attr( $literature_sidebar_position_ss );
			echo ' sidebar_' . esc_attr( $literature_sidebar_type );

			$literature_sidebar_scheme = apply_filters( 'literature_filter_sidebar_scheme', literature_get_theme_option( 'sidebar_scheme', 'inherit' ) );
			if ( ! empty( $literature_sidebar_scheme ) && ! literature_is_inherit( $literature_sidebar_scheme ) && 'custom' != $literature_sidebar_type ) {
				echo ' scheme_' . esc_attr( $literature_sidebar_scheme );
			}
			?>
		" role="complementary">
			<?php

			// Skip link anchor to fast access to the sidebar from keyboard
			?>
			<span id="sidebar_skip_link_anchor" class="literature_skip_link_anchor"></span>
			<?php

			do_action( 'literature_action_before_sidebar_wrap', 'sidebar' );

			// Button to show/hide sidebar on mobile
			if ( in_array( $literature_sidebar_position_ss, array( 'above', 'float' ) ) ) {
				$literature_title = apply_filters( 'literature_filter_sidebar_control_title', 'float' == $literature_sidebar_position_ss ? esc_html__( 'Show Sidebar', 'literature' ) : '' );
				$literature_text  = apply_filters( 'literature_filter_sidebar_control_text', 'above' == $literature_sidebar_position_ss ? esc_html__( 'Show Sidebar', 'literature' ) : '' );
				?>
				<a href="#" role="button" class="sidebar_control" title="<?php echo esc_attr( $literature_title ); ?>"><?php echo esc_html( $literature_text ); ?></a>
				<?php
			}
			?>
			<div class="sidebar_inner">
				<?php
				do_action( 'literature_action_before_sidebar', 'sidebar' );
				literature_show_layout( preg_replace( "/<\/aside>[\r\n\s]*<aside/", '</aside><aside', $literature_out ) );
				do_action( 'literature_action_after_sidebar', 'sidebar' );
				?>
			</div>
			<?php

			do_action( 'literature_action_after_sidebar_wrap', 'sidebar' );

			?>
		</div>
		<div class="clearfix"></div>
		<?php
	}
}
