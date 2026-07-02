<?php
/**
 * Skins support: Main skin file for the skin 'Literature'
 *
 * Load scripts and styles,
 * and other operations that affect the appearance and behavior of the theme
 * when the skin is activated
 *
 * @package LITERATURE
 * @since LITERATURE 2.30.1
 */


// SKIN SETUP
//--------------------------------------------------------------------

// Setup fonts, colors, blog and single styles, etc.
$literature_skin_path = literature_get_file_dir( literature_skins_get_current_skin_dir() . 'skin-setup.php' );
if ( ! empty( $literature_skin_path ) ) {
	require_once $literature_skin_path;
}

// Skin options
$literature_skin_path = literature_get_file_dir( literature_skins_get_current_skin_dir() . 'skin-options.php' );
if ( ! empty( $literature_skin_path ) ) {
	require_once $literature_skin_path;
}

// Required plugins
$literature_skin_path = literature_get_file_dir( literature_skins_get_current_skin_dir() . 'skin-plugins.php' );
if ( ! empty( $literature_skin_path ) ) {
	require_once $literature_skin_path;
}

// Demo import
$literature_skin_path = literature_get_file_dir( literature_skins_get_current_skin_dir() . 'skin-demo-importer.php' );
if ( ! empty( $literature_skin_path ) ) {
	require_once $literature_skin_path;
}

// If separate single styles are supported with a current skin - return true to place its to the stand-alone files
// '__single.css' with general styles for single posts
// '__single-responsive.css' with responsive styles for single posts
if ( ! function_exists( 'literature_skin_allow_separate_single_styles' ) ) {
	add_filter( 'literature_filters_separate_single_styles', 'literature_skin_allow_separate_single_styles' );
	function literature_skin_allow_separate_single_styles( $allow ) {
		return true;
	}
}

// If separate ThemeREX Addons styles are supported with a current skin - return true to place its to the stand-alone files
// inside a skin's folder "skins/skin-slug/plugins/trx_addons/components".
// For example: "skins/default/plugins/trx_addons/components/sc-blogger.css" and "sc-blogger-responsive.css"
if ( ! function_exists( 'literature_skin_allow_separate_trx_addons_styles' ) ) {
	add_filter( 'literature_filters_separate_trx_addons_styles', 'literature_skin_allow_separate_trx_addons_styles' );
	function literature_skin_allow_separate_trx_addons_styles( $allow ) {
		return true;
	}
}

// If separate ThemeREX Addons styles are supported with a current skin - return a list of components,
// who have a separate css files inside a skin's folder "plugins/trx_addons/components".
// For example:
// 		'cpt_cars', 'cpt_courses', 'cpt_dishes', 'cpt_portfolio', 'cpt_properties', 'cpt_services', 'cpt_sport',
//		'cpt_team', 'cpt_testimonials',
//		'sc_accordionposts', 'sc_action', 'sc_anchor', 'sc_blogger', 'sc_content', 'sc_countdown', 'sc_cover',
//		'sc_googlemap', 'sc_hotspot', 'sc_icompare', 'sc_icons', 'sc_osmap', 'sc_price', 'sc_promo', 'sc_skills',
//		'sc_socials', 'sc_supertitle', 'sc_table', 'sc_users',
//		'widget_aboutme', 'widget_audio', 'widget_banner', 'widget_categories_list', 'widget_contacts',
//		'widget_custom_links', 'widget_flickr', 'widget_instagram', 'widget_recent_news', 'widget_socials',
//		'widget_twitter', 'widget_video', 'widget_video_list'
if ( ! function_exists( 'literature_skin_separate_trx_addons_styles_list' ) ) {
	add_filter( 'literature_filters_separate_trx_addons_styles_list', 'literature_skin_separate_trx_addons_styles_list' );
	function literature_skin_separate_trx_addons_styles_list( $list ) {
		return array(
			'cpt_cars', 'cpt_courses', 'cpt_dishes', 'cpt_portfolio', 'cpt_properties', 'cpt_services', 'cpt_sport',
			'cpt_team', 'cpt_testimonials',
			'sc_action', 'sc_blogger', 'sc_countdown', 'sc_googlemap', 'sc_hotspot', 'sc_icons', 'sc_osmap', 'sc_price',
			'sc_promo', 'sc_skills', 'sc_switcher', 'sc_users',
			'widget_categories_list', 'widget_contacts', 'widget_recent_news', 'widget_twitter',
		);
	}
}


