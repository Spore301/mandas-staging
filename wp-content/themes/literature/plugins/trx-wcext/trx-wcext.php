<?php
/* ThemeRex Woocommerce Extensions support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if ( ! function_exists( 'literature_trx_wcext_theme_setup9' ) ) {
	add_action( 'after_setup_theme', 'literature_trx_wcext_theme_setup9', 9 );
	function literature_trx_wcext_theme_setup9() {
		if ( is_admin() ) {
			add_filter( 'literature_filter_tgmpa_required_plugins', 'literature_trx_wcext_tgmpa_required_plugins' );
			add_filter( 'literature_filter_theme_plugins', 'literature_trx_wcext_theme_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( ! function_exists( 'literature_trx_wcext_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('literature_filter_tgmpa_required_plugins',	'literature_trx_wcext_tgmpa_required_plugins');
	function literature_trx_wcext_tgmpa_required_plugins( $list = array() ) {
		if ( literature_storage_isset( 'required_plugins', 'trx-wcext' ) && literature_storage_get_array( 'required_plugins', 'trx-wcext', 'install' ) !== false && literature_is_theme_activated() ) {
			$path = literature_get_plugin_source_path( 'plugins/trx-wcext/trx-wcext.zip' );
			if ( ! empty( $path ) || literature_get_theme_setting( 'tgmpa_upload' ) ) {
				$list[] = array(
					'name'     => literature_storage_get_array( 'required_plugins', 'trx-wcext', 'title' ),
					'slug'     => 'trx-wcext',
					'source'   => ! empty( $path ) ? $path : 'upload://trx-wcext.zip',
					'version'  => '2.0',
					'required' => false,
				);
			}
		}
		return $list;
	}
}

// Filter theme-supported plugins list
if ( ! function_exists( 'literature_trx_wcext_theme_plugins' ) ) {
	//Handler of the add_filter( 'literature_filter_theme_plugins', 'literature_trx_wcext_theme_plugins' );
	function literature_trx_wcext_theme_plugins( $list = array() ) {
		return literature_add_group_and_logo_to_slave( $list, 'trx-wcext', 'trx-wcext-' );
	}
}



// Check if a plugin is installed and activated
if ( ! function_exists( 'literature_exists_trx_wcext' ) ) {
	function literature_exists_trx_wcext() {
		return class_exists( 'TrxWcext\Plugin' );
	}
}

if ( ! function_exists( 'literature_trx_wcext_theme_features' ) ) {
	add_filter( 'trx_wcext_filter/theme_features', 'literature_trx_wcext_theme_features' );
	/**
	 * Checks whether a specific theme feature is active.
	 *
	 * @param string $feature  The theme feature to check. Possible values:
	 *                         - 'quantity_with_inc_dec_buttons'      - theme adds buttons Inc/Dec around the field quantity in WooCommerce output
	 *                         - 'checkbox_input_with_before_element' - theme decorate :before element in the tag <input type="checkbox">
	 *                         - 'checkbox_label_with_before_element' - theme decorate :before element in the tag <label> after the <input type="checkbox">
	 *                         - 'radio_input_with_before_element'    - theme decorate :before element in the tag <input type="radio">
	 *                         - 'radio_label_with_before_element'    - theme decorate :before element in the tag <label> after the <input type="radio">
	 * 
	 * @return bool
	 */
	function literature_trx_wcext_theme_features( $features ) {
		$features['quantity_with_inc_dec_buttons']      = true;
		$features['checkbox_label_with_before_element'] = true;
		$features['radio_label_with_before_element']    = true;
		return $features;
	}
}


