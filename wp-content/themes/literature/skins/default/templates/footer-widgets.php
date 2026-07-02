<?php
/**
 * The template to display the widgets area in the footer
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.10
 */

// Footer sidebar
$literature_footer_name    = literature_get_theme_option( 'footer_widgets' );
$literature_footer_present = ! literature_is_off( $literature_footer_name ) && is_active_sidebar( $literature_footer_name );
if ( $literature_footer_present ) {
	literature_storage_set( 'current_sidebar', 'footer' );
	ob_start();
	if ( is_active_sidebar( $literature_footer_name ) ) {
		dynamic_sidebar( $literature_footer_name );
	}
	$literature_out = trim( ob_get_contents() );
	ob_end_clean();
	if ( ! empty( $literature_out ) ) {
		$literature_out          = preg_replace( "/<\\/aside>[\r\n\s]*<aside/", '</aside><aside', $literature_out );
		$literature_need_columns = true;   //or check: strpos($literature_out, 'columns_wrap')===false;
		if ( $literature_need_columns ) {
			$literature_columns = max( 0, (int) literature_get_theme_option( 'footer_columns' ) );			
			if ( 0 == $literature_columns ) {
				$literature_columns = min( 4, max( 1, literature_tags_count( $literature_out, 'aside' ) ) );
			}
			if ( $literature_columns > 1 ) {
				$literature_out = preg_replace( '/<aside([^>]*)class="widget/', '<aside$1class="column-1_' . esc_attr( $literature_columns ) . ' widget', $literature_out );
			} else {
				$literature_need_columns = false;
			}
		}
		?>
		<div class="footer_widgets_wrap widget_area sc_layouts_row">
			<?php do_action( 'literature_action_before_sidebar_wrap', 'footer' ); ?>
			<div class="footer_widgets_inner widget_area_inner">
				<div class="content_wrap">
					<?php
					if ( $literature_need_columns ) {
						?>
						<div class="columns_wrap">
						<?php
					}
					do_action( 'literature_action_before_sidebar', 'footer' );
					literature_show_layout( $literature_out );
					do_action( 'literature_action_after_sidebar', 'footer' );
					if ( $literature_need_columns ) {
						?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php do_action( 'literature_action_after_sidebar_wrap', 'footer' ); ?>
		</div>
		<?php
	}
}
