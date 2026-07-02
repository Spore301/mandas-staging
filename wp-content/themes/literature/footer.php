<?php
/**
 * The Footer: widgets area, logo, footer menu and socials
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

							do_action( 'literature_action_page_content_end_text' );
							
							// Widgets area below the content
							literature_create_widgets_area( 'widgets_below_content' );
						
							do_action( 'literature_action_page_content_end' );
							?>
						</div>
						<?php
						
						do_action( 'literature_action_after_page_content' );

						// Show main sidebar
						get_sidebar();

						do_action( 'literature_action_content_wrap_end' );
						?>
					</div>
					<?php

					do_action( 'literature_action_after_content_wrap' );

					// Widgets area below the page and related posts below the page
					$literature_body_style = literature_get_theme_option( 'body_style' );
					$literature_widgets_name = literature_get_theme_option( 'widgets_below_page', 'hide' );
					$literature_show_widgets = ! literature_is_off( $literature_widgets_name ) && is_active_sidebar( $literature_widgets_name );
					$literature_show_related = literature_is_single() && literature_get_theme_option( 'related_position', 'below_content' ) == 'below_page';
					if ( $literature_show_widgets || $literature_show_related ) {
						if ( 'fullscreen' != $literature_body_style ) {
							?>
							<div class="content_wrap">
							<?php
						}
						// Show related posts before footer
						if ( $literature_show_related ) {
							do_action( 'literature_action_related_posts' );
						}

						// Widgets area below page content
						if ( $literature_show_widgets ) {
							literature_create_widgets_area( 'widgets_below_page' );
						}
						if ( 'fullscreen' != $literature_body_style ) {
							?>
							</div>
							<?php
						}
					}
					do_action( 'literature_action_page_content_wrap_end' );
					?>
			</div>
			<?php
			do_action( 'literature_action_after_page_content_wrap' );

			// Don't display the footer elements while actions 'full_post_loading' and 'prev_post_loading'
			if ( ( ! literature_is_singular( 'post' ) && ! literature_is_singular( 'attachment' ) ) || ! in_array ( literature_get_value_gp( 'action' ), array( 'full_post_loading', 'prev_post_loading' ) ) ) {
				
				// Skip link anchor to fast access to the footer from keyboard
				?>
				<span id="footer_skip_link_anchor" class="literature_skip_link_anchor"></span>
				<?php

				do_action( 'literature_action_before_footer' );

				// Footer
				$literature_footer_type = literature_get_theme_option( 'footer_type' );
				if ( 'custom' == $literature_footer_type && ! literature_is_layouts_available() ) {
					$literature_footer_type = 'default';
				}
				get_template_part( apply_filters( 'literature_filter_get_template_part', "templates/footer-" . sanitize_file_name( $literature_footer_type ) ) );

				do_action( 'literature_action_after_footer' );

			}
			?>

			<?php do_action( 'literature_action_page_wrap_end' ); ?>

		</div>

		<?php do_action( 'literature_action_after_page_wrap' ); ?>

	</div>

	<?php do_action( 'literature_action_after_body' ); ?>

	<?php wp_footer(); ?>

</body>
</html>