if ( ! function_exists( 'literature_trx_wcext_override_theme_options' ) ) {
	add_action( 'literature_action_override_theme_options', 'literature_trx_wcext_override_theme_options', 20 );
	/**
	 * Override options with stored page meta on 'Shop' pages and single product pages, if they are use the trx-wcext templates.
	 * 
	 * @hooked literature_action_override_theme_options, 20 to override options from the standart shop page
	 */
	function literature_trx_wcext_override_theme_options() {
		if ( ! literature_exists_woocommerce() ) {
			return;
		}
		$id = apply_filters( 'trx_wcext_filter/woocommerce_template_id', 0 );
		if ( 0 < $id ) {
			// Get Theme Options from the shop page
			$shop_meta = get_post_meta( $id, 'literature_options', true );
			// Add (override) with current post (product) options
			if ( is_array( $shop_meta ) && count( $shop_meta ) > 0 ) {
				// Get Theme Options from the current product/page
				$options_meta = literature_storage_get( 'options_meta' );
				if ( is_array( $options_meta ) ) {
					$shop_meta = array_merge( $shop_meta, $options_meta );
				}
				literature_storage_set( 'options_meta', $shop_meta );
			}
		}
	}
}


if ( ! function_exists( 'literature_trx_wcext_detect_blog_mode' ) ) {
	add_filter( 'literature_filter_detect_blog_mode', 'literature_trx_wcext_detect_blog_mode' );
	/**
	 * Override the 'blog_mode' on plugin-specific pages
	 * 
	 * @hooked literature_filter_detect_blog_mode
	 */
	function literature_trx_wcext_detect_blog_mode( $mode = '' ) {
		if ( literature_exists_woocommerce() ) {
			if ( $mode != 'shop' && apply_filters( 'trx_wcext_filter/is_woocommerce_template', false ) ) {
				return 'shop';
			}
		}
		return $mode;
	}
}

if ( ! function_exists( 'literature_trx_wcext_theme_setup3' ) ) {
	add_action( 'after_setup_theme', 'literature_trx_wcext_theme_setup3', 3 );
	/**
	 * Add 'woo-template' to the 'override' list in the Theme Options
	 * 
	 * @hooked literature_action_after_setup_theme, 3 to modify Theme Options
	 */
	function literature_trx_wcext_theme_setup3() {
		if ( ! literature_exists_woocommerce() || ! literature_exists_trx_wcext() ) {
			return;
		}

		global $LITERATURE_STORAGE;

		if ( ! isset( $LITERATURE_STORAGE['options'] ) || ! is_array( $LITERATURE_STORAGE['options'] ) ) {
			return;
		}

		foreach ( $LITERATURE_STORAGE['options'] as $k => $v ) {
			if ( ! empty( $v['override']['mode'] ) && strpos( $v['override']['mode'], 'page' ) !== false && $v['override']['mode'] != 'page' ) {
				$LITERATURE_STORAGE['options'][ $k ]['override']['mode'] .= ',woo-template';
			}
		}
	}
}

// Check if meta box is allowed
if ( ! function_exists( 'literature_trx_wcext_allow_override_options' ) ) {
	if ( ! LITERATURE_THEME_FREE ) {
		add_filter( 'literature_filter_allow_override_options', 'literature_trx_wcext_allow_override_options', 10, 2 );
	}
	/**
	 * Allow override Theme Options when the post type 'woo-template' is editing.
	 * 
	 * @hooked literature_filter_allow_override_options
	 */
	function literature_trx_wcext_allow_override_options( $allow, $post_type ) {
		return $allow || 'woo-template' == $post_type;
	}
}

if ( ! function_exists( 'literature_trx_wcext_import_page_settings' ) ) {
	add_action( 'trx_wcext_action/woo_template_import_page_settings', 'literature_trx_wcext_import_page_settings', 10, 2 );
	/**
	 * Override a page meta with Theme Options from the Elementor meta data after import
	 * 
	 * @hooked trx_wcext_action/woo_template_import_page_settings
	 */
	function literature_trx_wcext_import_page_settings( $post_id, $page_settings ) {
		if ( ! literature_exists_woocommerce() || ! literature_exists_trx_wcext() || ! literature_exists_elementor() || ! function_exists( 'literature_elm_page_options_save' ) ) {
			return;
		}
		literature_elm_page_options_save( false, $post_id, $page_settings );	// First parameter is not used in this case
	}
}
