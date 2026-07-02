<?php
/* Theme-specific action to configure ThemeREX Addons components
------------------------------------------------------------------------------- */


/* ThemeREX Addons components
------------------------------------------------------------------------------- */
if ( ! function_exists( 'literature_trx_addons_theme_specific_components' ) ) {
	add_filter( 'trx_addons_filter_components_editor', 'literature_trx_addons_theme_specific_components' );
	function literature_trx_addons_theme_specific_components( $enable = false ) {
		return LITERATURE_THEME_FREE
					? false     // Free version
					: false;    // Pro version or Developer mode
	}
}

if ( ! function_exists( 'literature_trx_addons_theme_specific_setup1' ) ) {
	add_action( 'after_setup_theme', 'literature_trx_addons_theme_specific_setup1', 1 );
	function literature_trx_addons_theme_specific_setup1() {
		if ( literature_exists_trx_addons() ) {
			add_filter( 'trx_addons_api_list', 'literature_trx_addons_api_list' );
			add_filter( 'trx_addons_cpt_list', 'literature_trx_addons_cpt_list' );
			add_filter( 'trx_addons_sc_list', 'literature_trx_addons_sc_list' );
			add_filter( 'trx_addons_widgets_list', 'literature_trx_addons_widgets_list' );
		}
	}
}

if ( ! function_exists( 'literature_trx_addons_theme_specific_setup2' ) ) {
	add_action( 'after_setup_theme', 'literature_trx_addons_theme_specific_setup2', 2 );
	function literature_trx_addons_theme_specific_setup2() {
		if ( literature_exists_trx_addons() ) {
			// Disable Blog Layouts to the blog styles list
			// Alternative - pass the "false" parameter to the function: $list = literature_get_list_blog_styles( strpos( $id, 'blog_style_' ) === 0 /*, 'arh', false */ ); - located in the function -  literature_options_get_list_choises
			remove_action( 'literature_filter_list_blog_styles', 'literature_trx_addons_list_blog_styles' );
		}
	}
}


/* CPT Layout types
------------------------------------------------------------------------------- */
// Disable Blog Layouts
if ( ! function_exists( 'literature_trx_addons_filter_layout_types' ) ) {
	add_filter( 'trx_addons_filter_layout_types', 'literature_trx_addons_filter_layout_types' );
	function literature_trx_addons_filter_layout_types( $list_layout_types ) {
		if ( is_array( $list_layout_types ) ) {
			unset( $list_layout_types[ 'blog' ] );
		}
		return $list_layout_types;
	}
}

// API
if ( ! function_exists( 'literature_trx_addons_api_list' ) ) {
	//Handler of the add_filter('trx_addons_api_list',	'literature_trx_addons_api_list');
	function literature_trx_addons_api_list( $list = array() ) {
		// To do: Enable/Disable Third-party plugins API via add/remove it in the list

		// If it's a free version - leave only basic set
		if ( LITERATURE_THEME_FREE ) {
			$free_api = array( 'gutenberg', 'elementor', 'contact-form-7', 'instagram_feed', 'woocommerce' );
			foreach ( $list as $k => $v ) {
				if ( ! in_array( $k, $free_api ) ) {
					unset( $list[ $k ] );
				}
			}
		}
		return $list;
	}
}

// CPT
if ( ! function_exists( 'literature_trx_addons_cpt_list' ) ) {
	//Handler of the add_filter('trx_addons_cpt_list',	'literature_trx_addons_cpt_list');
	function literature_trx_addons_cpt_list( $list = array() ) {
		// To do: Enable/Disable CPT via add/remove it in the list

		// If it's a free version - leave only basic set
		if ( LITERATURE_THEME_FREE ) {
			$free_cpt = array( 'layouts', 'portfolio', 'post', 'services', 'team', 'testimonials' );
			foreach ( $list as $k => $v ) {
				if ( ! in_array( $k, $free_cpt ) ) {
					unset( $list[ $k ] );
				}
			}
		}
		return $list;
	}
}

// Shortcodes
if ( ! function_exists( 'literature_trx_addons_sc_list' ) ) {
	//Handler of the add_filter('trx_addons_sc_list',	'literature_trx_addons_sc_list');
	function literature_trx_addons_sc_list( $list = array() ) {
		// To do: Add/Remove shortcodes into list
		// If you add new shortcode - in the theme's folder must exists /trx_addons/shortcodes/new_sc_name/new_sc_name.php

		// If it's a free version - leave only basic set
		if ( LITERATURE_THEME_FREE ) {
			$free_shortcodes = array( 'action', 'anchor', 'blogger', 'button', 'form', 'icons', 'price', 'promo', 'socials' );
			foreach ( $list as $k => $v ) {
				if ( ! in_array( $k, $free_shortcodes ) ) {
					unset( $list[ $k ] );
				}
			}
		}
		return $list;
	}
}

