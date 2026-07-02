<?php
/**
 * The Header: Logo and main menu
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js<?php
	// Class scheme_xxx need in the <html> as context for the <body>!
	echo ' scheme_' . esc_attr( literature_get_theme_option( 'color_scheme' ) );
?>">

<head>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php
	if ( function_exists( 'wp_body_open' ) ) {
		wp_body_open();
	} else {
		do_action( 'wp_body_open' );
	}

	$literature_full_post_loading = ( literature_is_singular( 'post' ) || literature_is_singular( 'attachment' ) ) && literature_get_value_gp( 'action' ) == 'full_post_loading';
	$literature_prev_post_loading = ( literature_is_singular( 'post' ) || literature_is_singular( 'attachment' ) ) && literature_get_value_gp( 'action' ) == 'prev_post_loading';

	// Don't display the short links while actions 'full_post_loading' and 'prev_post_loading'
	if ( ! $literature_full_post_loading && ! $literature_prev_post_loading ) {
		// Short links to fast access to the content, sidebar and footer from the keyboard
		?><a class="skip-link literature_skip_link skip_to_content_link" href="#content_skip_link_anchor" tabindex="<?php echo esc_attr( apply_filters( 'literature_filter_skip_links_tabindex', 0 ) ); ?>"><?php esc_html_e( "Skip to content", 'literature' ); ?></a><?php
		if ( literature_sidebar_present() ) {
			?><a class="skip-link literature_skip_link skip_to_sidebar_link" href="#sidebar_skip_link_anchor" tabindex="<?php echo esc_attr( apply_filters( 'literature_filter_skip_links_tabindex', 0 ) ); ?>"><?php esc_html_e( "Skip to sidebar", 'literature' ); ?></a><?php
		}
		?><a class="skip-link literature_skip_link skip_to_footer_link" href="#footer_skip_link_anchor" tabindex="<?php echo esc_attr( apply_filters( 'literature_filter_skip_links_tabindex', 0 ) ); ?>"><?php esc_html_e( "Skip to footer", 'literature' ); ?></a><?php
	}

	do_action( 'literature_action_before_body' );
	?>

	<div class="<?php echo esc_attr( apply_filters( 'literature_filter_body_wrap_class', 'body_wrap' ) ); ?>" <?php do_action('literature_action_body_wrap_attributes'); ?>>

		<?php do_action( 'literature_action_before_page_wrap' ); ?>

		<div class="<?php echo esc_attr( apply_filters( 'literature_filter_page_wrap_class', 'page_wrap' ) ); ?>" <?php do_action('literature_action_page_wrap_attributes'); ?>>

			<?php do_action( 'literature_action_page_wrap_start' ); ?>

			<?php

			// Don't display the header elements while actions 'full_post_loading' and 'prev_post_loading'
			if ( ! $literature_full_post_loading && ! $literature_prev_post_loading ) {

				do_action( 'literature_action_before_header' );

				// Header
				$literature_header_type = literature_get_theme_option( 'header_type' );
				if ( 'custom' == $literature_header_type && ! literature_is_layouts_available() ) {
					$literature_header_type = 'default';
				}
				get_template_part( apply_filters( 'literature_filter_get_template_part', "templates/header-" . sanitize_file_name( $literature_header_type ) ) );

				// Side menu
				if ( in_array( literature_get_theme_option( 'menu_side', 'none' ), array( 'left', 'right' ) ) ) {
					get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/header-navi-side' ) );
				}

				// Mobile menu
				if ( apply_filters( 'literature_filter_use_navi_mobile', literature_sc_layouts_showed( 'menu_button' ) || $literature_header_type == 'default' ) ) {
					get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/header-navi-mobile' ) );
				}

				do_action( 'literature_action_after_header' );

			}
			?>

			<?php do_action( 'literature_action_before_page_content_wrap' ); ?>

			<div class="page_content_wrap<?php
				if ( literature_is_off( literature_get_theme_option( 'remove_margins' ) ) ) {
					if ( empty( $literature_header_type ) ) {
						$literature_header_type = literature_get_theme_option( 'header_type' );
					}
					if ( 'custom' == $literature_header_type && literature_is_layouts_available() ) {
						$literature_header_id = literature_get_custom_header_id();
						if ( $literature_header_id > 0 ) {
							$literature_header_meta = literature_get_custom_layout_meta( $literature_header_id );
							if ( ! empty( $literature_header_meta['margin'] ) ) {
								?> page_content_wrap_custom_header_margin<?php
							}
						}
					}
					$literature_footer_type = literature_get_theme_option( 'footer_type' );
					if ( 'custom' == $literature_footer_type && literature_is_layouts_available() ) {
						$literature_footer_id = literature_get_custom_footer_id();
						if ( $literature_footer_id ) {
							$literature_footer_meta = literature_get_custom_layout_meta( $literature_footer_id );
							if ( ! empty( $literature_footer_meta['margin'] ) ) {
								?> page_content_wrap_custom_footer_margin<?php
							}
						}
					}
				}
				do_action( 'literature_action_page_content_wrap_class', $literature_prev_post_loading );
				?>"<?php
				if ( apply_filters( 'literature_filter_is_prev_post_loading', $literature_prev_post_loading ) ) {
					?> data-single-style="<?php echo esc_attr( literature_get_theme_option( 'single_style' ) ); ?>"<?php
				}
				do_action( 'literature_action_page_content_wrap_data', $literature_prev_post_loading );
			?>>
				<?php
				do_action( 'literature_action_page_content_wrap', $literature_full_post_loading || $literature_prev_post_loading );

				// Single posts banner
				if ( apply_filters( 'literature_filter_single_post_header', literature_is_singular( 'post' ) || literature_is_singular( 'attachment' ) ) ) {
					if ( $literature_prev_post_loading ) {
						if ( literature_get_theme_option( 'posts_navigation_scroll_which_block', 'article' ) != 'article' ) {
							do_action( 'literature_action_between_posts' );
						}
					}
					// Single post thumbnail and title
					$literature_path = apply_filters( 'literature_filter_get_template_part', 'templates/single-styles/' . literature_get_theme_option( 'single_style' ) );
					if ( literature_get_file_dir( $literature_path . '.php' ) != '' ) {
						get_template_part( $literature_path );
					}
				}

				// Widgets area above page
				$literature_body_style   = literature_get_theme_option( 'body_style' );
				$literature_widgets_name = literature_get_theme_option( 'widgets_above_page', 'hide' );
				$literature_show_widgets = ! literature_is_off( $literature_widgets_name ) && is_active_sidebar( $literature_widgets_name );
				if ( $literature_show_widgets ) {
					if ( 'fullscreen' != $literature_body_style ) {
						?>
						<div class="content_wrap">
							<?php
					}
					literature_create_widgets_area( 'widgets_above_page' );
					if ( 'fullscreen' != $literature_body_style ) {
						?>
						</div>
						<?php
					}
				}

				// Content area
				do_action( 'literature_action_before_content_wrap' );
				?>
				<div class="content_wrap<?php echo 'fullscreen' == $literature_body_style ? '_fullscreen' : ''; ?>">

					<?php do_action( 'literature_action_content_wrap_start' ); ?>

					<div class="content">
						<?php
						do_action( 'literature_action_page_content_start' );

						// Skip link anchor to fast access to the content from keyboard
						?>
						<span id="content_skip_link_anchor" class="literature_skip_link_anchor"></span>
						<?php
						// Single posts banner between prev/next posts
						if ( ( literature_is_singular( 'post' ) || literature_is_singular( 'attachment' ) )
							&& $literature_prev_post_loading 
							&& literature_get_theme_option( 'posts_navigation_scroll_which_block', 'article' ) == 'article'
						) {
							do_action( 'literature_action_between_posts' );
						}

						// Widgets area above content
						literature_create_widgets_area( 'widgets_above_content' );

						do_action( 'literature_action_page_content_start_text' );
