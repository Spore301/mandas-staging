<div class="front_page_section front_page_section_testimonials<?php
	$literature_scheme = literature_get_theme_option( 'front_page_testimonials_scheme' );
	if ( ! empty( $literature_scheme ) && ! literature_is_inherit( $literature_scheme ) ) {
		echo ' scheme_' . esc_attr( $literature_scheme );
	}
	echo ' front_page_section_paddings_' . esc_attr( literature_get_theme_option( 'front_page_testimonials_paddings' ) );
	if ( literature_get_theme_option( 'front_page_testimonials_stack' ) ) {
		echo ' sc_stack_section_on';
	}
?>"
		<?php
		$literature_css      = '';
		$literature_bg_image = literature_get_theme_option( 'front_page_testimonials_bg_image' );
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
	$literature_anchor_icon = literature_get_theme_option( 'front_page_testimonials_anchor_icon' );
	$literature_anchor_text = literature_get_theme_option( 'front_page_testimonials_anchor_text' );
if ( ( ! empty( $literature_anchor_icon ) || ! empty( $literature_anchor_text ) ) && shortcode_exists( 'trx_sc_anchor' ) ) {
	echo do_shortcode(
		'[trx_sc_anchor id="front_page_section_testimonials"'
									. ( ! empty( $literature_anchor_icon ) ? ' icon="' . esc_attr( $literature_anchor_icon ) . '"' : '' )
									. ( ! empty( $literature_anchor_text ) ? ' title="' . esc_attr( $literature_anchor_text ) . '"' : '' )
									. ']'
	);
}
?>
	<div class="front_page_section_inner front_page_section_testimonials_inner
	<?php
	if ( literature_get_theme_option( 'front_page_testimonials_fullheight' ) ) {
		echo ' literature-full-height sc_layouts_flex sc_layouts_columns_middle';
	}
	?>
			"
			<?php
			$literature_css      = '';
			$literature_bg_mask  = literature_get_theme_option( 'front_page_testimonials_bg_mask' );
			$literature_bg_color_type = literature_get_theme_option( 'front_page_testimonials_bg_color_type' );
			if ( 'custom' == $literature_bg_color_type ) {
				$literature_bg_color = literature_get_theme_option( 'front_page_testimonials_bg_color' );
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
		<div class="front_page_section_content_wrap front_page_section_testimonials_content_wrap content_wrap">
			<?php
			// Caption
			$literature_caption = literature_get_theme_option( 'front_page_testimonials_caption' );
			if ( ! empty( $literature_caption ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<h2 class="front_page_section_caption front_page_section_testimonials_caption front_page_block_<?php echo ! empty( $literature_caption ) ? 'filled' : 'empty'; ?>"><?php echo wp_kses( $literature_caption, 'literature_kses_content' ); ?></h2>
				<?php
			}

			// Description (text)
			$literature_description = literature_get_theme_option( 'front_page_testimonials_description' );
			if ( ! empty( $literature_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<div class="front_page_section_description front_page_section_testimonials_description front_page_block_<?php echo ! empty( $literature_description ) ? 'filled' : 'empty'; ?>"><?php echo wp_kses( wpautop( $literature_description ), 'literature_kses_content' ); ?></div>
				<?php
			}

			// Content (widgets)
			?>
			<div class="front_page_section_output front_page_section_testimonials_output">
				<?php
				if ( is_active_sidebar( 'front_page_testimonials_widgets' ) ) {
					dynamic_sidebar( 'front_page_testimonials_widgets' );
				} elseif ( current_user_can( 'edit_theme_options' ) ) {
					if ( ! literature_exists_trx_addons() ) {
						literature_customizer_need_trx_addons_message();
					} else {
						literature_customizer_need_widgets_message( 'front_page_testimonials_caption', 'ThemeREX Addons - Testimonials' );
					}
				}
				?>
			</div>
		</div>
	</div>
</div>
