<?php
/**
 * The template to display Admin notices
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.64
 */

$literature_skins_url  = get_admin_url( null, 'admin.php?page=trx_addons_theme_panel#trx_addons_theme_panel_section_skins' );
$literature_skins_args = get_query_var( 'literature_skins_notice_args' );
?>
<div class="literature_admin_notice literature_skins_notice notice notice-info is-dismissible" data-notice="skins">
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
		<?php esc_html_e( 'New skins are available', 'literature' ); ?>
	</h3>
	<?php

	// Description
	$literature_total      = $literature_skins_args['update'];	// Store value to the separate variable to avoid warnings from ThemeCheck plugin!
	$literature_skins_msg  = $literature_total > 0
							// Translators: Add new skins number
							? '<strong>' . sprintf( _n( '%d new version', '%d new versions', $literature_total, 'literature' ), $literature_total ) . '</strong>'
							: '';
	$literature_total      = $literature_skins_args['free'];
	$literature_skins_msg .= $literature_total > 0
							? ( ! empty( $literature_skins_msg ) ? ' ' . esc_html__( 'and', 'literature' ) . ' ' : '' )
								// Translators: Add new skins number
								. '<strong>' . sprintf( _n( '%d free skin', '%d free skins', $literature_total, 'literature' ), $literature_total ) . '</strong>'
							: '';
	$literature_total      = $literature_skins_args['pay'];
	$literature_skins_msg .= $literature_skins_args['pay'] > 0
							? ( ! empty( $literature_skins_msg ) ? ' ' . esc_html__( 'and', 'literature' ) . ' ' : '' )
								// Translators: Add new skins number
								. '<strong>' . sprintf( _n( '%d paid skin', '%d paid skins', $literature_total, 'literature' ), $literature_total ) . '</strong>'
							: '';
	?>
	<div class="literature_notice_text">
		<p>
			<?php
			// Translators: Add new skins info
			echo wp_kses_data( sprintf( __( "We are pleased to announce that %s are available for your theme", 'literature' ), $literature_skins_msg ) );
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
			esc_html_e( 'Go to Skins manager', 'literature' );
			?>
		</a>
		<?php
		// Dismiss notice for 7 days
		?>
		<a href="#" role="button" class="button button-secondary literature_notice_button_dismiss" data-notice="skins"><i class="dashicons dashicons-no-alt"></i> 
			<?php
			esc_html_e( 'Dismiss', 'literature' );
			?>
		</a>
		<?php
		// Hide notice forever
		?>
		<a href="#" role="button" class="button button-secondary literature_notice_button_hide" data-notice="skins"><i class="dashicons dashicons-no-alt"></i> 
			<?php
			esc_html_e( 'Never show again', 'literature' );
			?>
		</a>
	</div>
</div>