// Filter to add in the required plugins list
// Priority 11 to add new plugins to the end of the list
if ( ! function_exists( 'literature_skin_tgmpa_required_plugins' ) ) {
	add_filter( 'literature_filter_tgmpa_required_plugins', 'literature_skin_tgmpa_required_plugins', 11 );
	function literature_skin_tgmpa_required_plugins( $list = array() ) {
		// ToDo: Check if plugin is in the 'required_plugins' and add his parameters to the TGMPA-list
		//       Replace 'skin-specific-plugin-slug' to the real slug of the plugin
		if ( literature_storage_isset( 'required_plugins', 'skin-specific-plugin-slug' ) ) {
			$list[] = array(
				'name'     => literature_storage_get_array( 'required_plugins', 'skin-specific-plugin-slug', 'title' ),
				'slug'     => 'skin-specific-plugin-slug',
				'required' => false,
			);
		}
		return $list;
	}
}



// TRX_ADDONS SETUP
//--------------------------------------------------------------------

// Filter to add/remove components of ThemeREX Addons when current skin is active
if ( ! function_exists( 'literature_skin_trx_addons_default_components' ) ) {
	add_filter( 'trx_addons_filter_load_options', 'literature_skin_trx_addons_default_components', 20 );
	function literature_skin_trx_addons_default_components($components) {
		// ToDo: Set key value in the array $components to 0 (disable component) or 1 (enable component)
		//---> For example (enable reviews for posts):
		//---> $components['components_components_reviews'] = 1;
		return $components;
	}
}

// Filter to add/remove CPT
if ( ! function_exists( 'literature_skin_trx_addons_cpt_list' ) ) {
	add_filter( 'trx_addons_cpt_list', 'literature_skin_trx_addons_cpt_list' );
	function literature_skin_trx_addons_cpt_list( $list = array() ) {
		// ToDo: Unset CPT slug from list to disable CPT when current skin is active
		//---> For example to disable CPT 'Portfolio':
		//---> unset( $list['portfolio'] );
		return $list;
	}
}

// Filter to add/remove shortcodes
if ( ! function_exists( 'literature_skin_trx_addons_sc_list' ) ) {
	add_filter( 'trx_addons_sc_list', 'literature_skin_trx_addons_sc_list' );
	function literature_skin_trx_addons_sc_list( $list = array() ) {
		// ToDo: Unset shortcode's slug from list to disable shortcode when current skin is active
		//---> For example to disable shortcode 'Action':
		//---> unset( $list['action'] );

		// Also can be used to add/remove/modify shortcodes params
		//---> For example to add new template to the 'Blogger':
		//---> $list['blogger']['templates']['default']['new_template_slug'] = array(
		//--->		'title' => __('Title of the new template', 'literature'),
		//--->		'layout' => array(
		//--->			'featured' => array(),
		//--->			'content' => array('meta_categories', 'title', 'excerpt', 'meta', 'readmore')
		//--->		)
		//---> );
		return $list;
	}
}

// Filter to add/remove widgets
if ( ! function_exists( 'literature_skin_trx_addons_widgets_list' ) ) {
	add_filter( 'trx_addons_widgets_list', 'literature_skin_trx_addons_widgets_list' );
	function literature_skin_trx_addons_widgets_list( $list = array() ) {
		// ToDo: Unset widget's slug from list to disable widget when current skin is active
		//---> For example to disable widget 'About Me':
		//---> unset( $list['aboutme'] );
		return $list;
	}
}

// Scroll to top progress
if ( ! function_exists( 'literature_skin_trx_addons_scroll_progress_type' ) ) {
	add_filter( 'trx_addons_filter_scroll_progress_type', 'literature_skin_trx_addons_scroll_progress_type' );
	function literature_skin_trx_addons_scroll_progress_type( $type = '' ) {
		return '';	// round | box | vertical | horizontal
	}
}

// Disable a "Title, Description, Link" parameters in out shortcodes
if ( ! function_exists( 'literature_skin_trx_addons_add_title_param' ) ) {
	add_filter( 'trx_addons_filter_add_title_param', 'literature_skin_trx_addons_add_title_param', 10, 2 );
	function literature_skin_trx_addons_add_title_param( $allow, $sc = '' ) {
		return false;
	}
}

// Disable display "Title, Description, Link" in our shortcodes
if ( ! function_exists( 'literature_skin_trx_addons_sc_show_titles' ) ) {
	add_filter( 'trx_addons_filter_sc_show_titles', 'literature_skin_trx_addons_sc_show_titles', 10, 2 );
	function literature_skin_trx_addons_sc_show_titles( $allow, $sc = '' ) {
		return $sc === 'sc_title';
	}
}

