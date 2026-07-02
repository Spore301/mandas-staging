<?php
/**
 * The template to display Admin notices
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.1
 */

$literature_theme_slug = get_template();
$literature_theme_obj  = wp_get_theme( $literature_theme_slug );

?>
<div class="literature_admin_notice literature_rate_notice notice notice-info is-dismissible" data-notice="rate">
	<?php
	// Theme image
	$literature_theme_img = literature_get_file_url( 'screenshot.jpg' );
	if ( '' != $literature_theme_img ) {
		?>
		<div class="literature_notice_image"><img src="<?php echo esc_url( $literature_theme_img ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'literature' ); ?>"></div>
		<?php
	}

	// Title
	$literature_theme_name = '"' . $literature_theme_obj->get( 'Name' ) . ( LITERATURE_THEME_FREE ? ' ' . __( 'Free', 'literature' ) : '' ) . '"';
	?>
	<h3 class="literature_notice_title"><a href="<?php echo esc_url( literature_storage_get( 'theme_rate_url' ) ); ?>"<?php if ( function_exists( 'literature_external_links_target' ) ) echo literature_external_links_target( true ); ?>>
		<?php
		echo esc_html(
			sprintf(
				// Translators: Add theme name to the 'Welcome' message
				__( 'Help Us Grow - Rate %s Today!', 'literature' ),
				$literature_theme_name
			)
		);
		?>
	</a></h3>
	<?php

	// Description
	?>
	<div class="literature_notice_text">
		<p><?php
			// Translators: Add theme name to the 'Welcome' message
			echo wp_kses_data( sprintf( __( "Thank you for choosing the %s theme for your website! We're excited to see how you've customized your site, and we hope you've enjoyed working with our theme.", 'literature' ), $literature_theme_name ) );
		?></p>
		<p><?php
			// Translators: Add theme name to the 'Welcome' message
			echo wp_kses_data( sprintf( __( "Your feedback really matters to us! If you've had a positive experience, we'd love for you to take a moment to rate %s and share your thoughts on the customer service you received.", 'literature' ), $literature_theme_name ) );
		?></p>
	</div>
	<?php

	// Buttons
	?>
	<div class="literature_notice_buttons">
		<?php
		// Link to the theme download page
		?>
		<a href="<?php echo esc_url( literature_storage_get( 'theme_rate_url' ) ); ?>" class="button button-primary"<?php if ( function_exists( 'literature_external_links_target' ) ) echo literature_external_links_target( true ); ?>><i class="dashicons dashicons-star-filled"></i> 
			<?php
			// Translators: Add the theme name to the button caption
			echo esc_html( sprintf( __( 'Rate %s Now', 'literature' ), $literature_theme_name ) );
			?>
		</a>
		<?php
		// Link to the theme support
		?>
		<a href="<?php echo esc_url( literature_storage_get( 'theme_support_url' ) ); ?>" class="button"<?php if ( function_exists( 'literature_external_links_target' ) ) echo literature_external_links_target( true ); ?>><i class="dashicons dashicons-sos"></i> 
			<?php
			esc_html_e( 'Support', 'literature' );
			?>
		</a>
		<?php
		// Link to the theme documentation
		?>
		<a href="<?php echo esc_url( literature_storage_get( 'theme_doc_url' ) ); ?>" class="button"<?php if ( function_exists( 'literature_external_links_target' ) ) echo literature_external_links_target( true ); ?>><i class="dashicons dashicons-book"></i> 
			<?php
			esc_html_e( 'Documentation', 'literature' );
			?>
		</a>
	</div>
</div>
