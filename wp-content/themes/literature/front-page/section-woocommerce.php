<?php
$literature_woocommerce_sc = literature_get_theme_option( 'front_page_woocommerce_products' );
if ( ! empty( $literature_woocommerce_sc ) ) {
	?><div class="front_page_section front_page_section_woocommerce<?php
		$literature_scheme = literature_get_theme_option( 'front_page_woocommerce_scheme' );
		if ( ! empty( $literature_scheme ) && ! literature_is_inherit( $literature_scheme ) ) {
			echo ' scheme_' . esc_attr( $literature_scheme );
		}
		echo ' front_page_section_paddings_' . esc_attr( literature_get_theme_option( 'front_page_woocommerce_paddings' ) );
		if ( literature_get_theme_option( 'front_page_woocommerce_stack' ) ) {
			echo ' sc_stack_section_on';
		}
	?>"
			<?php
			$literature_css      = '';
			$literature_bg_image = literature_get_theme_option( 'front_page_woocommerce_bg_image' );
			if ( ! empty( $literature_bg_image ) ) {
				$literature_css .= 'background-image: url(' . esc_url( literature_get_attachment_url( $literature_bg_image ) ) . ');';
			}
			if ( ! empty( $literature_css ) ) {
				echo ' style="' . esc_attr( $literature_css ) . '"';
			}
			?>
	>
	<?php
		// Add anchor
		$literature_anchor_icon = literature_get_theme_option( 'front_page_woocommerce_anchor_icon' );
		$literature_anchor_text = literature_get_theme_option( 'front_page_woocommerce_anchor_text' );
		if ( ( ! empty( $literature_anchor_icon ) || ! empty( $literature_anchor_text ) ) && shortcode_exists( 'trx_sc_anchor' ) ) {
			echo do_shortcode(
				'[trx_sc_anchor id="front_page_section_woocommerce"'
											. ( ! empty( $literature_anchor_icon ) ? ' icon="' . esc_attr( $literature_anchor_icon ) . '"' : '' )
											. ( ! empty( $literature_anchor_text ) ? ' title="' . esc_attr( $literature_anchor_text ) . '"' : '' )
											. ']'
			);
		}
	?>
		<div class="front_page_section_inner front_page_section_woocommerce_inner
			<?php
			if ( literature_get_theme_option( 'front_page_woocommerce_fullheight' ) ) {
				echo ' literature-full-height sc_layouts_flex sc_layouts_columns_middle';
			}
			?>
				"
				<?php
				$literature_css      = '';
				$literature_bg_mask  = literature_get_theme_option( 'front_page_woocommerce_bg_mask' );
				$literature_bg_color_type = literature_get_theme_option( 'front_page_woocommerce_bg_color_type' );
				if ( 'custom' == $literature_bg_color_type ) {
					$literature_bg_color = literature_get_theme_option( 'front_page_woocommerce_bg_color' );
				} elseif ( 'scheme_bg_color' == $literature_bg_color_type ) {
					$literature_bg_color = literature_get_scheme_color( 'bg_color', $literature_scheme );
				} else {
					$literature_bg_color = '';
				}
				if ( ! empty( $literature_bg_color ) && $literature_bg_mask > 0 ) {
					$literature_css .= 'background-color: ' . esc_attr(
						1 == $literature_bg_mask ? $literature_bg_color : literature_hex2rgba( $literature_bg_color, $literature_bg_mask )
					) . ';';
				}
				if ( ! empty( $literature_css ) ) {
					echo ' style="' . esc_attr( $literature_css ) . '"';
				}
				?>
		>
			<div class="front_page_section_content_wrap front_page_section_woocommerce_content_wrap content_wrap woocommerce">
				<?php
				// Content wrap with title and description
				$literature_caption     = literature_get_theme_option( 'front_page_woocommerce_caption' );
				$literature_description = literature_get_theme_option( 'front_page_woocommerce_description' );
				if ( ! empty( $literature_caption ) || ! empty( $literature_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
					// Caption
					if ( ! empty( $literature_caption ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
						?>
						<h2 class="front_page_section_caption front_page_section_woocommerce_caption front_page_block_<?php echo ! empty( $literature_caption ) ? 'filled' : 'empty'; ?>">
						<?php
							echo wp_kses( $literature_caption, 'literature_kses_content' );
						?>
						</h2>
						<?php
					}

					// Description (text)
					if ( ! empty( $literature_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
						?>
						<div class="front_page_section_description front_page_section_woocommerce_description front_page_block_<?php echo ! empty( $literature_description ) ? 'filled' : 'empty'; ?>">
						<?php
							echo wp_kses( wpautop( $literature_description ), 'literature_kses_content' );
						?>
						</div>
						<?php
					}
				}

				// Content (widgets)
				?>
				<div class="front_page_section_output front_page_section_woocommerce_output list_products shop_mode_thumbs">
					<?php
					if ( 'products' == $literature_woocommerce_sc ) {
						$literature_woocommerce_sc_ids      = literature_get_theme_option( 'front_page_woocommerce_products_per_page' );
						$literature_woocommerce_sc_per_page = count( explode( ',', $literature_woocommerce_sc_ids ) );
					} else {
						$literature_woocommerce_sc_per_page = max( 1, (int) literature_get_theme_option( 'front_page_woocommerce_products_per_page' ) );
					}
					$literature_woocommerce_sc_columns = max( 1, min( $literature_woocommerce_sc_per_page, (int) literature_get_theme_option( 'front_page_woocommerce_products_columns' ) ) );
					echo do_shortcode(
						"[{$literature_woocommerce_sc}"
										. ( 'products' == $literature_woocommerce_sc
												? ' ids="' . esc_attr( $literature_woocommerce_sc_ids ) . '"'
												: '' )
										. ( 'product_category' == $literature_woocommerce_sc
												? ' category="' . esc_attr( literature_get_theme_option( 'front_page_woocommerce_products_categories' ) ) . '"'
												: '' )
										. ( 'best_selling_products' != $literature_woocommerce_sc
												? ' orderby="' . esc_attr( literature_get_theme_option( 'front_page_woocommerce_products_orderby' ) ) . '"'
													. ' order="' . esc_attr( literature_get_theme_option( 'front_page_woocommerce_products_order' ) ) . '"'
												: '' )
										. ' per_page="' . esc_attr( $literature_woocommerce_sc_per_page ) . '"'
										. ' columns="' . esc_attr( $literature_woocommerce_sc_columns ) . '"'
						. ']'
					);
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
