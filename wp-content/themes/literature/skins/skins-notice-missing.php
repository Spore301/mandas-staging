<?php
/**
 * The template to display Admin notices
 *
 * @package LITERATURE
 * @since LITERATURE 1.98.0
 */

$literature_skins_url   = get_admin_url( null, 'admin.php?page=trx_addons_theme_panel#trx_addons_theme_panel_section_skins' );
$literature_active_skin = literature_skins_get_active_skin_name();
?>
<div class="literature_admin_notice literature_skins_notice notice notice-error">
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
		<?php esc_html_e( 'Active skin is missing!', 'literature' ); ?>
	</h3>
	<div class="literature_notice_text">
		<p>
			<?php
			// Translators: Add a current skin name to the message
			echo wp_kses_data( sprintf( __( "Your active skin <b>'%s'</b> is missing. Usually this happens when the theme is updated directly through the server or FTP.", 'literature' ), ucfirst( $literature_active_skin ) ) );
			?>
		</p>
		<p>
			<?php
			echo wp_kses_data( __( "Please use only <b>'ThemeREX Updater v.1.6.0+'</b> plugin for your future updates.", 'literature' ) );
			?>
		</p>
		<p>
			<?php
			echo wp_kses_data( __( "But no worries! You can re-download the skin via 'Skins Manager' ( Theme Panel - Theme Dashboard - Skins ).", 'literature' ) );
			?>
		</p>
	</div>
	<?php

	// Buttons
	?>
	<div class="literature_notice_buttons">
		<?php
		// Link to the theme dashboard page
		?>
		<a href="<?php echo esc_url( $literature_skins_url ); ?>" class="button button-primary"><i class="dashicons dashicons-update"></i> 
			<?php
			// Translators: Add theme name
			esc_html_e( 'Go to Skins manager', 'literature' );
			?>
		</a>
	</div>
</div>