// Widgets
if ( ! function_exists( 'literature_trx_addons_widgets_list' ) ) {
	//Handler of the add_filter('trx_addons_widgets_list',	'literature_trx_addons_widgets_list');
	function literature_trx_addons_widgets_list( $list = array() ) {
		// To do: Add/Remove widgets into list
		// If you add widget - in the theme's folder must exists /trx_addons/widgets/new_widget_name/new_widget_name.php

		// If it's a free version - leave only basic set
		if ( LITERATURE_THEME_FREE ) {
			$free_widgets = array( 'aboutme', 'banner', 'contacts', 'flickr', 'popular_posts', 'recent_posts', 'slider', 'socials' );
			foreach ( $list as $k => $v ) {
				if ( ! in_array( $k, $free_widgets ) ) {
					unset( $list[ $k ] );
				}
			}
		}
		return $list;
	}
}

// Add mobile menu to the plugin's cached menu list
if ( ! function_exists( 'literature_trx_addons_menu_cache' ) ) {
	add_filter( 'trx_addons_filter_menu_cache', 'literature_trx_addons_menu_cache' );
	function literature_trx_addons_menu_cache( $list = array() ) {
		if ( in_array( '#menu_main', $list ) ) {
			$list[] = '#menu_mobile';
		}
		$list[] = '.menu_mobile_inner > nav > ul';
		return $list;
	}
}

// Add theme-specific vars into localize array
if ( ! function_exists( 'literature_trx_addons_localize_script' ) ) {
	add_filter( 'literature_filter_localize_script', 'literature_trx_addons_localize_script' );
	function literature_trx_addons_localize_script( $arr ) {
		$arr['alter_link_color'] = literature_get_scheme_color( 'link' ); // for sc_countdown_item -> canvas ( \trx_addons.js )
		return $arr;
	}
}


// CPT meta box
//------------------------------------------------------------------------

// Disable banner in the single post (banners parameters to the Meta Box support)
if ( ! function_exists( 'literature_trx_addons_disable_allow_post_banners' ) ) {
	add_filter( 'trx_addons_filter_allow_post_banners', 'literature_trx_addons_disable_allow_post_banners' );
	function literature_trx_addons_disable_allow_post_banners() {
		return false;
	}
}


// Shortcodes support
//------------------------------------------------------------------------

// Add classes to the shortcode's output from new params
if ( ! function_exists( 'literature_trx_addons_sc_output' ) ) {
	add_filter( 'trx_addons_sc_output', 'literature_trx_addons_sc_output', 10, 4 );
	function literature_trx_addons_sc_output( $output, $sc, $atts, $content ) {
		$sc = str_replace( array( 'trx_widget', 'trx_' ), array( 'sc_widget', '' ), $sc );
		if ( substr( $sc, -3 ) == 'map' ) {
			$sc = str_replace( 'map', 'map_content', $sc );
		}
		return $output;
	}
}

// Add new styles to the Google map
if ( ! function_exists( 'literature_trx_addons_sc_googlemap_styles' ) ) {
	add_filter( 'trx_addons_filter_sc_googlemap_styles', 'literature_trx_addons_sc_googlemap_styles' );
	function literature_trx_addons_sc_googlemap_styles( $list ) {
		$list['dark'] = esc_html__( 'Dark', 'literature' ); // theme specific style in core - \plugins\trx_addons\trx_addons.js
		$list['extra'] = esc_html__( 'Extra', 'literature' ); // skin specific style in skin - \skins\default\skin.js
		return $list;
	}
}

// Remove params "Row type" -> section: 'Custom Layouts'
if (!function_exists('literature_trx_addons_cpt_layouts_elm_add_params_in_standard_elements')) {
    add_action( 'elementor/element/after_section_end', 'literature_trx_addons_cpt_layouts_elm_add_params_in_standard_elements', 11, 3 );
    function literature_trx_addons_cpt_layouts_elm_add_params_in_standard_elements( $element, $section_id, $args )  {
        if ( is_object( $element ) ) {
            $el_name = $element->get_name();
			if ( ( in_array( $el_name, array('section') ) && $section_id == 'section_layout' ) || ( in_array( $el_name, array( 'container' ) ) && $section_id == 'section_layout_container' ) ) {
				$element->remove_control( 'row_type' );
			}
		}
	}
}