// Add a prefix 'theme-color-' to all colors added to Gutenberg (theme.json)
if ( ! function_exists( 'literature_skin_theme_json_data_add_scheme_color_prefix' ) ) {
	add_filter( 'literature_filter_gutenberg_fse_theme_json_data_add_scheme_color_prefix', 'literature_skin_theme_json_data_add_scheme_color_prefix' );
	function literature_skin_theme_json_data_add_scheme_color_prefix( $allow ) {
		return true;
	}
}

// If a new styles for ThemeREX Addons shortcodes are supported with a current skin - return true to add a tab "STYLE"
// to some shortcodes for full customization
if ( ! function_exists( 'literature_skin_allow_sc_styles_in_elementor' ) ) {
	add_filter( 'trx_addons_filter_allow_sc_styles_in_elementor', 'literature_skin_allow_sc_styles_in_elementor', 10, 2 );
	function literature_skin_allow_sc_styles_in_elementor( $allow, $sc ) {
		return true;
	}
}



// WOOCOMMERCE SETUP
//--------------------------------------------------

// Allow extended layouts for WooCommerce
if ( ! function_exists( 'literature_skin_woocommerce_allow_extensions' ) ) {
	add_filter( 'literature_filter_load_woocommerce_extensions', 'literature_skin_woocommerce_allow_extensions' );
	function literature_skin_woocommerce_allow_extensions( $allow ) {
		return false;
	}
}



// SCRIPTS AND STYLES
//--------------------------------------------------

// Return a skin-specific media slug for each responsive css-file
if ( ! function_exists( 'literature_skin_media_for_load_css_responsive' ) ) {
	add_filter( 'literature_filter_media_for_load_css_responsive', 'literature_skin_media_for_load_css_responsive', 10, 2 );
	function literature_skin_media_for_load_css_responsive( $media, $slug ) {
		if ( in_array( $slug, array( 'main', 'single', 'gutenberg-general' ) ) ) {
			$media = 'xxl';
		} else if ( in_array( $slug, array( 'front-page', 'bbpress', 'tribe-events', 'trx-addons-layouts', 'woocommerce', 'blog-styles' ) ) ) {
			$media = 'xl';
		} else if ( in_array( $slug, array( 'edd', 'mptt', 'trx-addons', 'woocommerce-extensions', 'single-styles' ) ) ) {
			$media = 'lg';
		} else if ( in_array( $slug, array( 'vc', 'learnpress', 'theme-hovers' ) ) ) {
			$media = 'md';
		} else if ( in_array( $slug, array( 'elementor', 'booked', 'instagram-feed' ) ) ) {
			$media = 'sm';
		} else if ( in_array( $slug, array( 'gutenberg' ) ) ) {
			$media = 'xs';
		}
		return $media;
	}
}


// Enqueue skin-specific scripts and styles for the frontend
// Priority 1050 -  before main theme plugins-specific (1100)
if ( ! function_exists( 'literature_skin_frontend_scripts' ) ) {
	add_action( 'wp_enqueue_scripts', 'literature_skin_frontend_scripts', 1050 );
	function literature_skin_frontend_scripts() {
		$literature_url = literature_get_file_url( literature_skins_get_current_skin_dir() . 'css/style.css' );
		if ( '' != $literature_url ) {
			wp_enqueue_style( 'literature-skin-' . esc_attr( literature_skins_get_current_skin_name() ), $literature_url, array(), null );
		}
		$literature_url = literature_get_file_url( literature_skins_get_current_skin_dir() . 'skin.js' );
		if ( '' != $literature_url ) {
			wp_enqueue_script( 'literature-skin-' . esc_attr( literature_skins_get_current_skin_name() ), $literature_url, array( 'jquery' ), null, true );
		}
	}
}


// Add skin-specific variables to the scripts
if ( ! function_exists( 'literature_skin_localize_script' ) ) {
	add_filter( 'literature_filter_localize_script', 'literature_skin_localize_script');
	function literature_skin_localize_script( $arr ) {
		// ToDo: Add skin-specific vars to the $arr to use its in the 'skin.js'
		// ---> For example: $arr['myvar'] = 'Value';
		// ---> In js code you can use variable 'myvar' as LITERATURE_STORAGE['myvar']
		return $arr;
	}
}


