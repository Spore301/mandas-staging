<?php
/**
 * Required plugins
 *
 * @package LITERATURE
 * @since LITERATURE 1.76.0
 */

// THEME-SUPPORTED PLUGINS
// If plugin not need - remove its settings from next array
//----------------------------------------------------------
if ( ! function_exists( 'literature_skin_required_plugins' ) ) {
	add_action( 'after_setup_theme', 'literature_skin_required_plugins', -1 );
	function literature_skin_required_plugins() {
		$literature_theme_required_plugins_groups = array(
			'core'          => esc_html__( 'Core', 'literature' ),
			'page_builders' => esc_html__( 'Page Builders', 'literature' ),
			'ecommerce'     => esc_html__( 'E-Commerce & Donations', 'literature' ),
			'socials'       => esc_html__( 'Socials and Communities', 'literature' ),
			'events'        => esc_html__( 'Events and Appointments', 'literature' ),
			'content'       => esc_html__( 'Content', 'literature' ),
			'other'         => esc_html__( 'Other', 'literature' ),
		);
		$literature_theme_required_plugins        = array(
			// Core
			'trx_addons'                 => array(
				'title'       => esc_html__( 'ThemeREX Addons', 'literature' ),
				'description' => esc_html__( "Will allow you to install recommended plugins, demo content, and improve the theme's functionality overall with multiple theme options", 'literature' ),
				'required'    => true, // Check this plugin in the list on load Theme Dashboard
				'logo'        => 'trx_addons.png',
				'group'       => $literature_theme_required_plugins_groups['core'],
			),
			// Page Builders
			'elementor'                  => array(
				'title'       => esc_html__( 'Elementor', 'literature' ),
				'description' => esc_html__( "Is a beautiful PageBuilder, even the free version of which allows you to create great pages using a variety of modules.", 'literature' ),
				'required'    => false, // Leave this plugin unchecked on load Theme Dashboard
				'logo'        => 'elementor.png',
				'group'       => $literature_theme_required_plugins_groups['page_builders'],
			),
			'gutenberg'                  => array(
				'title'       => esc_html__( 'Gutenberg', 'literature' ),
				'description' => esc_html__( "It's a posts editor coming in place of the classic TinyMCE. Can be installed and used in parallel with Elementor", 'literature' ),
				'required'    => false,
				'install'     => false, // Do not offer installation of the plugin in the Theme Dashboard and TGMPA
				'logo'        => 'gutenberg.png',
				'group'       => $literature_theme_required_plugins_groups['page_builders'],
			),
			// Content
			'sitepress-multilingual-cms' => array(
				'title'       => esc_html__( 'WPML - Sitepress Multilingual CMS', 'literature' ),
				'description' => esc_html__( "Allows you to make your website multilingual", 'literature' ),
				'required'    => false,
				'install'     => false, // Do not offer installation of the plugin in the Theme Dashboard and TGMPA
				'logo'        => 'sitepress-multilingual-cms.png',
				'group'       => $literature_theme_required_plugins_groups['content'],
			),
			'metform'                    => array(
				'title'       => esc_html__( 'MetForm', 'literature' ),
				'description' => esc_html__( "Contact Form, Survey, Quiz, & Custom Form Builder for Elementor", 'literature' ),
				'required'    => false,
				'logo'        => 'metform.png',
				'group'       => $literature_theme_required_plugins_groups['content'],
			),
			'woocommerce'                => array(
				'title'       => esc_html__( 'WooCommerce', 'literature' ),
				'description' => esc_html__( "Connect the store to your website and start selling now", 'literature' ),
				'required'    => false,
				'install'     => false, // Do not offer installation of the plugin in the Theme Dashboard and TGMPA
				'logo'        => 'woocommerce.png',
				'group'       => $literature_theme_required_plugins_groups['ecommerce'],
			),
			// Other
			'trx_updater'                => array(
				'title'       => esc_html__( 'ThemeREX Updater', 'literature' ),
				'description' => esc_html__( "Update theme and theme-specific plugins from developer's upgrade server.", 'literature' ),
				'required'    => false,
				'logo'        => 'trx_updater.png',
				'group'       => $literature_theme_required_plugins_groups['other'],
			)
		);

		if ( LITERATURE_THEME_FREE ) {
			unset( $literature_theme_required_plugins['sitepress-multilingual-cms'] );
			unset( $literature_theme_required_plugins['trx_updater'] );
		}

		// Add plugins list to the global storage
		literature_storage_set( 'required_plugins', $literature_theme_required_plugins );
	}
}
