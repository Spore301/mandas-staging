<?php
/**
 * The template to display the page title and breadcrumbs
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

// Page (category, tag, archive, author) title

if ( literature_need_page_title() ) {
	literature_sc_layouts_showed( 'title', true );
	?>
	<div class="top_panel_title sc_layouts_row">
		<div class="content_wrap">
			<div class="sc_layouts_column sc_layouts_column_align_center">
				<div class="sc_layouts_item">
					<div class="sc_layouts_title sc_align_center">
						<?php
						// Blog/Page title
						?>
						<div class="sc_layouts_title_title">
							<?php
							$literature_blog_title           = literature_get_blog_title();
							$literature_blog_title_text      = '';
							$literature_blog_title_class     = '';
							$literature_blog_title_link      = '';
							$literature_blog_title_link_text = '';
							if ( is_array( $literature_blog_title ) ) {
								$literature_blog_title_text      = $literature_blog_title['text'];
								$literature_blog_title_class     = ! empty( $literature_blog_title['class'] ) ? ' ' . $literature_blog_title['class'] : '';
								$literature_blog_title_link      = ! empty( $literature_blog_title['link'] ) ? $literature_blog_title['link'] : '';
								$literature_blog_title_link_text = ! empty( $literature_blog_title['link_text'] ) ? $literature_blog_title['link_text'] : '';
							} else {
								$literature_blog_title_text = $literature_blog_title;
							}
							?>
							<h1 class="sc_layouts_title_caption<?php echo esc_attr( $literature_blog_title_class ); ?>"<?php
								if ( literature_is_on( literature_get_theme_option( 'seo_snippets' ) ) ) {
									?> itemprop="headline"<?php
								}
							?>>
								<?php
								$literature_top_icon = literature_get_term_image_small();
								if ( ! empty( $literature_top_icon ) ) {
									$literature_attr = literature_getimagesize( $literature_top_icon );
									?>
									<img src="<?php echo esc_url( $literature_top_icon ); ?>" alt="<?php esc_attr_e( 'Site icon', 'literature' ); ?>"
										<?php
										if ( ! empty( $literature_attr[3] ) ) {
											literature_show_layout( $literature_attr[3] );
										}
										?>
									>
									<?php
								}
								echo wp_kses_data( $literature_blog_title_text );
								?>
							</h1>
							<?php
							if ( ! empty( $literature_blog_title_link ) && ! empty( $literature_blog_title_link_text ) ) {
								?>
								<a href="<?php echo esc_url( $literature_blog_title_link ); ?>" class="theme_button sc_layouts_title_link"><?php echo esc_html( $literature_blog_title_link_text ); ?></a>
								<?php
							}

							// Category/Tag description
							if ( ! is_paged() && ( is_category() || is_tag() || is_tax() ) ) {
								the_archive_description( '<div class="sc_layouts_title_description">', '</div>' );
							}

							?>
						</div>
						<?php

						// Breadcrumbs
						ob_start();
						do_action( 'literature_action_breadcrumbs' );
						$literature_breadcrumbs = ob_get_contents();
						ob_end_clean();
						literature_show_layout( $literature_breadcrumbs, '<div class="sc_layouts_title_breadcrumbs">', '</div>' );
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