// Enqueue skin-specific scripts and styles for the admin
if ( ! function_exists( 'literature_skin_admin_scripts' ) ) {
	// Uncomment the code below to enable skin-specific styles and scripts for the admin
	add_action( 'admin_enqueue_scripts', 'literature_skin_admin_scripts', 12 );
	add_action( 'enqueue_block_editor_assets', 'literature_skin_admin_scripts', 12 );
	function literature_skin_admin_scripts() {
		static $loaded = false;
		if ( $loaded ) {
			return;
		}
		$loaded = true;
		$literature_url = literature_get_file_url( literature_skins_get_current_skin_dir() . 'css/admin.css' );
		if ( '' != $literature_url ) {
			wp_enqueue_style( 'literature-admin-skin-' . esc_attr( literature_skins_get_current_skin_name() ), $literature_url, array(), null );
		}
		$literature_url = literature_get_file_url( literature_skins_get_current_skin_dir() . 'skin-admin.js' );
		if ( '' != $literature_url ) {
			wp_enqueue_script( 'literature-admin-skin-' . esc_attr( literature_skins_get_current_skin_name() ), $literature_url, array( 'jquery' ), null, true );
		}
	}
}


// Custom styles
$literature_style_path = literature_get_file_dir( literature_skins_get_current_skin_dir() . 'css/style.php' );
if ( ! empty( $literature_style_path ) ) {
	require_once $literature_style_path;
}



// Correct the theme engine's output
//--------------------------------------------------

// Allow columns wrap for a single column
if ( ! function_exists( 'literature_skin_allow_columns_wrap_for_single_column' ) ) {
	add_filter( 'literature_filter_columns_wrap_for_single_column', 'literature_skin_allow_columns_wrap_for_single_column' );
	function literature_skin_allow_columns_wrap_for_single_column( $allow ) {
		return true;
	}
}

// Allow an alpha channel in the color picker
if ( ! function_exists( 'literature_skin_colorpicker_allow_alpha' ) ) {
	add_filter( 'literature_filter_colorpicker_allow_alpha', 'literature_skin_colorpicker_allow_alpha', 10, 2 );
	function literature_skin_colorpicker_allow_alpha( $allow, $field = '' ) {
		// Prevent loading the script 'wp-color-picker-alpha' for the skin
		// return $field == 'wp-color-picker-alpha' ? false : true;
		return true;
	}
}

// Allow a scheme color picker (globals) in the color picker
if ( ! function_exists( 'literature_skin_colorpicker_allow_globals' ) ) {
	add_filter( 'literature_filter_colorpicker_allow_globals', 'literature_skin_colorpicker_allow_globals', 10, 2 );
	function literature_skin_colorpicker_allow_globals( $allow, $field = '' ) {
		return true;
	}
}

// Disable color schemes for the layout elements (sections, columns, etc.)
if ( ! function_exists( 'literature_skin_disable_schemes_in_elements' ) ) {
	add_filter( 'literature_filter_add_scheme_in_elements', 'literature_skin_disable_schemes_in_elements' );
	function literature_skin_disable_schemes_in_elements( $allow ) {
		return false;
	}
}

// Disable color style for the layout elements (buttons, headings, etc.)
if ( ! function_exists( 'literature_skin_disable_color_style_in_elements' ) ) {
	add_filter( 'literature_filter_add_color_style_in_elements', 'literature_skin_disable_color_style_in_elements' );
	function literature_skin_disable_color_style_in_elements( $allow ) {
		return false;
	}
}



// Add/remove/change Theme Options and Settings
//--------------------------------------------------

// Override internal settings of the theme.
if ( ! function_exists( 'literature_skin_override_theme_settings' ) ) {
	add_action( 'after_setup_theme', 'literature_skin_override_theme_settings', 1 );
	function literature_skin_override_theme_settings() {
		// Disable a front page builder
		literature_storage_set_array( 'settings', 'allow_front_page_builder', false );
	}
}

// Hide the option 'Show helpers' from the Theme Options
if ( ! function_exists( 'literature_skin_hide_scheme_helpers' ) ) {
	add_action( 'after_setup_theme', 'literature_skin_hide_scheme_helpers', 3 );
	function literature_skin_hide_scheme_helpers() {
		literature_storage_set_array2( 'options', 'color_scheme_helpers', 'type', 'hidden' );
	}
}

// Disable a scheme selector in the Theme Options (if the skin has a single color scheme)
if ( ! function_exists( 'literature_skin_disable_scheme_selector' ) ) {
	add_filter( 'literature_filter_scheme_editor_show_selector', 'literature_skin_disable_scheme_selector' );
	function literature_skin_disable_scheme_selector( $allow ) {
		return false;
	}
}