// Remove params 'Extend background' and 'Background mask' from the Elementor's sections
if ( ! function_exists( 'literature_trx_addons_elm_add_params_extend_bg' ) ) {
	add_action( 'elementor/element/before_section_end', 'literature_trx_addons_elm_add_params_extend_bg', 11, 3 );
	function literature_trx_addons_elm_add_params_extend_bg( $element, $section_id, $args )  {
        if ( is_object( $element ) ) {
            $el_name = $element->get_name();
			if ( ( $el_name == 'section' && $section_id == 'section_background' ) || ( $el_name == 'column' && $section_id == 'section_style' ) || ( $el_name == 'text-editor' && $section_id == 'section_background' ) || ( $el_name == 'container' && $section_id == 'section_background' ) ) {
				$element->remove_control( 'extra_bg' );
				$element->remove_control( 'extra_bg_mask' );
			}
		}
	}
}

// TRX Setup
//------------------------------------------------------------------------

// Disable extended emotions
if ( ! function_exists( 'literature_trx_addons_allow_emotions_settings' ) ) {
	add_filter( 'trx_addons_filter_allow_emotions_settings', 'literature_trx_addons_allow_emotions_settings' );
	function literature_trx_addons_allow_emotions_settings() {
		return false;
	}
}

// Disable "Menu & Search" block settings
if ( ! function_exists( 'literature_trx_addons_allow_menu_and_search' ) ) {
	add_filter( 'trx_addons_filter_allow_menu_and_search', 'literature_trx_addons_allow_menu_and_search' );
	function literature_trx_addons_allow_menu_and_search() {
		return false;
	}
}

// Change skin-specific variables to the scripts (Stretch menu layouts - ON)
if ( ! function_exists( 'literature_trx_addons_filter_localize_script' ) ) {
	add_filter( 'trx_addons_filter_localize_script', 'literature_trx_addons_filter_localize_script');
	function literature_trx_addons_filter_localize_script( $arr ) {
		$arr['menu_stretch'] = 1;
		return $arr;
	}
}

// Disable "Tabs" settings
if ( ! function_exists( 'literature_trx_addons_allow_layouts_in_tabs' ) ) {
	add_filter( 'trx_addons_filter_allow_layouts_in_tabs', 'literature_trx_addons_allow_layouts_in_tabs' );
	function literature_trx_addons_allow_layouts_in_tabs() {
		return false;
	}
}

// Disable "Input field's hover" settings
if ( ! function_exists( 'literature_trx_addons_allow_input_hover' ) ) {
	add_filter( 'trx_addons_filter_allow_input_hover', 'literature_trx_addons_allow_input_hover' );
	function literature_trx_addons_allow_input_hover() {
		return false;
	}
}

// Disable "Disable new Widgets Block Editor" settings
if ( ! function_exists( 'literature_trx_addons_allow_disable_widgets_block_editor' ) ) {
	add_filter( 'trx_addons_filter_allow_disable_widgets_block_editor', 'literature_trx_addons_allow_disable_widgets_block_editor' );
	function literature_trx_addons_allow_disable_widgets_block_editor() {
		return false;
	}
}

// Disable "Columns Grid" settings
if ( ! function_exists( 'literature_trx_addons_allow_theme_columns' ) ) {
	add_filter( 'trx_addons_filter_allow_theme_columns', 'literature_trx_addons_allow_theme_columns' );
	function literature_trx_addons_allow_theme_columns() {
		return false;
	}
}

// Disable "Posts selector" settings
if ( ! function_exists( 'literature_trx_addons_allow_ajax_posts_selector' ) ) {
	add_filter( 'trx_addons_filter_allow_ajax_posts_selector', 'literature_trx_addons_allow_ajax_posts_selector' );
	function literature_trx_addons_allow_ajax_posts_selector() {
		return false;
	}
}

// Layout Settings "Fill Background" - Off
if ( ! function_exists( 'literature_trx_addons_filter_layout_fill_bg' ) ) {
	add_filter( 'trx_addons_filter_layout_fill_bg', 'literature_trx_addons_filter_layout_fill_bg' );
	function literature_trx_addons_filter_layout_fill_bg() {
		return '';
	}
}

// List with plugin-specific thumb sizes
if ( ! function_exists( 'literature_trx_addons_filter_add_thumb_sizes' ) ) {
	add_filter( 'trx_addons_filter_add_thumb_sizes', 'literature_trx_addons_filter_add_thumb_sizes' );
	add_filter( 'trx_addons_filter_add_thumb_names', 'literature_trx_addons_filter_add_thumb_sizes' );
	function literature_trx_addons_filter_add_thumb_sizes( $thumb_size_list = array() ) {
		if ( is_array( $thumb_size_list ) ) {
			unset( $thumb_size_list[ 'trx_addons-thumb-portrait' ] ); // not used
			unset( $thumb_size_list[ 'trx_addons-thumb-small' ] );    // used in shortcode "action"
		}
		return $thumb_size_list;
	}
}
