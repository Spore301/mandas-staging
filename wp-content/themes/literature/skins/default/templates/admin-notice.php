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
<div class="literature_admin_notice literature_welcome_notice notice notice-info is-dismissible" data-notice="admin">
	<?php
	// Theme image
	$literature_theme_img = literature_get_file_url( 'screenshot.jpg' );
	if ( '' != $literature_theme_img ) {
		?>
		<div class="literature_notice_image"><img src="<?php echo esc_url( $literature_theme_img ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'literature' ); ?>"></div>
		<?php
	}

	// Title
	?>
	<h3 class="literature_notice_title">
		<?php
		echo esc_html(
			sprintf(
				// Translators: Add theme name and version to the 'Welcome' message
				__( 'Welcome to %1$s v.%2$s', 'literature' ),
				$literature_theme_obj->get( 'Name' ) . ( LITERATURE_THEME_FREE ? ' ' . __( 'Free', 'literature' ) : '' ),
				$literature_theme_obj->get( 'Version' )
			)
		);
		?>
	</h3>
	<?php

	// Description
	?>
	<div class="literature_notice_text">
		<p class="literature_notice_text_description">
			<?php
			echo str_replace( '. ', '.<br>', wp_kses_data( $literature_theme_obj->description ) );
			?>
		</p>
		<p class="literature_notice_text_info">
			<?php
			echo wp_kses_data( __( 'Attention! Plugin "ThemeREX Addons" is required! Please, install and activate it!', 'literature' ) );
			?>
		</p>
	</div>
	<?php

	// Buttons
	?>
	<div class="literature_notice_buttons">
		<?php
		// Link to the page 'About Theme'
		?>
		<a href="<?php echo esc_url( admin_url() . 'themes.php?page=literature_about' ); ?>" class="button button-primary"><i class="dashicons dashicons-nametag"></i> 
			<?php
			echo esc_html__( 'Install plugin "ThemeREX Addons"', 'literature' );
			?>
		</a>
	</div>
</div>