// Removing the color scheme class
if ( ! function_exists( 'literature_skin_filter_sidebar_scheme' ) ) {
	add_filter( 'literature_filter_sidebar_scheme', 'literature_skin_filter_sidebar_scheme' );
	function literature_skin_filter_sidebar_scheme() {
		return 'inherit';
	}
}

// Remove unused widget areas
if ( ! function_exists( 'literature_skin_remove_unused_sidebars' ) ) {
	add_filter( 'literature_filter_list_sidebars', 'literature_skin_remove_unused_sidebars' );
	function literature_skin_remove_unused_sidebars( $list ) {
		unset( $list['header_widgets'] );
		unset( $list['above_page_widgets'] );
		unset( $list['below_page_widgets'] );
		unset( $list['above_content_widgets'] );
		unset( $list['below_content_widgets'] );
		return $list;
	}
}

// Remove a header posiotion 'under' from the list
if ( ! function_exists( 'literature_skin_remove_header_positions' ) ) {
	add_filter( 'literature_filter_list_header_positions', 'literature_skin_remove_header_positions' );
	function literature_skin_remove_header_positions( $list ) {
		unset( $list['under'] );
		return $list;
	}
}

// Add & Remove image's hovers
if ( ! function_exists( 'literature_skin_filter_get_list_hovers' ) ) {
	add_filter(	'literature_filter_list_hovers', 'literature_skin_filter_get_list_hovers' );
	function literature_skin_filter_get_list_hovers( $list ) {
		unset($list['dots']);
		unset($list['icon']);
		unset($list['icons']);
		unset($list['zoom']);
		unset($list['fade']);
		unset($list['slide']);
		unset($list['pull']);
		unset($list['border']);
		unset($list['excerpt']);
		unset($list['info']);

		$list['default'] = esc_html__( 'Default', 'literature' );
		$list['dots'] = esc_html__( 'Dots', 'literature' );
		return $list;
	}
}

// Change "load more" button text 
if ( ! function_exists( 'literature_skin_load_more_text_new' ) ) {
    add_filter( 'literature_filter_load_more_text', 'literature_skin_load_more_text_new' );
    function literature_skin_load_more_text_new() {
		$text = esc_html__('Load More', 'literature');
        return $text;
    }
}

// Change "comment button" and "comment title" text 
if ( ! function_exists( 'literature_skin_filter_comment_form_args' ) ) {
    add_filter( 'literature_filter_comment_form_args', 'literature_skin_filter_comment_form_args' );
    function literature_skin_filter_comment_form_args( $arr ) {
		$arr['label_submit'] = esc_html__( 'Leave a Comment', 'literature' );
		$arr['title_reply'] = esc_html__( 'Leave a Comment', 'literature' );
		return $arr;
    }
}

// Remove navigation menu
if ( ! function_exists( 'literature_skin_filter_register_nav_menus' ) ) {
    add_filter( 'literature_filter_register_nav_menus', 'literature_skin_filter_register_nav_menus' );
    function literature_skin_filter_register_nav_menus( $list ) {
		unset($list['menu_footer']);
		return $list;
    }
}

// Remove 'Quick Setup' tab
if ( ! function_exists( 'literature_skin_filter_theme_panel_tabs' ) ) {
	add_filter( 'trx_addons_filter_theme_panel_tabs', 'literature_skin_filter_theme_panel_tabs', 13 );
	function literature_skin_filter_theme_panel_tabs( $tabs ) {
		if ( isset( $tabs[ 'qsetup' ] ) ) {
			unset( $tabs[ 'qsetup' ] );
		}
		return $tabs;
	}
}

// Disable - wrap select with .select_container
if ( ! function_exists( 'literature_skin_disable_select_container_wrap' ) ) {
	add_filter( 'literature_filter_localize_script', 'literature_skin_disable_select_container_wrap' );
	function literature_skin_disable_select_container_wrap( $vars ) {
		$vars['select_container_disabled'] = true;
		return $vars;
	}
}

// Activation methods
if ( ! function_exists( 'literature_skin_filter_activation_methods' ) ) {
	add_filter( 'trx_addons_filter_activation_methods', 'literature_skin_filter_activation_methods', 10, 1 );
	function literature_skin_filter_activation_methods( $args ) {
		$args['elements_key'] = false;
		return $args;
	}
}