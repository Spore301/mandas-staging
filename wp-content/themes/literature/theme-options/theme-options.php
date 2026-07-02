<?php
/**
 * Theme Options, Color Schemes and Fonts utilities
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

// -----------------------------------------------------------------
// -- Create and manage Theme Options
// -----------------------------------------------------------------

if ( ! function_exists( 'literature_options_theme_setup2' ) ) {
	add_action( 'after_setup_theme', 'literature_options_theme_setup2', 2 );
	/**
	 * Create the global array with a Theme Options
	 * 
	 * Theme init priorities:
	 * 2 - create Theme Options
	 * 
	 * @hooked 'after_setup_theme', 2
	 */
	function literature_options_theme_setup2() {
		literature_create_theme_options();
	}
}

if ( ! function_exists( 'literature_options_theme_setup5' ) ) {
	add_action( 'after_setup_theme', 'literature_options_theme_setup5', 5 );
	/**
	 * Step 1: Load default settings and previously saved mods
	 * 
	 * @hooked 'after_setup_theme', 5
	 */
	function literature_options_theme_setup5() {
		literature_storage_set( 'options_reloaded', false );
		literature_load_theme_options();
	}
}

if ( is_customize_preview() ) {
	if ( ! function_exists( 'literature_load_custom_options' ) ) {
		add_action( 'wp_loaded', 'literature_load_custom_options' );
		/**
		 * Step 2: Reload current theme customization mods on the 'wp_loaded' action if the customizer is active
		 * 
		 * @hooked 'wp_loaded'
		 */
		function literature_load_custom_options() {
			if ( ! literature_storage_get( 'options_reloaded' ) ) {
				literature_storage_set( 'options_reloaded', true );
				literature_load_theme_options();
			}
		}
	}
}

if ( ! function_exists( 'literature_load_theme_options' ) ) {
	/**
	 * Load current values for each customizable option.
	 * If the 'reset_options' theme mod is set to 1, reset all options to their default values.
	 * If current option have no default values, they will be set to 'std' values.
	 * If current option have a responsive mode, it will be loaded for each breakpoint. 
	 * 
	 * @trigger 'literature_action_load_options'
	 */
	function literature_load_theme_options() {
		global $LITERATURE_STORAGE;
		$reset = (int) get_theme_mod( 'reset_options', 0 );
		$reset_custom_logo = false;
		$breakpoints = literature_get_theme_breakpoints();
		foreach ( $LITERATURE_STORAGE['options'] as $k => $v ) {
			foreach ( ! empty( $v['responsive'] ) ? $breakpoints : array( 'desktop' => array() ) as $bp => $bpv ) {
				$suffix = $bp == 'desktop' ? '' : '_' . $bp;
				if ( isset( $v['std'] ) ) {
					$value = literature_get_theme_option_std( $k, isset( $v["std{$suffix}"] ) ? $v["std{$suffix}"] : ( isset( $v['std'] ) ? $v['std'] : '' ), $suffix );
					// Save std value to the options array as a factory value
					$LITERATURE_STORAGE['options'][ $k ]["factory{$suffix}"] = $value;
					if ( ! $reset ) {
						if ( isset( $_GET[ $k . $suffix ] ) ) {
							$value = wp_kses_data( wp_unslash( $_GET[ $k . $suffix ] ) );
						} else {
							$default_value = -987654321;
							$tmp           = get_theme_mod( $k . $suffix, $default_value );
							if ( $tmp != $default_value ) {
								$value = $tmp;
							}
						}
					}
					$LITERATURE_STORAGE['options'][ $k ]["val{$suffix}"] = $value;
					if ( $reset ) {
						remove_theme_mod( $k . $suffix );
						if ( empty( $suffix ) && 'custom_logo' == $k ) {
							$reset_custom_logo = true;
						}
					}
				}
			}
		}
		if ( $reset ) {
			// Fix to reset the option 'custom_logo' if WP version >= 4.5 and if current page is 'Customize'
			if ( ! $reset_custom_logo && function_exists( 'the_custom_logo' ) ) {
				remove_theme_mod( 'custom_logo' );
			}
			// Unset reset flag
			set_theme_mod( 'reset_options', 0 );
			// Regenerate CSS with default colors and fonts
			literature_customizer_save_css();
		} else {
			do_action( 'literature_action_load_options' );
		}
	}
}

if ( ! function_exists( 'literature_override_theme_options' ) ) {
	add_action( 'wp', 'literature_override_theme_options', 1 );
	/**
	 * Override theme options with stored meta for the current post/page.
	 * 
	 * @hooked 'wp', 1
	 * 
	 * @param array|null $query_vars  Query variables (not used)
	 * @param int        $page_id     ID of the page to override options for (default: 0 - current page ID is used)
	 * 
	 * @trigger 'literature_action_override_theme_options'
	 */
	function literature_override_theme_options( $query_vars = null, $page_id = 0 ) {
		if ( $page_id > 0 || is_page_template( 'blog.php' ) ) {
			literature_storage_set( 'blog_archive', true );
			literature_storage_set( 'blog_template', $page_id > 0 ? $page_id : get_the_ID() );
		}
		literature_storage_set( 'blog_mode', $page_id > 0 ? 'blog' : literature_detect_blog_mode() );
		if ( $page_id > 0 || literature_is_singular() ) {
			literature_storage_set( 'options_meta', get_post_meta( $page_id > 0 ? $page_id : get_the_ID(), 'literature_options', true ) );
		}
		do_action( 'literature_action_override_theme_options' );
	}
}

if ( ! function_exists( 'literature_blog_override_theme_options' ) ) {
	add_action( 'literature_action_override_theme_options', 'literature_blog_override_theme_options' );
	/**
	 * Override theme options with stored page meta on 'Blog posts' pages
	 * 
	 * @hooked 'literature_action_override_theme_options'
	 * 
	 * @global WP_Query $wp_query  The current query object
	 */
	function literature_blog_override_theme_options() {
		global $wp_query;
		if ( is_home() && ! is_front_page() && ! empty( $wp_query->is_posts_page ) ) {
			$id = get_option( 'page_for_posts' );
			if ( $id > 0 ) {
				literature_storage_set( 'options_meta', get_post_meta( $id, 'literature_options', true ) );
			}
		}
	}
}


if ( ! function_exists( 'literature_get_theme_option_std' ) ) {
	/**
	 * Return 'std' value of the option, processed by special function if a 'std' starts with '$literature_'
	 * 
	 * @param string $opt_name  Name of the option
	 * @param mixed  $opt_std   Default value of the option
	 * @param string $suffix    Suffix for the responsive mode (if any)
	 * 
	 * @return mixed  Processed default value of the option
	 */
	function literature_get_theme_option_std( $opt_name, $opt_std, $suffix = '' ) {
		if ( ! is_array( $opt_std ) && strpos( $opt_std, '$literature_' ) !== false ) {
			$func = substr( $opt_std, 1 );
			if ( function_exists( $func ) ) {
				$opt_std = $func( $opt_name, $suffix );
			}
		}
		return $opt_std;
	}
}


if ( ! function_exists( 'literature_get_theme_option' ) ) {
	/**
	 * Return a value of the specified option.
	 * If option is not set - return a default value. If a default value is not set and a strict_mode is true - display error message.
	 * If $post_id > 0 - return option value from the post meta.
	 * If name contains '#mode' - return option value for the specified responsive mode (laptop, tablet, mobile). For example, 'expand_content#tablet'
	 * 
	 * @param string $name         Name of the option
	 * @param mixed  $defa         Default value of the option (if not set)
	 * @param bool   $strict_mode  If true - display error message if option is not set
	 * @param int    $post_id      ID of the post to get option value from (default: 0 - a global option is returned)
	 * 
	 * @return mixed  Value of the option or default value if option is not set
	 */
	function literature_get_theme_option( $name, $defa = '', $strict_mode = false, $post_id = 0 ) {

		$rez            = $defa;
		$from_post_meta = false;
		$suffix		    = '';

		if ( strpos( $name, '#' ) !== false ) {
			$parts  = explode( '#', $name );
			$name   = $parts[0];
			$suffix = '_' . $parts[1];
		}

		if ( $post_id > 0 ) {
			if ( ! literature_storage_isset( 'post_options_meta', $post_id ) ) {
				literature_storage_set_array( 'post_options_meta', $post_id, get_post_meta( $post_id, 'literature_options', true ) );
			}
			if ( literature_storage_isset( 'post_options_meta', $post_id, $name . $suffix ) ) {
				$tmp = literature_storage_get_array( 'post_options_meta', $post_id, $name . $suffix );
				if ( ! literature_is_inherit( $tmp ) ) {
					$rez            = $tmp;
					$from_post_meta = true;
				}
			}
		}

		if ( ! $from_post_meta && literature_storage_isset( 'options' ) ) {

			$blog_mode   = literature_storage_get( 'blog_mode' );
			$mobile_mode = wp_is_mobile() ? 'mobile' : '';

			if ( ! literature_storage_isset( 'options', $name )
				&& ( empty( $blog_mode ) || ! literature_storage_isset( 'options', $name . '_' . $blog_mode ) )
				&& ( ! literature_storage_isset( 'options_meta', $name ) || literature_is_inherit( literature_storage_get_array( 'options_meta', $name ) ) )
			) {

				$rez = '_not_exists_';
				$tmp = $rez;
				if ( function_exists( 'trx_addons_get_option' ) ) {
					$rez = trx_addons_get_option( $name, $tmp, false );
				}
				if ( $rez === $tmp ) {
					$rez = $defa;
					if ( $strict_mode
						&& func_num_args() == 1
						&& is_user_logged_in()
					) {
						$s = '';
						if ( function_exists( 'ddo' ) ) {
							$s = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
							array_shift($s);
							$s = ddo($s, 0, 3);
						}
						// Don't break execution, only display a message if a user is logged in
						// wp_die(
						dcl(
							// Translators: Add option's name to the message
							esc_html( sprintf( __( 'Undefined option "%s"', 'literature' ), $name ) )
							. ( ! empty( $s )
									? ' ' . esc_html( __( 'called from:', 'literature' ) ) . "<pre>" . wp_kses_data( $s ) . '</pre>'
									: ''
									)
						);
					}
				}

			} else {

				// Single meta name: 'expand_content' -> 'expand_content_single'
				$single_meta_name = $name . ( literature_is_single() && substr( $name, -7 ) != '_single' ? '_single' : '' );

				// Single option name: 'expand_content' -> 'expand_content_single'
				// If 'override_option_single' == 'post' - override option allowed only for post type 'post', otherwise - for all CPT
				$single_name = $name . ( ( literature_get_theme_setting( 'override_option_single', 'post' ) == 'post'
											? literature_is_singular( 'post' )
											: literature_is_single()
											)
										&& substr( $name, -7 ) != '_single'
											? '_single'
											: ''
										);

				// Parent mode: 'team_single' -> 'team', 
				//              'post', 'home', 'category', 'tag', 'archive', 'author', 'search' -> 'blog'
				$blog_mode_parent = apply_filters( 
										'literature_filter_blog_mode_parent',
										in_array( $blog_mode, array( 'post', 'home', 'category', 'tag', 'archive', 'author', 'search' ) )
											? 'blog'
											: str_replace( '_single', '', $blog_mode )
									);

				// Parent option name for posts: 'expand_content_single' -> 'expand_content_blog'
				$blog_name = 'post' == $blog_mode && substr( $name, -7 ) == '_single'
								? str_replace( '_single', '_blog', $name )
								: ( 'home' == $blog_mode && substr( $name, -5 ) != '_blog'
									? $name . '_blog'
									: ''
									);

				// Parent option name for CPT: 'expand_content_single_team' -> 'expand_content_team'
				$parent_name = strpos( $name, '_single') !== false ? str_replace( '_single', '', $name ) : '';

				// Get 'xxx_single' instead 'xxx_post'
				if ( 'post' == $blog_mode ) {
					$blog_mode = 'single';
				}

				// Override option from GET or POST for current blog mode
				// example: request 'expand_content_single_team'
				if ( ! empty( $blog_mode ) && isset( $_REQUEST[ $name . '_' . $blog_mode . $suffix ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_REQUEST[ $name . '_' . $blog_mode . $suffix ] ) );

					// Override option from GET or POST
					// example: request 'expand_content_single'
				} elseif ( isset( $_REQUEST[ $name ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_REQUEST[ $name ] ) );

					// Override option from COOKIE for current blog mode
					// example: request 'expand_content_single_team'
				} else if ( ! empty( $blog_mode ) && isset( $_COOKIE[ $name . '_' . $blog_mode . $suffix ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_COOKIE[ $name . '_' . $blog_mode . $suffix ] ) );

					// Override option from COOKIE
					// example: request 'expand_content_single'
				} elseif ( isset( $_COOKIE[ $name . $suffix ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_COOKIE[ $name . $suffix ] ) );

					// Override option from current page settings (if exists) with mobile mode
					// example: meta 'expand_content_single_mobile'
				} elseif ( ! empty( $mobile_mode ) && literature_storage_isset( 'options_meta', $name . '_' . $mobile_mode . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options_meta', $name . '_' . $mobile_mode . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options_meta', $name . '_' . $mobile_mode . $suffix );

					// Override single option with mobile mode
					// example: option 'expand_content_single_mobile'
				} elseif ( ! empty( $mobile_mode ) && $single_name != $name && literature_storage_isset( 'options', $single_name . '_' . $mobile_mode, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $single_name . '_' . $mobile_mode, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $single_name . '_' . $mobile_mode, 'val' . $suffix );

					// Override option with mobile mode
					// example: option 'expand_content_mobile'
				} elseif ( ! empty( $mobile_mode ) && literature_storage_isset( 'options', $name . '_' . $mobile_mode, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $name . '_' . $mobile_mode, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $name . '_' . $mobile_mode, 'val' . $suffix );

					// Override option from current page settings (if exists)
					// example: meta 'expand_content_single'
				} elseif ( $single_meta_name != $name && literature_storage_isset( 'options_meta', $single_meta_name . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options_meta', $single_meta_name . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options_meta', $single_meta_name . $suffix );

					// Override option from current page settings (if exists)
					// example: meta 'expand_content'
				} elseif ( literature_storage_isset( 'options_meta', $name . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options_meta', $name . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options_meta', $name . $suffix );

					// Override option from current blog mode settings: 'front', 'search', 'page', 'post', 'blog', etc. (if exists)
					// if 'override_option_single' == 'all' - override allowed for any CPT
					// example: option 'expand_content_single_team'
				} elseif ( ! empty( $blog_mode ) && $single_name != $name && literature_storage_isset( 'options', $single_name . '_' . $blog_mode, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $single_name . '_' . $blog_mode, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $single_name . '_' . $blog_mode, 'val' . $suffix );

					// Override option from current blog mode settings: 'front', 'search', 'page', 'post', 'blog', etc. (if exists)
					// 'override_option_single' == 'post' - override allowed only for 'post', check 'xxx_single_CPT' manually
					// example: option 'expand_content_single_team'
				} elseif ( ! empty( $blog_mode ) && literature_is_single() && $single_name == $name && ! in_array( $blog_mode, array( 'front', 'search', 'page', 'post', 'blog' ) ) && literature_storage_isset( 'options', $name . '_single_' . $blog_mode, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $name . '_single_' . $blog_mode, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $name . '_single_' . $blog_mode, 'val' . $suffix );

					// Override option from current blog mode settings: 'front', 'search', 'page', 'post', 'blog', etc. (if exists)
					// example: option 'expand_content_team'
				} elseif ( ! empty( $blog_mode ) && literature_storage_isset( 'options', $name . '_' . $blog_mode, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $name . '_' . $blog_mode, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $name . '_' . $blog_mode, 'val' . $suffix );

					// Override option from parent blog mode
					// example: option 'expand_content_team'
				} elseif ( ! empty( $blog_mode ) && ! empty( $parent_name ) && $parent_name != $name && literature_storage_isset( 'options', $parent_name . '_' . $blog_mode, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $parent_name . '_' . $blog_mode, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $parent_name . '_' . $blog_mode, 'val' . $suffix );

					// Override option for 'post' from 'blog' settings (if exists)
					// Also used for override 'xxx_single' on the 'xxx'
					// (instead 'sidebar_courses_single' return option for 'sidebar_courses')
					// example: option 'expand_content_single_team'
				} elseif ( ! empty( $blog_mode_parent ) && $blog_mode != $blog_mode_parent && $single_name != $name && literature_storage_isset( 'options', $single_name . '_' . $blog_mode_parent, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $single_name . '_' . $blog_mode_parent, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $single_name . '_' . $blog_mode_parent, 'val' . $suffix );

				} elseif ( ! empty( $blog_mode_parent ) && $blog_mode != $blog_mode_parent && literature_storage_isset( 'options', $name . '_' . $blog_mode_parent, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $name . '_' . $blog_mode_parent, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $name . '_' . $blog_mode_parent, 'val' . $suffix );

				} elseif ( ! empty( $blog_mode_parent ) && $blog_mode != $blog_mode_parent && $parent_name != $name && literature_storage_isset( 'options', $parent_name . '_' . $blog_mode_parent, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $parent_name . '_' . $blog_mode_parent, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $parent_name . '_' . $blog_mode_parent, 'val' . $suffix );

					// Get saved option value for single post
					// example: option 'expand_content_single'
				} elseif ( literature_storage_isset( 'options', $single_name, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $single_name, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $single_name, 'val' . $suffix );

					// Get saved option value
					// example: option 'expand_content'
				} elseif ( literature_storage_isset( 'options', $name, 'val' . $suffix ) && $single_name != $name && ! literature_is_inherit( literature_storage_get_array( 'options', $name, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $name, 'val' . $suffix );

					// Override option for '_single' from '_blog' settings (if exists)
					// example: option 'expand_content_blog'
				} elseif ( ! empty( $blog_name ) && literature_storage_isset( 'options', $blog_name, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $blog_name, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $blog_name, 'val' . $suffix );

					// Override option for '_single' from parent settings (if exists)
					// example: option 'expand_content'
				} elseif ( ! empty( $parent_name ) && $parent_name != $name && literature_storage_isset( 'options', $parent_name, 'val' . $suffix ) && ! literature_is_inherit( literature_storage_get_array( 'options', $parent_name, 'val' . $suffix ) ) ) {
					$rez = literature_storage_get_array( 'options', $parent_name, 'val' . $suffix );

					// Get saved option value if nobody override it
					// example: option 'expand_content'
				} elseif ( literature_storage_isset( 'options', $name, 'val' . $suffix ) ) {
					$rez = literature_storage_get_array( 'options', $name, 'val' . $suffix );

					// Get ThemeREX Addons option value
				} elseif ( function_exists( 'trx_addons_get_option' ) ) {
					$rez = trx_addons_get_option( $name, $defa, false );

				}
			}
		}

		return $rez;
	}
}


if ( ! function_exists( 'literature_check_theme_option' ) ) {
	/**
	 * Check if an option with a specified name is exists
	 * 
	 * @param string $name  Name of the option to check
	 * 
	 * @return bool  True if option exists, false otherwise
	 */
	function literature_check_theme_option( $name ) {
		return literature_storage_isset( 'options', $name );
	}
}


if ( ! function_exists( 'literature_get_theme_option_from_meta' ) ) {
	/**
	 * Return an option value, stored in the posts meta of current page/post.
	 * 
	 * @param string $name  Name of the option to get
	 * @param mixed  $defa  Default value of the option (if not set)
	 *
	 * @return mixed  Value of the option or default value if option is not set
	 */
	function literature_get_theme_option_from_meta( $name, $defa = '' ) {
		$rez = $defa;
		if ( literature_storage_isset( 'options_meta' ) ) {
			if ( literature_storage_isset( 'options_meta', $name ) ) {
				$rez = literature_storage_get_array( 'options_meta', $name );
			} else {
				$rez = 'inherit';
			}
		}
		return $rez;
	}
}


if ( ! function_exists( 'literature_get_theme_dependencies' ) ) {
	/**
	 * Get the list with all dependencies from the Theme Options to use in JS
	 * 
	 * @trigger 'literature_filter_get_theme_dependencies'
	 * 
	 * @return array  Array with dependencies, where key is the option name and value is the dependency
	 */
	function literature_get_theme_dependencies() {
		$depends = array();
		global $LITERATURE_STORAGE;
		foreach ( $LITERATURE_STORAGE['options'] as $k => $v ) {
			if ( isset( $v['dependency'] ) ) {
				$depends[ $k ] = $v['dependency'];
			}
		}
		return apply_filters( 'literature_filter_get_theme_dependencies', $depends );
	}
}


if ( ! function_exists( 'literature_get_theme_breakpoints' ) ) {
	/**
	 * Get the breakpoints list for the Theme Options
	 * 
	 * @trigger 'literature_filter_get_theme_breakpoints'
	 * 
	 * @return array  Array with breakpoints, where key is the breakpoint name and value is an array with breakpoint properties
	 */
	function literature_get_theme_breakpoints() {
		$bp = array(
			'desktop' => array( 'max' => 100000, 'title' => esc_html__( 'Desktop', 'literature' ), 'icon' => 'icon-desktop' ),
			'laptop' => array( 'max' => 1679, 'title' => esc_html__( 'Laptop', 'literature' ), 'icon' => 'icon-laptop' ),
			'tablet'  => array( 'max' => 1279, 'title' => esc_html__( 'Tablet', 'literature' ), 'icon' => 'icon-tablet-1' ),
			'mobile'  => array( 'max' => 767, 'title' => esc_html__( 'Mobile', 'literature' ), 'icon' => 'icon-mobile' )
		);
		return apply_filters( 'literature_filter_get_theme_breakpoints', $bp );
	}
}



//------------------------------------------------
// Save options
//------------------------------------------------
if ( ! function_exists( 'literature_options_save' ) ) {
	add_action( 'after_setup_theme', 'literature_options_save', 4 );
	/**
	 * Save theme options from the request
	 * 
	 * @hooked 'after_setup_theme', 4
	 */
	function literature_options_save() {

		if ( ! isset( $_REQUEST['page'] ) || 'theme_options' != $_REQUEST['page'] || '' == literature_get_value_gp( 'literature_nonce' ) ) {
			return;
		}

		// verify nonce
		if ( ! wp_verify_nonce( literature_get_value_gp( 'literature_nonce' ), admin_url() ) ) {
			literature_add_admin_message( esc_html__( 'Bad security code! Options are not saved!', 'literature' ), 'error', true );
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			literature_add_admin_message( esc_html__( 'Manage options is denied for the current user! Options are not saved!', 'literature' ), 'error', true );
			return;
		}

		// Save options
		literature_options_update( null, 'literature_options_field_' );

		// Return result
		literature_add_admin_message( esc_html__( 'Options are saved', 'literature' ) );
		wp_redirect( get_admin_url( null, 'admin.php?page=theme_options' ) );
		exit();
	}
}


if ( ! function_exists( 'literature_options_update' ) ) {
	/**
	 * Update the theme options from the specified source (_POST or any other options storage)
	 *
	 * @trigger 'literature_filter_options_save' (to filter options before saving) 
	 * @trigger 'literature_action_just_save_options' (immediately after options update)
	 * @trigger 'literature_action_save_options' (after the page reload)
	 * 
	 * @param array|null $from          Source of the options to update from (default: null - use $_POST)
	 * @param string     $from_prefix   Prefix for the options names in the source (default: empty string - use options names as is)
	 */
	function literature_options_update( $from = null, $from_prefix = '' ) {
		$external_storages = array();
		$values            = null === $from ? get_theme_mods() : $from;
		$options           = literature_storage_get( 'options' );
		$breakpoints	   = literature_get_theme_breakpoints();
		foreach ( $options as $k => $v ) {
			// Skip non-data options - sections, info, etc.
			if ( ! isset( $v['std'] ) ) {
				continue;
			}
			foreach ( ! empty( $v['responsive'] ) ? $breakpoints : array( 'desktop' => array() ) as $bp => $bpv ) {
				$suffix = $bp == 'desktop' ? '' : '_' . $bp;
				// Get new value
				$value = null;
				if ( null === $from ) {
					$from_name = "{$from_prefix}{$k}{$suffix}";
					if ( isset( $_POST[ $from_name ] ) ) {
						$value = literature_get_value_gp( $from_name );
						if ( in_array( $v['type'], array( 'checkbox', 'switch' ) ) ) {
							$value = (int) $value;
						} else if ( $v['type'] == 'color' && ! empty( $v['globals'] ) ) {
							$from_globals = literature_get_value_gp( "{$from_name}_globals" );
							if ( ! empty( $from_globals ) ) {
								$value = $from_globals;
							}
						} else if ( is_numeric( $v['std'] ) ) {
							$value = strpos( strval( $value ), '.' ) === false ? (int) $value : (float) $value;
						} else if ( is_bool( $v['std'] ) ) {
							$value = (bool) $value;
						}
						// Individual options processing
						if ( 'custom_logo' == $k . $suffix ) {
							if ( ! empty( $value ) && 0 == (int) $value ) {
								$protocol = explode( '//', $value );
								$value = literature_clear_thumb_size( $value );
								if ( strpos( $value, ':' ) === false && ! empty( $protocol[0] ) && substr( $protocol[0], -1 ) == ':' ) {
									$value = $protocol[0] . $value;
								}
								$value = literature_attachment_url_to_postid( $value );
								if ( empty( $value ) ) {
									$value = null === $from ? get_theme_mod( $k ) : $values[ $k ];
								}
							}
						}
						// Save to the result array
						if ( ! empty( $v['type'] ) 
							&& ( 'hidden' != $v['type'] || 'reset_options' == $k . $suffix )
							&& empty( $v['hidden'] )
							&& ( ! empty( $v['options_storage'] ) || literature_get_theme_option_std( $k, isset( $v["std{$suffix}"] ) ? $v["std{$suffix}"] : ( isset( $v['std'] ) ? $v['std'] : '' ), $suffix ) !== $value )
						) {
							// If value is not hidden and not equal to 'std' - store it
							$values[ $k . $suffix ] = $value;
						} elseif ( isset( $values[ $k . $suffix ] ) ) {
							// Otherwise - remove this key from options
							unset( $values[ $k . $suffix ] );
							$value = null;
						}
					}
				} else {
					$value = isset( $values[ $k . $suffix ] )
									? $values[ $k . $suffix ]
									: literature_get_theme_option_std( $k, isset( $v["std{$suffix}"] ) ? $v["std{$suffix}"] : ( isset( $v['std'] ) ? $v['std'] : '' ), $suffix );
				}
				// External plugin's options
				if ( $value !== null && ! empty( $v['options_storage'] ) && empty( $suffix ) ) {
					if ( ! isset( $external_storages[ $v['options_storage'] ] ) ) {
						$external_storages[ $v['options_storage'] ] = array();
					}
					$external_storages[ $v['options_storage'] ][ $k ] = $value;
				}
			}
		}

		// Update options in the external storages
		foreach ( $external_storages as $storage_name => $storage_values ) {
			$storage = get_option( $storage_name, false );
			if ( is_array( $storage ) ) {
				foreach ( $storage_values as $k => $v ) {
					if ( ! empty( $options[$k]['type'] )
						&& 'hidden' != $options[$k]['type']
						&& ( empty( $options[$k]['hidden'] ) || ! $options[$k]['hidden'] )
						&& literature_get_theme_option_std( $k, $options[$k]['std'] ) != $v
					) {
						// If value is not hidden and not equal to 'std' - store it
						$storage[ $k ] = $v;
					} else {
						// Otherwise - remove this key from the external storage and from the theme options
						unset( $storage[ $k ] );
						unset( $values[ $k ] );
					}
				}
				update_option( $storage_name, apply_filters( 'literature_filter_options_save', $storage, $storage_name ) );
			}
		}

		//---------------------------- DEV RESET HELPER -------------------------------------
		// Set to true and save theme options
		// if you want to reset colors
		if ( false ) {
			unset( $values['scheme_storage'] );
		}
		// Set to true and save theme options
		// if you want to reset fonts
		if ( false ) {
			$fonts = literature_get_theme_fonts();
			foreach ( $fonts as $tag => $v ) {
				foreach ( $v as $css_prop => $css_value ) {
					if ( in_array( $css_prop, array( 'title', 'description' ) ) ) {
						continue;
					}
					// Skip responsive values
					if ( strpos( $css_prop, '_' ) !== false ) {
						continue;
					}
					foreach ( ! empty( $options["{$tag}_{$css_prop}"]['responsive'] ) ? $breakpoints : array( 'desktop' => array() ) as $bp => $bpv ) {
						$suffix = $bp == 'desktop' ? '' : '_' . $bp;
						if ( isset( $values[ "{$tag}_{$css_prop}{$suffix}" ] ) ) {
							unset( $values[ "{$tag}_{$css_prop}{$suffix}" ] );
						}
					}
				}
			}
		}
		//---------------------------- /DEV RESET HELPER -------------------------------------

		// Update Theme Mods (internal Theme Options)
		$stylesheet_slug = get_stylesheet();
		$values          = apply_filters( 'literature_filter_options_save', $values, 'theme_mods' );
		update_option( "theme_mods_{$stylesheet_slug}", $values );

		// Store new schemes colors
		if ( ! empty( $values['scheme_storage'] ) ) {
			$schemes = literature_unserialize( $values['scheme_storage'], true );
			if ( is_array( $schemes ) && count( $schemes ) > 0 ) {
				literature_storage_set( 'schemes', $schemes );
			}
		}

		// Store new fonts parameters
		$fonts = literature_get_theme_fonts();
		foreach ( $fonts as $tag => $v ) {
			foreach ( $v as $css_prop => $css_value ) {
				if ( in_array( $css_prop, array( 'title', 'description' ) ) ) {
					continue;
				}
				// Skip responsive values
				if ( strpos( $css_prop, '_' ) !== false ) {
					continue;
				}
				foreach ( ! empty( $options["{$tag}_{$css_prop}"]['responsive'] ) ? $breakpoints : array( 'desktop' => array() ) as $bp => $bpv ) {
					$suffix = $bp == 'desktop' ? '' : '_' . $bp;
					if ( isset( $values[ "{$tag}_{$css_prop}{$suffix}" ] ) ) {
						$fonts[ $tag ][ $css_prop . $suffix ] = $values[ "{$tag}_{$css_prop}{$suffix}" ];
					}
				}
			}
		}
		literature_storage_set( 'theme_fonts', $fonts );

		do_action( 'literature_action_just_save_options', $values );

		// Update ThemeOptions save timestamp
		$stylesheet_time = time();
		update_option( "literature_options_timestamp_{$stylesheet_slug}", $stylesheet_time );

		// Synchronize theme options between child and parent themes
		if ( literature_get_theme_setting( 'duplicate_options' ) == 'both' ) {
			$theme_slug = get_template();
			if ( $theme_slug != $stylesheet_slug ) {
				literature_customizer_duplicate_theme_options( $stylesheet_slug, $theme_slug, $stylesheet_time );
			}
		}

		// Apply action - moved to the delayed state (see below) to load all enabled modules and apply changes after
		// Attention! Don't remove comment the line below!
		// Not need here: do_action('literature_action_save_options');
		update_option( 'literature_action', 'literature_action_save_options' );
	}
}

if ( ! function_exists( 'literature_do_delayed_action' ) ) {
	add_action( 'after_setup_theme', 'literature_do_delayed_action' );
	/**
	 * Call the delayed action from previous session (after save options) to save new CSS, etc.
	 * 
	 * @hooked 'after_setup_theme'
	 */
	function literature_do_delayed_action() {
		$action = get_option( 'literature_action' );
		if ( '' != $action ) {
			do_action( $action );
			update_option( 'literature_action', '' );
		}
	}
}



// -----------------------------------------------------------------
// -- Theme Settings utilities
// -----------------------------------------------------------------

if ( ! function_exists( 'literature_get_theme_setting' ) ) {
	/**
	 * Return the internal setting value
	 * 
	 * @param string $name     Name of the setting to get
	 * @param mixed  $default  Default value to return if the setting is not defined (default: -999999)
	 * 
	 * @return mixed  Value of the setting or default value if setting is not defined
	 */
	function literature_get_theme_setting( $name, $default = -999999 ) {
		if ( ! literature_storage_isset( 'settings', $name ) ) {
			if ( $default != -999999 )
				return $default;
			else if ( defined( 'WP_CLI' ) )
				return false;
			else {
				$s = '';
				if ( function_exists( 'ddo' ) ) {
					$s = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
					array_shift($s);
					$s = ddo($s, 0, 3);
				}
				wp_die(
					// Translators: Add option's name to the message
					esc_html( sprintf( __( 'Undefined setting "%s"', 'literature' ), $name ) )
					. ( ! empty( $s )
							? ' ' . esc_html( __( 'called from:', 'literature' ) ) . "<pre>" . wp_kses_data( $s ) . '</pre>'
							: ''
							)
				);
			}
		} else {
			return literature_storage_get_array( 'settings', $name );
		}
	}
}

if ( ! function_exists( 'literature_set_theme_setting' ) ) {
	/**
	 * Set (override) a new value for the theme setting
	 * 
	 * @param string $option_name  Name of the setting to set
	 * @param mixed  $value        New value for the setting
	 */
	function literature_set_theme_setting( $option_name, $value ) {
		if ( literature_storage_isset( 'settings', $option_name ) ) {
			literature_storage_set_array( 'settings', $option_name, $value );
		}
	}
}



// -----------------------------------------------------------------
// -- Color Schemes utilities
// -----------------------------------------------------------------

if ( ! function_exists( 'literature_load_schemes' ) ) {
	add_action( 'literature_action_load_options', 'literature_load_schemes' );
	/**
	 * Load saved values to all color schemes
	 * 
	 * @hooked 'literature_action_load_options'
	 */
	function literature_load_schemes() {
		$schemes = literature_storage_get( 'schemes' );
		$storage = literature_unserialize( literature_get_theme_option( 'scheme_storage' ), true );
		if ( is_array( $storage ) && count( $storage ) > 0 ) {
			// Add a factory colors to the storage
			foreach ( $schemes as $k => $v ) {
				$storage[ $k ]['colors_factory'] = $v['colors'];
			}
			// Update schemes with saved colors
			literature_storage_set( 'schemes', literature_check_scheme_colors( $storage, $schemes ) );
		}
	}
}

if ( ! function_exists( 'literature_check_scheme_colors' ) ) {
	/**
	 * Compare schemes (from the skin-options.php and saved version) and return correct colors set (if some colors are removed or added)
	 * 
	 * @param array $storage  Array with schemes colors from the saved options
	 * @param array $schemes  Array with schemes colors from the skin-options.php
	 * 
	 * @return array  Corrected array with schemes colors
	 */
	function literature_check_scheme_colors( $storage, $schemes ) {
		// Remove old colors
		foreach ( $storage as $k => $v ) {
			if ( isset( $schemes[ $k ] ) && ! empty( $v['colors'] ) && is_array( $v['colors'] ) ) {
				foreach ( $v['colors'] as $k1 => $v1 ) {
					if ( ! isset( $schemes[ $k ]['colors'][ $k1 ] ) ) {
						unset( $storage[ $k ]['colors'][ $k1 ] );
					}
				}
			}
		}
		// Add new colors
		foreach ( $schemes as $k => $v ) {
			if ( is_array( $v['colors'] ) ) {
				if ( ! isset( $storage[ $k ] ) ) {
					$storage[ $k ] = $v;
				}
				foreach ( $v['colors'] as $k1 => $v1 ) {
					if ( ! isset( $storage[ $k ]['colors'][ $k1 ] ) ) {
						$storage[ $k ]['colors'][ $k1 ] = $v1;
					}
				}
			}
		}
		return $storage;
	}
}

if ( ! function_exists( 'literature_get_scheme_color_name' ) ) {
	/**
	 * Return a filtered color name from the color scheme to allow change it in the child themes or skins.
	 * For example, if in the theme core the color name is 'text_link' and it is changed in the child theme to 'link'.
	 * 
	 * @param string $color_name  Name of the color to get
	 * 
	 * @return string  Filtered color name
	 */
	function literature_get_scheme_color_name( $color_name ) {
		$substitutes = literature_storage_get( 'scheme_color_substitutes' );
		return apply_filters( 'literature_filter_get_scheme_color_name', ! empty( $substitutes[ $color_name ] ) ? $substitutes[ $color_name ] : $color_name );
	}
}

if ( ! function_exists( 'literature_get_scheme_color' ) ) {
	/**
	 * Return the specified color value from current (or specified) color scheme
	 * 
	 * @param string $color_name  Name of the color to get
	 * @param string $scheme      Name of the color scheme to get the color from (default: empty - use current color scheme)
	 * 
	 * @return string  Value of the color or empty string if color is not defined in the scheme
	 */
	function literature_get_scheme_color( $color_name, $scheme = '' ) {
		if ( empty( $scheme ) ) {
			$scheme = literature_get_theme_option( 'color_scheme', 'default' );
		}
		if ( empty( $scheme ) || literature_storage_empty( 'schemes', $scheme ) ) {
			$scheme = 'default';
		}
		$colors = literature_storage_get_array( 'schemes', $scheme, 'colors' );
		$color_name = literature_get_scheme_color_name( $color_name );
		return isset( $colors[ $color_name ] ) ? $colors[ $color_name ] : '';
	}
}

if ( ! function_exists( 'literature_get_scheme_colors' ) ) {
	/**
	 * Return an array with all colors from current color scheme
	 * 
	 * @param string $scheme  Name of the color scheme to get the colors from (default: empty - use current color scheme)
	 * 
	 * @return array  Array with colors from the specified color scheme
	 */
	function literature_get_scheme_colors( $scheme = '' ) {
		if ( empty( $scheme ) ) {
			$scheme = literature_get_theme_option( 'color_scheme', 'default' );
		}
		if ( empty( $scheme ) || literature_storage_empty( 'schemes', $scheme ) ) {
			$scheme = 'default';
		}
		return literature_storage_get_array( 'schemes', $scheme, 'colors' );
	}
}

if ( ! function_exists( 'literature_get_scheme_storage' ) ) {
	/**
	 * Return all color schemes
	 * 
	 * @param string $scheme  Name of the color scheme to get the storage for (default: empty - return all schemes). Not used in the current implementation.
	 * @param string $suffix  Suffix for the scheme storage (default: empty - use default storage). Not used in the current implementation.
	 * 
	 * @return string  Serialized string with all color schemes or empty string if no schemes are defined
	 */
	function literature_get_scheme_storage( $scheme = '', $suffix = '' ) {
		return serialize( literature_storage_get( 'schemes' ) );
	}
}

if ( ! function_exists( 'literature_get_scheme_color_option' ) ) {
	/**
	 * Return a scheme color by the option name
	 * 
	 * @param string $option_name  Name of the option to get the color for
	 * 
	 * @return string  Value of the color from the current color scheme or empty string if color is not defined
	 */
	function literature_get_scheme_color_option( $option_name ) {
		$parts = explode( '_', $option_name, 2 );
		return literature_get_scheme_color( $parts[1] );
	}
}

if ( ! function_exists( 'literature_get_list_schemes' ) ) {
	/**
	 * Return a list with all color schemes in the format 'slug' => 'title'
	 * 
	 * @param bool $prepend_inherit  If true, prepend the list with 'Inherit' option (default: false)
	 * 
	 * @return array  Array with color schemes, where key is the scheme slug and value is the scheme title
	 */
	function literature_get_list_schemes( $prepend_inherit = false ) {
		$list    = array();
		$schemes = literature_storage_get( 'schemes' );
		if ( is_array( $schemes ) && count( $schemes ) > 0 ) {
			foreach ( $schemes as $slug => $scheme ) {
				$list[ $slug ] = ! empty( $scheme['title'] ) ? $scheme['title'] : ucfirst( str_replace( '_', ' ', $slug ) );
			}
		}
		return $prepend_inherit ? literature_array_merge( array( 'inherit' => esc_html__( 'Inherit', 'literature' ) ), $list ) : $list;
	}
}

if ( ! function_exists( 'literature_get_sorted_schemes' ) ) {
	/**
	 * Return all schemes, sorted by usage in the parameters 'xxx_scheme' on the current page
	 * 
	 * @return array  Array with sorted schemes, where key is the scheme slug and value is the scheme parameters
	 */
	function literature_get_sorted_schemes() {
		$params  = literature_storage_get( 'schemes_sorted' );
		$schemes = literature_storage_get( 'schemes' );
		$rez     = array();
		if ( is_array( $schemes ) ) {
			foreach ( $params as $p ) {
				if ( ! literature_check_theme_option( $p ) ) {
					continue;
				}
				$s = literature_get_theme_option( $p );
				if ( ! empty( $s ) && ! literature_is_inherit( $s ) && isset( $schemes[ $s ] ) ) {
					$rez[ $s ] = $schemes[ $s ];
					unset( $schemes[ $s ] );
				}
			}
			if ( count( $schemes ) > 0 ) {
				$rez = array_merge( $rez, $schemes );
			}
		}
		return $rez;
	}
}

if ( ! function_exists( 'literature_get_color_presets' ) ) {
	/**
	 * Return the color presets data
	 * 
	 * @trigger 'literature_filter_color_presets' (to filter color presets before returning)
	 * 
	 * @return array  Array with color presets, where key is the preset slug and value is an array with preset properties
	 */
	function literature_get_color_presets() {
		return apply_filters( 'literature_filter_color_presets', literature_storage_get( 'color_presets', array() ) );
	}
}

if ( ! function_exists( 'literature_get_list_color_presets' ) ) {
	/**
	 * Return the color presets list in format 'slug' => array( 'title' => '...', 'icon' => '...' )
	 * 
	 * @param bool $prepend_inherit  If true, prepend the list with 'Inherit' option (default: false)
	 * 
	 * @return array  Array with color presets, where key is the preset slug and value is an array with preset properties (title and icon)
	 */
	function literature_get_list_color_presets( $prepend_inherit = false ) {
		$list    = array();
		$presets = literature_get_color_presets();
		if ( is_array( $presets ) && count( $presets ) > 0 ) {
			foreach ( $presets as $slug => $preset ) {
				$list[ $slug ] = array(
									'title' => $preset['title'],
									'icon'  => sprintf( 'images/theme-options/color-preset/%s.png', literature_esc( $slug ) ),
									);
			}
		}
		return $prepend_inherit
					? literature_array_merge(
							array( 
								'inherit' => array(
												'title' => esc_html__( 'Inherit', 'literature' ),
												'icon'  => 'images/theme-options/inherit.png',
												),
							),
							$list
						)
					: $list;
	}
}


// -----------------------------------------------------------------
// -- Theme Fonts utilities
// -----------------------------------------------------------------

if ( ! function_exists( 'literature_load_fonts' ) ) {
	add_action( 'literature_action_load_options', 'literature_load_fonts' );
	/**
	 * Load saved values into fonts list (the entry 'theme_fonts' in the global storage)
	 * 
	 * @hooked 'literature_action_load_options'
	 */
	function literature_load_fonts() {
		// Fonts to load when theme starts
		$load_fonts = array();
		for ( $i = 1; $i <= literature_get_theme_setting( 'max_load_fonts' ); $i++ ) {
			$name = literature_get_theme_option( "load_fonts-{$i}-name" );
			if ( '' != $name ) {
				$load_fonts[] = array(
					'name'   => $name,
					'family' => literature_get_theme_option( "load_fonts-{$i}-family" ),
					'styles' => literature_get_theme_option( "load_fonts-{$i}-styles" ),
					'link'   => literature_get_theme_option( "load_fonts-{$i}-link" ),
				);
			}
		}
		literature_storage_set( 'load_fonts', $load_fonts );
		literature_storage_set( 'load_fonts_subset', literature_get_theme_option( 'load_fonts_subset' ) );

		// Font parameters of the main theme's elements
		$options = literature_storage_get( 'options' );
		$breakpoints = literature_get_theme_breakpoints();
		$fonts = literature_get_theme_fonts();
		foreach ( $fonts as $tag => $v ) {
			foreach ( $v as $css_prop => $css_value ) {
				if ( in_array( $css_prop, array( 'title', 'description' ) ) ) {
					continue;
				}
				// Skip responsive values
				if ( strpos( $css_prop, '_' ) !== false ) {
					continue;
				}
				foreach ( ! empty( $options["{$tag}_{$css_prop}"]['responsive'] ) ? $breakpoints : array( 'desktop' => array() ) as $bp => $bpv ) {
					$suffix = $bp == 'desktop' ? '' : '_' . $bp;
					$fonts[ $tag ][ $css_prop . $suffix ] = isset( $options["{$tag}_{$css_prop}"]["val{$suffix}"] )
																? $options["{$tag}_{$css_prop}"]["val{$suffix}"]
																: '';
				}
			}
		}
		literature_storage_set( 'theme_fonts', $fonts );
	}
}

if ( ! function_exists( 'literature_get_load_fonts_slug' ) ) {
	/**
	 * Return slug of the loaded font by replacing spaces with dashes
	 * 
	 * @param string $name  Name of the font to get the slug for
	 * 
	 * @return string  Slug of the font, where spaces are replaced with dashes
	 */
	function literature_get_load_fonts_slug( $name ) {
		return str_replace( ' ', '-', $name );
	}
}

if ( ! function_exists( 'literature_get_load_fonts_family_string' ) ) {
	/**
	 * Return the 'font-family' string from the font parameters: enclose names with spaces in quotes
	 * 
	 * @param array $font  Array with font parameters, where 'name' is the font name and 'family' is the font family
	 * 
	 * @return string  String with font family, where each part is enclosed in quotes if it contains spaces
	 */
	function literature_get_load_fonts_family_string( $font ) {
		$parts = array( $font['name'] );
		if ( ! empty( $font['family'] ) ) {
			$parts = array_merge( $parts, explode( ',', $font['family'] ) );
		}
		foreach( $parts as $k => $v ) {
			$v = trim( $v );
			$parts[ $k ] = strpos( $v, '"' ) === false && strpos( $v, ' ' ) !== false
								? '"' . trim( $v ) . '"'
								: $v;
		}
		return join( ',', $parts );
	}
}

if ( ! function_exists( 'literature_get_load_fonts_option' ) ) {
	/**
	 * Return load fonts parameter's default value
	 * 
	 * @param string $option_name  Name of the option to get the value for
	 * @param string $suffix       Suffix for the option name (default: empty - use option name as is)
	 * 
	 * @return string  Value of the load fonts option or empty string if option is not defined
	 */
	function literature_get_load_fonts_option( $option_name, $suffix = '' ) {
		$rez        = '';
		$parts      = explode( '-', $option_name );
		$load_fonts = literature_storage_get( 'load_fonts' );
		if ( 'load_fonts' == $parts[0] && count( $load_fonts ) > $parts[1] - 1 && isset( $load_fonts[ $parts[1] - 1 ][ $parts[2] ] ) ) {
			$rez = $load_fonts[ $parts[1] - 1 ][ $parts[2] ];
		}
		return $rez;
	}
}

if ( ! function_exists( 'literature_get_load_fonts_subset' ) ) {
	/**
	 * Return load fonts subset's default value
	 * 
	 * @param string $option_name  Name of the option to get the value for
	 * @param string $suffix       Suffix for the option name (default: empty - use option name as is)
	 * 
	 * @return string  Value of the load fonts subset option or empty string if option is not defined
	 */
	function literature_get_load_fonts_subset( $option_name, $suffix = '' ) {
		return literature_storage_get( 'load_fonts_subset' );
	}
}

if ( ! function_exists( 'literature_get_list_load_fonts' ) ) {
	/**
	 * Return load fonts list in the format 'font-family' => 'font-name'
	 * 
	 * @param bool $prepend_inherit  If true, prepend the list with 'Inherit' option (default: false)
	 * 
	 * @return array  Array with load fonts, where key is the font family string and value is the font name
	 */
	function literature_get_list_load_fonts( $prepend_inherit = false ) {
		$list       = array();
		$load_fonts = literature_storage_get( 'load_fonts' );
		if ( is_array( $load_fonts ) && count( $load_fonts ) > 0 ) {
			foreach ( $load_fonts as $font ) {
				$list[ literature_get_load_fonts_family_string( $font ) ] = $font['name'];
			}
		}
		return $prepend_inherit ? literature_array_merge( array( 'inherit' => esc_html__( 'Inherit', 'literature' ) ), $list ) : $list;
	}
}

if ( ! function_exists( 'literature_get_theme_fonts' ) ) {
	/**
	 * Return the 'theme_fonts' setting from the global storage
	 * 
	 * @return array  Array with theme fonts, where key is the tag (p, h1..h6, etc.) and value is an array with font parameters
	 */
	function literature_get_theme_fonts() {
		return literature_storage_get( 'theme_fonts' );
	}
}

if ( ! function_exists( 'literature_get_theme_fonts_option' ) ) {
	/**
	 * Return theme fonts parameter's default value
	 * 
	 * @param string $option_name  Name of the option to get the value for
	 * @param string $suffix       Suffix for the option name (default: empty - use option name as is)
	 * 
	 * @return string  Value of the theme fonts option or empty string if option is not defined
	 */
	function literature_get_theme_fonts_option( $option_name, $suffix = '' ) {
		$rez         = '';
		$parts       = explode( '_', $option_name, 2 );
		$theme_fonts = literature_storage_get( 'theme_fonts' );
		if ( ! empty( $theme_fonts[ $parts[0] ][ $parts[1] . $suffix ] ) ) {
			$rez = $theme_fonts[ $parts[0] ][ $parts[1] . $suffix ];
		}
		return $rez;
	}
}

if ( ! function_exists( 'literature_update_list_load_fonts' ) ) {
	add_action( 'literature_action_load_options', 'literature_update_list_load_fonts', 11 );
	/**
	 * Update a loaded fonts list in the each tag's parameter (p, h1..h6,...) after the 'load_fonts' options are loaded
	 * 
	 * @hooked 'literature_action_load_options'
	 */
	function literature_update_list_load_fonts() {
		$theme_fonts = literature_get_theme_fonts();
		$load_fonts  = literature_get_list_load_fonts( true );
		foreach ( $theme_fonts as $tag => $v ) {
			literature_storage_set_array2( 'options', $tag . '_font-family', 'options', $load_fonts );
		}
	}
}

if ( ! function_exists( 'literature_get_font_presets' ) ) {
	/**
	 * Return font presets
	 * 
	 * @trigger 'literature_filter_font_presets' (to filter font presets before returning)
	 * 
	 * @return array  Array with font presets, where key is the preset slug and value is an array with preset properties
	 */
	function literature_get_font_presets() {
		return apply_filters( 'literature_filter_font_presets', literature_storage_get( 'font_presets', array() ) );
	}
}

if ( ! function_exists( 'literature_get_list_font_presets' ) ) {
	/**
	 * Return font presets list in the format 'slug' => array( 'title' => '...', 'icon' => '...' )
	 * 
	 * @param bool $prepend_inherit  If true, prepend the list with 'Inherit' option (default: false)
	 * 
	 * @return array  Array with font presets, where key is the preset slug and value is an array with preset properties (title and icon)
	 */
	function literature_get_list_font_presets( $prepend_inherit = false ) {
		$list    = array();
		$presets = literature_get_font_presets();
		if ( is_array( $presets ) && count( $presets ) > 0 ) {
			foreach ( $presets as $slug => $preset ) {
				$list[ $slug ] = array(
									'title' => $preset['title'],
									'icon'  => sprintf( 'images/theme-options/font-preset/%s.png', literature_esc( $slug ) ),
									);
			}
		}
		return $prepend_inherit
					? literature_array_merge(
							array( 
								'inherit' => array(
												'title' => esc_html__( 'Inherit', 'literature' ),
												'icon'  => 'images/theme-options/inherit.png',
												),
							),
							$list
						)
					: $list;
	}
}

if ( ! function_exists( 'literature_options_theme_setup3' ) ) {
	add_action( 'after_setup_theme', 'literature_options_theme_setup3', 3 );
	/**
	 * Make some options titles translatable
	 * 
	 * Theme init priorities:
	 * 3 - add/remove Theme Options elements
	 * 
	 * @hooked 'after_setup_theme', 3
	 */
	function literature_options_theme_setup3() {
		$fonts = literature_storage_get( 'theme_fonts' );
		$translates = array(
			'font-family'      => esc_html__( 'Font family', 'literature' ),
			'font-size'        => esc_html__( 'Font size', 'literature' ),
			'font-weight'      => esc_html__( 'Font weight', 'literature' ),
			'font-style'       => esc_html__( 'Font style', 'literature' ),
			'line-height'      => esc_html__( 'Line height', 'literature' ),
			'text-decoration'  => esc_html__( 'Text decoration', 'literature' ),
			'text-transform'   => esc_html__( 'Text transform', 'literature' ),
			'letter-spacing'   => esc_html__( 'Letter spacing', 'literature' ),
			'margin-top'       => esc_html__( 'Top margin', 'literature' ),
			'margin-bottom'    => esc_html__( 'Bottom margin', 'literature' ),
			'padding'          => esc_html__( 'Padding', 'literature' ),
			'border-width'     => esc_html__( 'Border width', 'literature' ),
			'border-style'     => esc_html__( 'Border style', 'literature' ),
			'border-color'     => esc_html__( 'Border color', 'literature' ),
			'border-radius'    => esc_html__( 'Border radius', 'literature' ),
			'background-color' => esc_html__( 'Background color', 'literature' ),
			'color'            => esc_html__( 'Color', 'literature' ),
		);
		$states = array(
			':hover' => esc_html__( 'Hover', 'literature' ),
			':focus' => esc_html__( 'Focus', 'literature' ),
			':active' => esc_html__( 'Active', 'literature' ),
			':placeholder' => esc_html__( 'Placeholder', 'literature' ),
		);
		global $LITERATURE_STORAGE;
		foreach ( $LITERATURE_STORAGE['options'] as $k => $v ) {
			$found = false;
			foreach ( $translates as $tk => $tv ) {
				foreach ( $fonts as $tag => $font ) {
					if ( strpos( $k, $tag . '_' . $tk ) === 0 ) {
						$LITERATURE_STORAGE['options'][ $k ]['title'] = $tv;
						$found = true;
					}
					if ( $found ) {
						foreach ( $states as $sk => $sv ) {
							if ( strpos( $k, $sk ) !== false ) {
								$LITERATURE_STORAGE['options'][ $k ]['title'] .= ' (' . $sv . ')';
								break;
							}
						}
						break;
					}
				}
				if ( $found ) {
					break;
				}
			}
		}
	}
}



// -----------------------------------------------------------------
// -- Other options utilities
// -----------------------------------------------------------------

if ( ! function_exists( 'literature_get_theme_vars' ) ) {
	/**
	 * Return all vars from Theme Options with the parameter 'customizer'
	 * 
	 * @return array  Array with theme variables, where key is the variable name and value is the variable value
	 */
	function literature_get_theme_vars() {
		$vars = literature_theme_defaults();
		global $LITERATURE_STORAGE;
		foreach ( $LITERATURE_STORAGE['options'] as $k => $v ) {
			if ( ! empty( $v['customizer'] ) ) {
				$vars[ $v['customizer'] ] = literature_theme_defaults( $v['customizer'], literature_get_theme_option( $k ) );
			}
		}
		return $vars;
	}
}

if ( ! function_exists( 'literature_get_border_radius' ) ) {
	/**
	 * Return current theme-specific border radius for form's fields and buttons
	 * 
	 * @return string  Border radius value in CSS format (px, em, etc.)
	 */
	function literature_get_border_radius() {
		$rad = str_replace( ' ', '', literature_get_theme_option( 'border_radius' ) );
		if ( empty( $rad ) ) {
			$rad = 0;
		}
		return literature_prepare_css_value( $rad );
	}
}




// -----------------------------------------------------------------
// -- Theme Options page
// -----------------------------------------------------------------

if ( ! function_exists( 'literature_options_init_page_builder' ) ) {
	add_action( 'after_setup_theme', 'literature_options_init_page_builder' );
	/**
	 * Initialize Theme Options page builder
	 */
	function literature_options_init_page_builder() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', 'literature_options_add_scripts' );
		}
	}
}

if ( ! function_exists( 'literature_options_add_scripts' ) ) {
	/**
	 * Load required styles and scripts for admin mode on the Theme Options page
	 * 
	 * @hooked 'admin_enqueue_scripts'
	 */
	function literature_options_add_scripts() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( ! empty( $screen->id ) && false !== strpos( $screen->id, '_page_theme_options' ) ) {
			$suffix = literature_is_off( literature_get_theme_option( 'debug_mode' ) ) ? '.min' : '';
			wp_enqueue_style( 'literature-fontello', literature_get_file_url( 'css/font-icons/css/fontello.css' ), array(), null );
			wp_enqueue_style( 'wp-color-picker', false, array(), null );
			wp_enqueue_script( 'wp-color-picker', false, array( 'jquery' ), null, true );
			if ( apply_filters( 'literature_filter_colorpicker_allow_alpha', false, 'wp-color-picker-alpha' ) ) {
				wp_enqueue_script( 'wp-color-picker-alpha', literature_get_file_url( 'js/colorpicker/wp-color-picker-alpha/wp-color-picker-alpha' . $suffix . '.js' ), array( 'jquery', 'wp-color-picker' ), null, true );
			}
			wp_enqueue_script( 'jquery-ui-tabs', false, array( 'jquery', 'jquery-ui-core' ), null, true );
			wp_enqueue_script( 'jquery-ui-accordion', false, array( 'jquery', 'jquery-ui-core' ), null, true );
			wp_enqueue_script( 'jquery-ui-sortable', false, array('jquery', 'jquery-ui-core'), null, true);
			wp_enqueue_script( 'literature-options', literature_get_file_url( 'theme-options/theme-options.js' ), array( 'jquery' ), null, true );
			wp_enqueue_style(  'spectrum', literature_get_file_url( 'js/colorpicker/spectrum/spectrum' . $suffix . '.css' ), array(), null );
			wp_enqueue_script( 'spectrum', literature_get_file_url( 'js/colorpicker/spectrum/spectrum' . $suffix . '.js' ), array( 'jquery' ), null, true );
			wp_localize_script( 'literature-options', 'literature_dependencies', literature_get_theme_dependencies() );
			wp_localize_script( 'literature-options', 'literature_color_schemes', literature_storage_get( 'schemes' ) );
			wp_localize_script( 'literature-options', 'literature_simple_schemes', literature_storage_get( 'schemes_simple' ) );
			wp_localize_script( 'literature-options', 'literature_sorted_schemes', literature_storage_get( 'schemes_sorted' ) );
			wp_localize_script( 'literature-options', 'literature_color_presets', literature_get_color_presets() );
			wp_localize_script( 'literature-options', 'literature_theme_fonts', literature_storage_get( 'theme_fonts' ) );
			wp_localize_script( 'literature-options', 'literature_font_presets', literature_get_font_presets() );
			wp_localize_script( 'literature-options', 'literature_theme_vars', literature_get_theme_vars() );
			wp_localize_script(
				'literature-options', 'literature_options_vars', apply_filters(
					'literature_filter_options_vars', array(
						'max_load_fonts'            => literature_get_theme_setting( 'max_load_fonts' ),
						'save_only_changed_options' => literature_get_theme_setting( 'save_only_changed_options' ),
					)
				)
			);
		}
	}
}

if ( ! function_exists( 'literature_options_add_theme_panel_page' ) ) {
	add_action( 'trx_addons_filter_add_theme_panel_pages', 'literature_options_add_theme_panel_page', 100 );
	/**
	 * Add "Theme Options" item to the admin menu "Theme Panel"
	 * 
	 * @hooked 'trx_addons_filter_add_theme_panel_pages', 100 - to place this menu item at the last position in the submenu
	 * 
	 * @param array $list  List of theme panel pages
	 * 
	 * @return array  Updated list of theme panel pages with the "Theme Options" item added
	 */
	function literature_options_add_theme_panel_page($list) {
		$list[] = array(
			esc_html__( 'Theme Options', 'literature' ),
			esc_html__( 'Theme Options', 'literature' ),
			'manage_options',
			'theme_options',
			'literature_options_page_builder',
			'dashicons-admin-generic'
		);
		return $list;
	}
}


if ( ! function_exists( 'literature_options_page_builder' ) ) {
	/**
	 * Build the Theme Options page
	 */
	function literature_options_page_builder() {
		?>
		<span class="wp-header-end" style="display:none"></span>
		<div class="literature_options" data-responsive="desktop">
			<div class="literature_options_header">
				<h2 class="literature_options_title"><?php esc_html_e( 'Theme Options', 'literature' ); ?></h2>
				<div class="literature_options_buttons">
					<a href="#" role="button" class="literature_options_button_submit literature_options_button literature_options_button_accent" tabindex="0"><?php esc_html_e( 'Save Options', 'literature' ); ?></a>
					<a href="#" role="button" class="literature_options_button_export literature_options_button" tabindex="0"><?php esc_html_e( 'Export Options', 'literature' ); ?></a>
					<a href="#" role="button" class="literature_options_button_import literature_options_button" tabindex="0"><?php esc_html_e( 'Import Options', 'literature' ); ?></a>
					<a href="#" role="button" class="literature_options_button_reset literature_options_button" tabindex="0"><?php esc_html_e( 'Reset Options', 'literature' ); ?></a>
				</div>
			</div>
			<?php literature_show_admin_messages(); ?>
			<form id="literature_options_form" action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="literature_nonce" value="<?php echo esc_attr( wp_create_nonce( admin_url() ) ); ?>" />
				<?php literature_options_show_fields(); ?>
			</form>
		</div>
		<?php
	}
}


if ( ! function_exists( 'literature_options_show_fields' ) ) {
	/**
	 * Display all option's fields in the Theme Options page
	 * 
	 * @param array $options  Array with options to display (default: false - use options from the global storage)
	 */
	function literature_options_show_fields( $options = false ) {
		$options_total = 1;
		if ( empty( $options ) ) {
			$options = literature_storage_get( 'options' );
		}
		$tabs_titles      = array();
		$tabs_content     = array();
		$last_panel_super = '';
		$last_panel       = '';
		$last_section     = '';
		$last_batch       = '';
		$allow_subtabs    = literature_get_theme_setting( 'options_tabs_position' ) == 'vertical' && literature_get_theme_setting( 'allow_subtabs' );
		foreach ( $options as $k => $v ) {
			if ( 'panel' == $v['type'] || ( 'section' == $v['type'] && ( empty( $last_panel ) || $allow_subtabs ) ) ) {
				// New tab
				if ( ! isset( $tabs_titles[ $k ] ) ) {
					$tabs_titles[ $k ]  = $v;
					$tabs_content[ $k ] = '';
				}
				if ( ! empty( $last_batch ) ) {
					$tabs_content[ $last_section ] .= '</div></div>';
					$last_batch                     = '';
				}
				if ( 'panel' == $v['type'] || $allow_subtabs ) {
					$last_panel = $k;
					if ( 'section' == $v['type'] && ! empty( $last_panel_super ) ) {
						$tabs_titles[ $last_panel_super ]['super'] = true;
						$tabs_titles[ $k ]['sub'] = true;
					}
				}
				if ( 'panel' == $v['type'] ) {
					$last_panel_super = $k;
				}
				if ( empty( $tabs_titles[ $k ]['sub'] ) && ! empty( $last_section ) && ! empty( $tabs_titles[ $last_section ]['sub'] ) ) {
					$tabs_titles[ $last_section ]['sub_last'] = true;
				}
				$last_section = $k;
			} elseif ( 'batch' == $v['type'] || ( 'section' == $v['type'] && ! empty( $last_panel ) ) ) {
				// New batch
				if ( empty( $last_batch ) ) {
					$tabs_content[ $last_section ] = ( ! isset( $tabs_content[ $last_section ] ) ? '' : $tabs_content[ $last_section ] )
													. '<div class="literature_accordion literature_options_batch">';
				} else {
					$tabs_content[ $last_section ] .= '</div>';
				}
				$tabs_content[ $last_section ] .= '<h4 class="literature_accordion_title literature_options_batch_title">' . wp_kses( $v['title'], 'literature_kses_content' ) . '</h4>'
												. '<div class="literature_accordion_content literature_options_batch_content">';
				$last_batch                     = $k;
			} elseif ( in_array( $v['type'], array( 'batch_end', 'section_end', 'panel_end' ) ) ) {
				// End panel, section or batch
				if ( ! empty( $last_batch ) && ( 'section_end' != $v['type'] || empty( $last_panel ) ) ) {
					$tabs_content[ $last_section ] .= '</div></div>';
					$last_batch                     = '';
				}
				if ( 'panel_end' == $v['type'] ) {
					$last_panel = '';
					$last_panel_super = '';
				}
			} else if ( 'group' == $v['type'] ) {
				// Fields set (group)
				if ( count( $v['fields'] ) > 0 ) {
					$tabs_content[ $last_section ] = ( ! isset( $tabs_content[ $last_section] ) ? '' : $tabs_content[ $last_section ] ) 
													. literature_options_show_group( $k, $v );
				}
			} else {
				// Field's layout
				$options_total++;
				$tabs_content[ $last_section ] = ( ! isset( $tabs_content[ $last_section ] ) ? '' : $tabs_content[ $last_section ] )
												. literature_options_show_field( $k, $v );
			}
		}
		if ( ! empty( $last_batch ) ) {
			$tabs_content[ $last_section ] .= '</div></div>';
		}

		if ( count( $tabs_content ) > 0 ) {
			// Remove empty sections
			$last_section = '';
			foreach ( $tabs_content as $k => $v ) {
				if ( empty( $v ) && empty( $tabs_titles[ $k ]['super'] ) ) {
					if ( ! empty( $tabs_titles[ $k ]['sub_last'] ) && ! empty( $last_section ) ) {
						$tabs_titles[ $last_section ]['sub_last'] = true;
					}
					unset( $tabs_titles[ $k ] );
					unset( $tabs_content[ $k ] );
				} else if ( ! empty( $tabs_titles[ $k ]['sub'] ) ) {
					$last_section = $k;
				} else {
					$last_section = '';
				}
			}
			// Display alert if options count greater then PHP setting 'max_input_vars'
			if ( ! literature_get_theme_setting( 'save_only_changed_options' ) ) {
				$options_max = function_exists( 'ini_get' ) ? ini_get( 'max_input_vars' ) : 0;
				if ( $options_max > 0 && $options_total > $options_max ) {
					?>
					<div class="literature_admin_messages">
						<div class="literature_admin_message_item error">
							<p><?php
								// Translators: Add total options and max input vars to the message
								echo wp_kses_data( sprintf( __( "<strong>Attention! The number of theme options ( %1\$d )</strong> on this page <strong>exceeds the maximum number of variables ( %2\$d )</strong> specified in your server's PHP configuration!", 'literature' ), $options_total, $options_max ) )
									. '<br>'
									. wp_kses_data( __( "When you save the options, you will lose some of the settings (they will take their default values).", 'literature' ) );
							?></p>
						</div>
					</div>
					<?php
				}
			}
			?>
			<div id="literature_options_tabs" class="literature_tabs literature_tabs_<?php echo esc_attr( literature_get_theme_setting( 'options_tabs_position' ) ); ?> <?php echo count( $tabs_titles ) > 1 ? 'with_tabs' : 'no_tabs'; ?>">
				<?php
				if ( count( $tabs_titles ) > 1 ) {
					?>
					<ul>
						<?php
						$cnt = 0;
						foreach ( $tabs_titles as $k => $v ) {
							$cnt++;
							echo '<li class="literature_tabs_title literature_tabs_title_' . esc_attr( $v['type'] )
									. ( ! empty( $v['super'] ) ? ' literature_tabs_title_super' : '' )
									. ( ! empty( $v['sub'] ) ? ' literature_tabs_title_sub' : '' )
									. ( ! empty( $v['sub_last'] ) ? ' literature_tabs_title_sub_last' : '' )
								. '"><a href="#literature_options_section_' . esc_attr( ! empty( $v['super'] ) ? $cnt + 1 : $cnt ) . '">'
										. ( !empty( $v['icon'] ) ? '<i class="' . esc_attr( $v['icon'] ) . '"></i>' : '' )
										. '<span class="literature_tabs_caption">' . esc_html( $v['title'] ) . '</span>'
									. '</a>'
								. '</li>';
						}
						?>
					</ul>
					<?php
				}
				$cnt = 0;
				foreach ( $tabs_content as $k => $v ) {
					$cnt++;
					if ( ! empty( $tabs_titles[ $k ]['super']) ) {
						continue;
					}
					?>
					<div id="literature_options_section_<?php echo esc_attr( $cnt ); ?>" class="literature_tabs_section literature_options_section">
						<?php literature_show_layout( $v ); ?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
	}
}


if ( ! function_exists( 'literature_options_show_group' ) ) {
	/**
	 * Display option's group (fields set) in the Theme Options page
	 * 
	 * @param string $k          Option's key
	 * @param array  $v          Option's value (array with fields)
	 * @param string $post_type  Post type for which the group is displayed (default: empty - not used)
	 * 
	 * @return string  HTML output of the group with fields
	 */
	function literature_options_show_group( $k, $v, $post_type = '' ) {
		$inherit_allow = ! empty( $post_type );
		$inherit_state = ! empty( $post_type ) && isset( $v['val'] ) && literature_is_inherit( $v['val'] );
		$output = '<div class="literature_options_group'
						. ( $inherit_allow ? ' literature_options_inherit_' . ( $inherit_state ? 'on' : 'off' ) : '' )
						. ( ! empty( $v['pro_only'] ) ? ' literature_options_pro_only' : '' )
						.'"'
						. ( isset( $v['dependency'] ) ? ' data-param="' . esc_attr( $k ) . '" data-type="group"' : '' )
					. '>'
						. '<h4 class="literature_options_group_title'
							. ( ! empty( $v['title_class'] ) ? ' ' . esc_attr( $v['title_class'] ) : '' )
						. '">'
							. wp_kses( $v['title'], 'literature_kses_content' )
							. literature_add_inherit_lock( $k, $v, $inherit_allow )
						. '</h4>'
						. ( ! empty( $v['override']['desc'] ) || ! empty( $v['desc'] )
							? ( '<div class="literature_options_group_description">'
								. ( ! empty( $v['override']['desc'] ) 	// param 'desc' already processed with wp_kses()!
									? $v['override']['desc']
									: ( ! empty( $v['desc'] ) ? $v['desc'] : '' )
									)
								. '</div>'
								)
							: ''
							)
						. '<div class="literature_options_group_fields">';
		if ( ! isset( $v['val'] ) || ! is_array( $v['val'] ) || count( $v['val'] ) == 0 ) {
			$v['val'] = isset( $v['std'] ) ? $v['std'] : array( array() );
		}
		foreach ( $v['val'] as $idx => $values ) {
			$output .= '<div class="literature_options_fields_set' 
							. ( ! empty( $v['clone'] ) ? ' literature_options_clone' : '' )
						. '">'
							. ( ! empty( $v['clone'] )
									? '<span class="literature_options_clone_control literature_options_clone_control_move" data-tooltip-text="' . esc_attr__('Drag to reorder', 'literature') . '">'
											. '<span class="icon-menu"></span>'
										. '</span>'
									: ''
								);
			foreach ( $v['fields'] as $k1 => $v1 ) {
				$v1['val'] = isset( $values[ $k1 ] ) ? $values[ $k1 ] : $v1['std'];
				$output   .= literature_options_show_field( $k1, $v1, '', "{$k}[{$idx}]" );
			}
			$output .= ! empty( $v['clone'] )
						? '<span class="literature_options_clone_control literature_options_clone_control_add" tabindex="0" data-tooltip-text="' . esc_attr__('Clone items', 'literature') . '">'
								. '<span class="icon-docs"></span>'
							. '</span>'
							. '<span class="literature_options_clone_control literature_options_clone_control_delete" tabindex="0" data-tooltip-text="' . esc_attr__('Delete items', 'literature') . '">'
								. '<span class="icon-clear-button"></span>'
							. '</span>'
						: '';
			$output .= '</div>';
		}
		if ( ! empty( $v['clone'] ) ) {
			$output .= '<div class="literature_options_clone_buttons">'
							. '<a class="literature_button literature_button_accent literature_options_clone_button_add" tabindex="0">'
								. esc_html__('+ Add New Item', 'literature')
							. '</a>'
						. '</div>';
		}
		$output .= literature_add_inherit_cover( $k, $v, $inherit_allow, $inherit_state )
					. '</div>'
				.'</div>';
		return $output;
	}
}


if ( ! function_exists( 'literature_options_show_field' ) ) {
	/**
	 * Display a single option's field in the Theme Options page
	 * 
	 * @param string $name        Field's name (key)
	 * @param array  $field       Field's properties (type, title, desc, val, std, responsive, etc.)
	 * @param string $post_type   Post type for which the field is displayed (default: empty - not used)
	 * @param string $group       Group name for the field (default: empty - not used)
	 * 
	 * @return string  HTML output of the field
	 */
	function literature_options_show_field( $name, $field, $post_type = '', $group = '' ) {

		$inherit_allow = ! empty( $post_type );
		$inherit_state = ! empty( $post_type ) && isset( $field['val'] ) && literature_is_inherit( $field['val'] );

		$field_data_present = 'info' != $field['type'] || ! empty( $field['override']['desc'] ) || ! empty( $field['desc'] );

		if ( ( 'hidden' == $field['type'] && $inherit_allow )         // Hidden field in the post meta (not in the root Theme Options)
			|| ( ! empty( $field['hidden'] ) && ! $inherit_allow )    // Field only for post meta in the root Theme Options
		) {
			return '';
		}

		// Prepare 'name' for the group fields
		if ( ! empty( $group ) ) {
			$name = "{$group}[{$name}]";
		}
		$id = str_replace( array( '[', ']' ), array('_', ''), $name );

		$output = '';
		$breakpoints = literature_get_theme_breakpoints();

		if ( 'hidden' == $field['type'] ) {
			if ( isset( $field['val'] ) ) {
				foreach ( ! empty( $field['responsive'] ) ? $breakpoints : array( 'desktop' => array() ) as $bp => $bpv ) {
					$suffix = $bp == 'desktop' ? '' : '_' . $bp;
					$output .= '<input type="hidden" name="literature_options_field_' . esc_attr( $name . $suffix ) . '"'
									. ' value="' . esc_attr( isset( $field["val{$suffix}"] ) ? $field["val{$suffix}"] : '' ) . '"'
									. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, isset( $field["std{$suffix}"] ) ? $field["std{$suffix}"] : ( isset( $field['std'] ) ? $field['std'] : '' ), $suffix ) ) . '"'
									. ( ! empty( $field['responsive'] ) ? ' data-responsive="' . esc_attr( $bp ) . '"' : '' )
								. ' />';
				}
			}

		} else {
			$output = ( ! empty( $field['class'] ) && strpos( $field['class'], 'literature_new_row' ) !== false
						? '<div class="literature_new_row_before"></div>'
						: '' )
						. '<div class="literature_options_item literature_options_item_' . esc_attr( $field['type'] )
									. ( $inherit_allow ? ' literature_options_inherit_' . ( $inherit_state ? 'on' : 'off' ) : '' )
									. ( ! empty( $field['responsive'] ) ? ' literature_options_item_responsive' : '' )
									. ( ! empty( $field['compact'] ) ? ' literature_options_item_compact' : '' )
									. ( ! empty( $field['pro_only'] ) ? ' literature_options_pro_only' : '' )
									. ( 'color' == $field['type'] && ! empty( $field['globals'] ) ? ' literature_options_item_with_globals' : '' )
									. ( ! empty( $field['class'] ) ? ' ' . esc_attr( $field['class'] ) : '' )
									. '">'
							. '<h4 class="literature_options_item_title'
								. ( ! empty( $field['override'] )
									? ' literature_options_item_title_override " title="' . esc_attr__('This option can be overridden in the following sections (Blog, Plugins settings, etc.) or in the settings of individual pages', 'literature') . '"'
									: '"'
									)
								. ( ! empty( $field['class'] ) && strpos( $field['class'], '_column-' ) !== false ? ' title="' . esc_attr( $field['title'] ) . '"' : '' )
							. '>'
								. '<span class="literature_options_item_title_text">' . wp_kses( $field['title'], 'literature_kses_content' ) . '</span>'
								. ( ! empty( $field['override'] )
									? ' <span class="literature_options_asterisk"></span>'
									: ''
									)
								. literature_add_inherit_lock( $id, $field, $inherit_allow )
								. literature_add_responsive_buttons( $id, $field, $breakpoints )
							. '</h4>'
							. ( $field_data_present
								? '<div class="literature_options_item_data">'
									. '<div class="literature_options_item_field"'
										. ' data-param="' . esc_attr( $name ). '"'
										. ' data-type="' . esc_attr( $field['type'] ). '"'
										. ( ! empty( $field['linked'] ) ? ' data-linked="' . esc_attr( $field['linked'] ) . '"' : '' )
									. '>'
								: '' );

			if ( 'checkbox' == $field['type'] ) {
				// Type 'checkbox'
				$output .= '<label class="literature_options_item_label">'
							// Hack to always send checkbox value even it not checked
							. '<input type="hidden" name="literature_options_field_' . esc_attr( $name ) . '"'
									. ' value="' . esc_attr( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
									. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
									. ' />'
							. '<input type="checkbox" name="literature_options_field_' . esc_attr( $name ) . '_chk" value="1"'
									. ( 1 == $field['val'] ? ' checked="checked"' : '' )
									. ' />'
							. '<span class="literature_options_item_caption">'
								. esc_html( $field['title'] )
							. '</span>'
						. '</label>';

			} else if ( 'switch' == $field['type'] ) {
				// Type 'switch'
				$output .= '<label class="literature_options_item_label">'
							// Hack to always send checkbox value even it not checked
							. '<input type="hidden" name="literature_options_field_' . esc_attr( $name ) . '"'
									. ' value="' . esc_attr( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
									. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
									. ' />'
							. '<input type="checkbox" name="literature_options_field_' . esc_attr( $name ) . '_chk" value="1"'
									. ( 1 == $field['val'] ? ' checked="checked"' : '' )
									. ' />'
							. '<span class="literature_options_item_holder" tabindex="0">'
								. '<span class="literature_options_item_holder_back"></span>'
								. '<span class="literature_options_item_holder_handle"></span>'
							. '</span>'
							. ( ! empty( $field['title'] )
								? '<span class="literature_options_item_caption">' . esc_html( $field['title'] ) . '</span>'
								: ''
								)
						. '</label>';

			} elseif ( in_array( $field['type'], array( 'radio' ) ) ) {
				// Type 'radio' (2+ choises)
				$field['options'] = apply_filters( 'literature_filter_options_get_list_choises', $field['options'], $name );
				$first            = true;
				foreach ( $field['options'] as $k => $v ) {
					$output .= '<label class="literature_options_item_label">'
								. '<input type="radio" name="literature_options_field_' . esc_attr( $name ) . '"'
										. ' value="' . esc_attr( $k ) . '"'
										. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
										. ( ( '#' . $field['val'] ) == ( '#' . $k ) || ( $first && ! isset( $field['options'][ $field['val'] ] ) ) ? ' checked="checked"' : '' )
										. ' />'
								. '<span class="literature_options_item_holder" tabindex="0"></span>'
								. '<span class="literature_options_item_caption">'
									. esc_html( $v )
								. '</span>'
							. '</label>';
					$first   = false;
				}

			} elseif ( in_array( $field['type'], array( 'text', 'time', 'date', 'number' ) ) ) {
				// Type 'text' or 'time' or 'date' or 'number'
				foreach ( ! empty( $field['responsive'] ) ? $breakpoints : array( 'desktop' => array() ) as $bp => $bpv ) {
					$suffix = $bp == 'desktop' ? '' : '_' . $bp;
					$output .= '<input type="' . ( $field['type'] == 'number' ? 'number' : 'text ')  . '" name="literature_options_field_' . esc_attr( $name . $suffix ) . '"'
									. ' value="' . esc_attr( isset( $field["val{$suffix}"] ) && ! literature_is_inherit( $field["val{$suffix}"] ) ? $field["val{$suffix}"] : '' ) . '"'
									. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, isset( $field["std{$suffix}"] ) ? $field["std{$suffix}"] : ( isset( $field['std'] ) ? $field['std'] : '' ), $suffix ) ) . '"'
									. ( $field['type'] == 'number' && ! empty( $field['min'] )  ? ' min="' . esc_attr( $field['min'] ) . '"' : '' )
									. ( $field['type'] == 'number' && ! empty( $field['max'] )  ? ' max="' . esc_attr( $field['max'] ) . '"' : '' )
									. ( $field['type'] == 'number' && ! empty( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : '' )
									. ( ! empty( $field['responsive'] ) ? ' data-responsive="' . esc_attr( $bp ) . '"' : '' )
								. ' />';
				}

			} elseif ( 'textarea' == $field['type'] ) {
				// Type 'textarea'
				$output .= '<textarea name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
							. '>'
								. esc_html( literature_is_inherit( $field['val'] ) ? '' : $field['val'] )
							. '</textarea>';

			} elseif ( 'text_editor' == $field['type'] ) {
				// Type 'text_editor'
				$output .= '<input type="hidden" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_textarea( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ' />'
							. literature_show_custom_field(
								'literature_options_field_' . esc_attr( $id ) . '_tinymce',
								$field,
								literature_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} elseif ( 'select' == $field['type'] ) {
				// Type 'select'
				$field['options'] = apply_filters( 'literature_filter_options_get_list_choises', $field['options'], $name );
				$output          .= '<select size="1" name="literature_options_field_' . esc_attr( $name ) . '"'
									. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
									. '>';
				foreach ( $field['options'] as $k => $v ) {
					$output .= '<option value="' . esc_attr( $k ) . '"' . ( ( '#' . $field['val'] ) == ( '#' . $k ) ? ' selected="selected"' : '' ) . '>' . esc_html( $v ) . '</option>';
				}
				$output .= '</select>';

			} elseif ( in_array( $field['type'], array( 'image', 'media', 'video', 'audio' ) ) ) {
				// Type 'image', 'media', 'video' or 'audio'
				if ( (int) $field['val'] > 0 ) {
					$image        = wp_get_attachment_image_src( $field['val'], 'full' );
					$field['val'] = empty( $image[0] ) ? '' : $image[0];
				}
				$output .= '<input type="hidden" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ' />'
						. literature_show_custom_field(
							'literature_options_field_' . esc_attr( $id ) . '_button',
							array(
								'type'            => 'mediamanager',
								'multiple'        => ! empty( $field['multiple'] ),
								'data_type'       => $field['type'],
								'linked_field_id' => 'literature_options_field_' . esc_attr( $id ),
							),
							literature_is_inherit( $field['val'] ) ? '' : $field['val']
						);

			} elseif ( 'color' == $field['type'] ) {
				// Type 'color'
				if ( empty( $field['colorpicker'] ) ) {
					$field['colorpicker'] = 'wp';
				}
				// Add a button with popup with a default color scheme color names and values
				$globals_value = '';
				if ( ! empty( $field['globals'] ) ) {
					$output .= '<span class="literature_color_selector_globals'
									. ( substr( $field['val'], 0, 4) == 'var(' ? ' literature_color_selector_globals_active' : '' )
								. '">'
									. '<input type="hidden" id="literature_options_field_' . esc_attr( $id ) . '_globals"'
									. ' name="literature_options_field_' . esc_attr( $name ) . '_globals"'
									. ' value="' . esc_attr( substr( $field['val'], 0, 4) == 'var(' ? $field['val'] : '' ) . '"'
									. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
									. ' />'
								. '<span class="literature_color_selector_globals_button">'
									. literature_get_svg_from_file( literature_get_file_dir( 'images/theme-options/icons/globe.svg' ) )
								. '</span>'
								. '<span class="literature_color_selector_globals_list">';
					$scheme = literature_get_scheme_colors();
					$groups = literature_storage_get( 'scheme_color_groups' );
					$names  = literature_storage_get( 'scheme_color_names' );
					foreach( $groups as $cg => $cgroup ) {
						foreach( $names as $cn => $cname ) {
							$c = 'main' == $cg ? ( 'text' == $cn ? 'text_color' : $cn ) : $cg . '_' . str_replace( 'text_', '', $cn );
							if ( isset( $scheme[ $c ] ) ) {
								if ( 'var(--theme-color-' . esc_attr( $c ) . ')' == $field['val'] ) {
									$globals_value = $scheme[ $c ];
								}
								$output .= '<span class="literature_color_selector_globals_list_item'
													. ( 'var(--theme-color-' . esc_attr( $c ) . ')' == $field['val'] ? ' literature_color_selector_globals_list_item_active' : '' )
												. '"'
												. ' data-color="' . esc_attr( $scheme[ $c ] ) . '"'
												. ' data-value="var(--theme-color-' . esc_attr( $c ) . ')"'
											. '>'
												. '<span class="literature_color_selector_globals_list_item_left">'
													. '<span class="literature_color_selector_globals_list_item_color" style="background-color:' . esc_attr( $scheme[ $c ] ) . '"></span>'
													. '<span class="literature_color_selector_globals_list_item_name">' . esc_html( ( 'main' == $cg ? '' : $cgroup['title'] . ' ' ) . $cname['title'] ) . '</span>'
												. '</span>'
												. '<span class="literature_color_selector_globals_list_item_value">' . esc_html( $scheme[ $c ] ) . '</span>'
											. '</span>';
							}
						}
						// Break cycle to add only one group of colors: if ( 'main' == $cg ) break;
					}
					$output .= '</span>'
								. '</span>';
				}
				$output .= '<input type="text" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' class="literature_color_selector ' . esc_attr( $field['colorpicker'] ) . 'ColorPicker"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( ! empty( $globals_value ) ? $globals_value : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ' data-alpha-enabled="' . ( ! empty( $field['alpha'] ) ? 'true' : 'false' ) . '"'
								. ' data-alpha-color-type="hex"'
								. ' />';

			} elseif ( 'icon' == $field['type'] ) {
				// Type 'icon'
				$output .= '<input type="hidden" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ' />'
							. literature_show_custom_field(
								'literature_options_field_' . esc_attr( $id ) . '_button',
								array(
									'type'   => 'icons',
									'style'  => ! empty( $field['style'] ) ? $field['style'] : 'icons',
									'button' => true,
									'icons'  => true,
								),
								literature_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} elseif ( 'choice' == $field['type'] ) {
				// Type 'choice'
				$field['options'] = apply_filters( 'literature_filter_options_get_list_choises', $field['options'], $name );
				$output .= '<input type="hidden" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ' />'
							. literature_show_custom_field(
								'literature_options_field_' . esc_attr( $id ) . '_list',
								array(
									'type'    => 'choice',
									'options' => $field['options']
								),
								$field['val']
							);

			} elseif ( 'checklist' == $field['type'] ) {
				// Type 'checklist'
				$field['options'] = apply_filters( 'literature_filter_options_get_list_choises', $field['options'], $name );
				$output .= '<input type="hidden" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ' />'
							. literature_show_custom_field(
								'literature_options_field_' . esc_attr( $id ) . '_list',
								$field,
								literature_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} elseif ( 'scheme_editor' == $field['type'] ) {
				// Type 'scheme_editor'
				$storage = literature_check_scheme_colors( literature_unserialize( $field['val'], true ), literature_storage_get( 'schemes' ) );
				$field['val'] = serialize( $storage );
				$output .= '<input type="hidden" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ' />'
							. literature_show_custom_field(
								'literature_options_field_' . esc_attr( $id ) . '_scheme',
								$field,
								$storage
							);

			} elseif ( 'presets' == $field['type'] ) {
				// Type 'presets'
				$presets_type = literature_get_edited_post_type();
				if ( empty( $preset_type ) ) {
					$preset_type = '#';
				}
				$presets = get_option( 'literature_options_presets' );
				if ( empty( $presets ) || ! is_array( $presets ) ) {
					$presets = array();
				}
				if ( empty( $presets[ $presets_type ] ) || ! is_array( $presets[ $presets_type ] ) ) {
					$presets[ $presets_type ] = array();
				}
				$output .= '<select class="literature_options_presets_list" size="1" name="literature_options_field_' . esc_attr( $name ) . '" data-type="' . esc_attr( $presets_type ) . '">';
				$output .= '<option value="">' . literature_get_not_selected_text( esc_html__( 'Select preset', 'literature' ) ) . '</option>';
				foreach ( $presets[ $presets_type ] as $k => $v ) {
					$output .= '<option value="' . esc_attr( $v ) . '">' . esc_html( $k ) . '</option>';
				}
				$output .= '</select>';
				$output .= '<a href="#" role="button"'
								. ' class="button literature_options_presets_apply icon-check-2"'
								. ' title="' .  esc_attr__( 'Apply the selected preset', 'literature' ) . '"'
							. '></a>';
				$output .= '<a href="#" role="button"'
								. ' class="button literature_options_presets_add icon-plus-2"'
								. ' title="' .  esc_attr__( 'Create a new preset', 'literature' ) . '"'
							. '></a>';
				$output .= '<a href="#" role="button"'
								. ' class="button literature_options_presets_delete icon-clear-button"'
								. ' title="' .  esc_attr__( 'Delete the selected preset', 'literature' ) . '"'
							. '></a>';

			} elseif ( in_array( $field['type'], array( 'slider', 'range' ) ) ) {
				// Type 'slider' || 'range'
				$field['show_value'] = ! isset( $field['show_value'] ) || $field['show_value'];
				$output             .= '<input type="' . ( ! $field['show_value'] ? 'hidden' : 'text' ) . '" id="literature_options_field_' . esc_attr( $id ) . '"'
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( literature_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ( $field['show_value'] ? ' class="literature_range_slider_value"' : '' )
								. ' data-type="' . esc_attr( $field['type'] ) . '"'
								. ' />'
							. ( $field['show_value'] && ! empty( $field['units'] ) ? '<span class="literature_range_slider_units">' . esc_html( $field['units'] ) . '</span>' : '' )
							. literature_show_custom_field(
								'literature_options_field_' . esc_attr( $id ) . '_slider',
								$field,
								literature_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} else if ( 'button' == $field['type'] ) {
				// Type 'button' - call specified js function
				$output .= '<input type="button"'
								. ( ! empty($field['class_field'] ) ? ' class="' . esc_attr( $field['class_field'] ) . '"' : '')
								. ' name="literature_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( ! empty( $field['caption'] ) ? $field['caption'] : $field['title']) . '"'
								. ' data-action="' . esc_attr( ! empty( $field['action'] ) ? $field['action'] : literature_get_theme_option_std( $name, $field['std'] ) ) . '"'
								. ( ! empty( $field['callback'] ) ? ' data-callback="'.esc_attr( $field['callback'] ) . '"' : '')
								. '>';

			} else {
				// Unknown type - apply filters
				$output .= apply_filters( 'literature_filter_get_custom_field', '', $name, $field, $inherit_allow, $inherit_state );

			}

			$output .= literature_add_inherit_cover( $name, $field, $inherit_allow, $inherit_state )
						. ( $field_data_present ? '</div>' : '' )
						. ( ! empty( $field['override']['desc'] ) || ! empty( $field['desc'] )
							? '<div class="literature_options_item_description">'
								. ( ! empty( $field['override']['desc'] )   // param 'desc' already processed with wp_kses()!
										? $field['override']['desc']
										: $field['desc'] )
								. '</div>'
							: '' )
					. ( $field_data_present ? '</div>' : '' )
				. '</div>';
		}
		return $output;
	}
}


if ( ! function_exists( 'literature_add_inherit_lock' ) ) {
	/**
	 * Add 'Inherit' lock to the field in the post meta section with Theme Options
	 * 
	 * @param string $id            Field ID
	 * @param array  $field         Field parameters
	 * @param bool   $inherit_allow Whether the field can be inherited
	 * 
	 * @return string HTML code for the inherit lock
	 */
	function literature_add_inherit_lock( $id, $field, $inherit_allow ) {
		return $inherit_allow
					? '<span class="literature_options_inherit_lock' . ( ! empty( $field['pro_only'] ) ? ' literature_options_pro_only_lock' : '' ) . '"'
							. ' id="literature_options_inherit_' . esc_attr( $id ) . '"'
							. ( empty( $field['pro_only'] ) ? ' tabindex="0"' : '' )
						. '>'
						. '</span>'
					: '';
	}
}


if ( ! function_exists( 'literature_add_inherit_cover' ) ) {
	/**
	 * Add 'Inherit' cover to the field in the post meta section with Theme Options
	 * 
	 * @param string $id            Field ID
	 * @param array  $field         Field parameters
	 * @param bool   $inherit_allow Whether the field can be inherited
	 * @param bool   $inherit_state  Current state of the field (true - inherit, false - not inherit)
	 * 
	 * @return string HTML code for the inherit cover
	 */
	function literature_add_inherit_cover( $id, $field, $inherit_allow=false, $inherit_state=false ) {
		return $inherit_allow
					? '<div class="literature_options_inherit_cover'
						. ( ! empty( $field['pro_only'] )
								? ' literature_options_pro_only_cover'
								: ( ! $inherit_state ? ' literature_hidden' : '' ) 
								)
						. '">'
							. ( ! empty( $field['pro_only'] )
								? ( '<a href="' . esc_url( literature_storage_get( 'theme_download_url' ) ) . '"' . literature_external_links_target( true ) . ' class="literature_options_inherit_label literature_options_pro_only_label">'
										. esc_html__( 'Activate Pro version', 'literature' )
									. '</a>' )
								: ( '<span class="literature_options_inherit_label">'
										. esc_html__( 'Inherit', 'literature' )
									. '</span>' )
								)
							. '<input type="hidden" name="literature_options_inherit_' . esc_attr( $id ) . '"'
								. ' value="' . esc_attr( $inherit_state ? 'inherit' : '' ) . '"'
								. ' />'
						. '</div>'
					: ( 'info' != $field['type'] && ! empty( $field['pro_only'] )
						? '<div class="literature_options_inherit_cover literature_options_pro_only_cover">'
								. '<a href="' . esc_url( literature_storage_get( 'theme_download_url' ) ) . '"' . literature_external_links_target( true ) . ' class="literature_options_inherit_label literature_options_pro_only_label">'
									. esc_html__( 'Activate Pro version', 'literature' )
								. '</a>'
							. '</div>'
						: '' );
	}
}


if ( ! function_exists( 'literature_add_responsive_buttons' ) ) {
	/**
	 * Add responsive buttons to the field in Theme Options
	 * 
	 * @param string $id          Field ID
	 * @param array  $field       Field parameters
	 * @param array  $breakpoints Breakpoints data
	 * 
	 * @return string HTML code for the responsive buttons
	 */
	function literature_add_responsive_buttons( $id, $field, $breakpoints ) {
		$html = '';
		if ( ! empty( $field['responsive'] ) ) {
			$html .= '<span class="literature_options_responsive_buttons">'
						. '<span class="literature_options_responsive_buttons_wrap">';
			foreach ( $breakpoints as $bp => $data ) {
				$html .= '<a href="#" role="button" class="literature_options_responsive_button'
							. ' literature_options_responsive_button_' . esc_attr( $bp )
							. ( ! empty( $data['icon'] ) ? ' ' . esc_attr( $data['icon'] ) : '' )
							. '"'
							. ' data-responsive="' . esc_attr( $bp ) . '"'
							. ' title="' . esc_attr( $data['title'] ) . '"'
						. '>'
						. '</a>';
			}
			$html .= 	'</span>'
					. '</span>';
		}
		return $html;
	}
}


if ( ! function_exists( 'literature_show_custom_field' ) ) {
	/**
	 * Show a single custom field in the Theme Options or in the post meta section
	 * 
	 * @param string $id    Field ID
	 * @param array  $field Field parameters
	 * @param string $value Current value of the field
	 * 
	 * @return string HTML code for the custom field
	 */
	function literature_show_custom_field( $id, $field, $value ) {
		$output = '';

		switch ( $field['type'] ) {

			case 'mediamanager':
				// Enqueue media is broke the popup 'Media' inside Gutenberg editor
				if ( ! literature_is_preview( 'gutenberg' ) ) {
					wp_enqueue_media();
				}
				$title   = empty( $field['data_type'] ) || 'image' == $field['data_type']
								? ( ! empty( $field['multiple'] ) ? esc_html__( 'Add Images', 'literature' ) : esc_html__( 'Choose Image', 'literature' ) )
								: ( ! empty( $field['multiple'] ) ? esc_html__( 'Add Media', 'literature' ) : esc_html__( 'Choose Media', 'literature' ) );
				$images  = explode( '|', $value );
				$output .= '<span class="literature_media_selector_preview'
								. ' literature_media_selector_preview_' . ( ! empty( $field['multiple'] ) ? 'multiple' : 'single' )
								. ( is_array( $images ) && count( $images ) > 0 ? ' literature_media_selector_preview_with_image' : '' )
							. '">';
				if ( is_array( $images ) ) {
					foreach ( $images as $img ) {
						$output .= $img && ! literature_is_inherit( $img )
								? '<span class="literature_media_selector_preview_image" tabindex="0">'
										. ( in_array( literature_get_file_ext( $img ), array( 'gif', 'jpg', 'jpeg', 'png' ) )
												? '<img src="' . esc_url( $img ) . '" alt="' . esc_attr__( 'Selected image', 'literature' ) . '">'
												: '<a href="' . esc_attr( $img ) . '">' . esc_html( basename( $img ) ) . '</a>'
											)
									. '</span>'
								: '';
					}
				}
				$output .= '</span>';
				$output .= '<input type="button"'
								. ' id="' . esc_attr( $id ) . '"'
								. ' class="button mediamanager literature_media_selector"'
								. '	data-param="' . esc_attr( $id ) . '"'
								. '	data-choose="' . esc_attr( $title ) . '"'
								. ' data-update="' . esc_attr( $title ) . '"'
								. '	data-multiple="' . esc_attr( ! empty( $field['multiple'] ) ? '1' : '0' ) . '"'
								. '	data-type="' . esc_attr( ! empty( $field['data_type'] ) ? $field['data_type'] : 'image' ) . '"'
								. '	data-linked-field="' . esc_attr( $field['linked_field_id'] ) . '"'
								. ' value="' .  esc_attr( $title ) . '"'
							. '>';
				break;

			case 'icons':
				$icons_type = ! empty( $field['style'] )
								? $field['style']
								: literature_get_theme_setting( 'icons_type' );
				if ( empty( $field['return'] ) ) {
					$field['return'] = 'full';
				}
				$literature_icons = literature_get_list_icons( $icons_type );
				if ( is_array( $literature_icons ) ) {
					if ( ! empty( $field['button'] ) ) {
						$output .= '<span id="' . esc_attr( $id ) . '"'
										. ' tabindex="0"'
										. ' class="literature_list_icons_selector'
												. ( 'icons' == $icons_type && ! empty( $value ) ? ' ' . esc_attr( $value ) : '' )
												. '"'
										. ' title="' . esc_attr__( 'Select icon', 'literature' ) . '"'
										. ' data-style="' . esc_attr( $icons_type ) . '"'
										. ( in_array( $icons_type, array( 'images', 'svg' ) ) && ! empty( $value )
											? ' style="background-image: url(' . esc_url( 'slug' == $field['return'] ? $literature_icons[ $value ] : $value ) . ');"'
											: ''
											)
									. '></span>';
					}
					if ( ! empty( $field['icons'] ) ) {
						$output .= '<div class="literature_list_icons">'
										. '<input type="text" class="literature_list_icons_search" placeholder="' . esc_attr__( 'Search for an icon', 'literature' ) . '">'
										. '<div class="literature_list_icons_wrap">'
											. '<div class="literature_list_icons_inner">';
						foreach ( $literature_icons as $slug => $icon ) {
							$output .= '<span tabindex="0" class="' . esc_attr( 'icons' == $icons_type ? $icon : $slug )
									. ( ( 'full' == $field['return'] ? $icon : $slug ) == $value ? ' literature_list_active' : '' )
									. '"'
									. ' title="' . esc_attr( $slug ) . '"'
									. ' data-icon="' . esc_attr( 'full' == $field['return'] ? $icon : $slug ) . '"'
									. ( ! empty( $icon ) && in_array( $icons_type, array( 'images', 'svg' ) ) ? ' style="background-image: url(' . esc_url( $icon ) . ');"' : '' )
									. '></span>';
						}
						$output .= '</div></div></div>';
					}
				}
				break;

			case 'choice':
				if ( is_array( $field['options'] ) ) {
					$output .= '<div class="literature_list_choice">';
					foreach ( $field['options'] as $slug => $data ) {
						$output .= ( ! empty( $data['new_row'] )
										? '<span class="literature_list_choice_rows_separator"></span>'
										: ''
										) 
								. '<span tabindex="0" class="literature_list_choice_item'
									. ( $slug == $value && strlen( $slug ) == strlen( $value ) ? ' literature_list_active' : '' )
									. '"'
									. ' data-choice="' . esc_attr( $slug ) . '"'
									. ( ! empty( $data[ 'description' ] ) ? ' title="' . esc_attr( $data[ 'description' ] ) . '"' : '' )
								. '>'
									. '<span class="literature_list_choice_item_icon">'
										. '<img src="' . esc_url( literature_get_file_url( $data['icon'] ) ) . '" alt="' . esc_attr( $data['title'] ) . '">'
									. '</span>'
									. '<span class="literature_list_choice_item_title">'
										. esc_html( $data['title'] )
									. '</span>'
								. '</span>';
					}
					$output .= '</div>';
				}
				break;

			case 'checklist':
				if ( ! empty( $field['sortable'] ) ) {
					wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery', 'jquery-ui-core' ), null, true );
				}
				$output .= '<div class="literature_checklist literature_checklist_' . esc_attr( $field['dir'] )
							. ( ! empty( $field['sortable'] ) ? ' literature_sortable' : '' )
							. '">';
				if ( ! is_array( $value ) ) {
					if ( ! empty( $value ) && ! literature_is_inherit( $value ) ) {
						parse_str( str_replace( '|', '&', $value ), $value );
					} else {
						$value = array();
					}
				}
				// Remove not exists values (if a key of value is not present in the 'options')
				if ( is_array( $value ) ) {
					foreach( array_keys( $value ) as $k ) {
						if ( ! isset( $field['options'][ $k ] ) ) {
							unset( $value[ $k ] );
						}
					}
				}
				// Sortable
				if ( ! empty( $field['sortable'] ) ) {
					// Sort options by values order
					if ( is_array( $value ) ) {
						$field['options'] = literature_array_merge( $value, $field['options'] );
					}
					if ( ! empty( $field['group'] ) ) {
						$field['group'] = false;
					}
				}
				$last_group = '';
				foreach ( $field['options'] as $k => $v ) {
					if ( ! empty( $field['group'] ) ) {
						if ( preg_match( '/\\(([^\\)]*)\\)/', $v, $matches ) ) {
							$cur_group = $matches[1];
							$v = trim( str_replace( '(' . $cur_group . ')', '', $v ) );
							if ( $cur_group != $last_group ) {
								$last_group = $cur_group;
								$output .= '<p class="trx_addons_options_item_choises_group">' . esc_html( $last_group ) . '</p>';
							}
						}
					}
					$output .= '<label class="literature_checklist_item_label' . ( ! empty( $field['sortable'] ) ? ' literature_sortable_item' : '' ) . '"'
									. ( 'horizontal' == $field['dir'] && substr( $v, 0, 4 ) != 'http' && strlen( $v ) >= 20 ? ' title="' . esc_attr( $v ) . '"' : '' )
								. '>'
								. '<input type="checkbox" value="1" data-name="' . $k . '"'
									. ( isset( $value[ $k ] ) && 1 == (int) $value[ $k ] ? ' checked="checked"' : '' )
									. ' />'
								. ( substr( $v, 0, 4 ) == 'http' ? '<img src="' . esc_url( $v ) . '">' : esc_html( $v ) )
							. '</label>';
				}
				$output .= '</div>';
				break;

			case 'slider':
			case 'range':
				wp_enqueue_script( 'jquery-ui-slider', false, array( 'jquery', 'jquery-ui-core' ), null, true );
				$is_range   = 'range' == $field['type'];
				$field_min  = ! empty( $field['min'] ) ? $field['min'] : 0;
				$field_max  = ! empty( $field['max'] ) ? $field['max'] : 100;
				$field_step = ! empty( $field['step'] ) ? $field['step'] : 1;
				$field_val  = ! empty( $value )
								? ( $value . ( $is_range && strpos( $value, ',' ) === false ? ',' . $field_max : '' ) )
								: ( $is_range ? $field_min . ',' . $field_max : $field_min );
				$output    .= '<div id="' . esc_attr( $id ) . '"'
								. ' class="literature_range_slider"'
								. ' data-range="' . esc_attr( $is_range ? 'true' : 'min' ) . '"'
								. ' data-min="' . esc_attr( $field_min ) . '"'
								. ' data-max="' . esc_attr( $field_max ) . '"'
								. ' data-step="' . esc_attr( $field_step ) . '"'
								. '>'
								. '<span class="literature_range_slider_label literature_range_slider_label_min">'
									. esc_html( $field_min )
								. '</span>'
								. '<span class="literature_range_slider_label literature_range_slider_label_avg">'
									. ( ( $field_max + $field_min ) / 2 == intval( ( $field_max + $field_min ) / 2 ) || $field_step !== intval( $field_step )
										? esc_html( round( ( $field_max + $field_min ) / 2, $field_step == (int)$field_step ? 0 : 2 ) )
										: ''
										)
								. '</span>'
								. '<span class="literature_range_slider_label literature_range_slider_label_max">'
									. esc_html( $field_max )
								. '</span>';
				$output    .= '<div class="literature_range_slider_scale">';
				for ( $i = 0; $i <= 11; $i++ ) {
					$output    .= '<span></span>';
				}
				$output    .= '</div>';
				$values     = explode( ',', $field_val );
				for ( $i = 0; $i < count( $values ); $i++ ) {
					$output .= '<span class="literature_range_slider_label literature_range_slider_label_cur">'
									. esc_html( $values[ $i ] )
								. '</span>';
				}
				$output .= '</div>';
				break;

			case 'text_editor':
				if ( function_exists( 'wp_enqueue_editor' ) ) {
					wp_enqueue_editor();
				}
				ob_start();
				wp_editor(
					$value, $id, array(
						'default_editor' => 'tmce',
						'wpautop'        => isset( $field['wpautop'] ) ? $field['wpautop'] : false,
						'teeny'          => isset( $field['teeny'] ) ? $field['teeny'] : false,
						'textarea_rows'  => isset( $field['rows'] ) && $field['rows'] > 1 ? $field['rows'] : 10,
						'editor_height'  => 16 * ( isset( $field['rows'] ) && $field['rows'] > 1 ? (int) $field['rows'] : 10 ),
						'tinymce'        => array(
							'resize'             => false,
							'wp_autoresize_on'   => false,
							'add_unload_trigger' => false,
						),
					)
				);
				$editor_html = ob_get_contents();
				ob_end_clean();
				$output .= '<div class="literature_text_editor" data-editor-html="' . esc_attr( $editor_html ) . '">' . $editor_html . '</div>';
				break;

			case 'scheme_editor':
				if ( ! is_array( $value ) ) {
					break;
				}
				if ( empty( $field['colorpicker'] ) ) {
					$field['colorpicker'] = 'internal';
				}
				$output .= '<div class="literature_scheme_editor">';
				// Select scheme
				if ( apply_filters( 'literature_filter_scheme_editor_show_selector', true ) ) {
					$output .= '<div class="literature_scheme_editor_scheme">'
									. '<select class="literature_scheme_editor_selector">';
					foreach ( $value as $scheme => $v ) {
						$output .= '<option value="' . esc_attr( $scheme ) . '">' . esc_html( $v['title'] ) . '</option>';
					}
					$output .= '</select>';
					// Scheme controls
					$output .= '<span class="literature_scheme_editor_controls">'
									. '<span class="literature_scheme_editor_control literature_scheme_editor_control_reset" title="' . esc_attr__( 'Reset scheme', 'literature' ) . '"></span>'
									. '<span class="literature_scheme_editor_control literature_scheme_editor_control_copy" title="' . esc_attr__( 'Duplicate scheme', 'literature' ) . '"></span>'
									. '<span class="literature_scheme_editor_control literature_scheme_editor_control_delete" title="' . esc_attr__( 'Delete scheme', 'literature' ) . '"></span>'
								. '</span>'
							. '</div>';
				}
				// Select type
				$schemes_simple = literature_storage_get( 'schemes_simple' );
				$output .= '<div class="literature_scheme_editor_type' . ( count( $schemes_simple ) == 0 ? ' literature_hidden' : '' ) . '">'
								. '<div class="literature_scheme_editor_row">'
									. '<span class="literature_scheme_editor_row_cell">'
										. esc_html__( 'Editor type', 'literature' )
									. '</span>'
									. '<span class="literature_scheme_editor_row_cell literature_scheme_editor_row_cell_span">'
										. '<label>'
											. '<input name="literature_scheme_editor_type" type="radio" value="simple"' . ( count( $schemes_simple ) > 0 ? ' checked="checked"' : '' ) . '> '
											. '<span class="literature_options_item_holder" tabindex="0"></span>'
											. '<span class="literature_options_item_caption">'
												. esc_html__( 'Simple', 'literature' )
											. '</span>'
										. '</label>'
										. '<label>'
											. '<input name="literature_scheme_editor_type" type="radio" value="advanced"' . ( count( $schemes_simple ) == 0 ? ' checked="checked"' : '' ) . '> '
											. '<span class="literature_options_item_holder" tabindex="0"></span>'
											. '<span class="literature_options_item_caption">'
												. esc_html__( 'Advanced', 'literature' )
											. '</span>'
										. '</label>'
									. '</span>'
								. '</div>'
							. '</div>';
				// Colors
				$used    = array();
				$groups  = literature_storage_get( 'scheme_color_groups' );
				$colors  = literature_storage_get( 'scheme_color_names' );
				$output .= '<div class="literature_scheme_editor_colors">';
				$first   = true;
				foreach ( $value as $scheme => $v ) {
					if ( $first ) {
						$output .= '<div class="literature_scheme_editor_header">'
										. '<span class="literature_scheme_editor_header_cell literature_scheme_editor_row_cell_caption"></span>';
						// Display column titles
						foreach ( $groups as $group_name => $group_data ) {
							$output .= '<span class="literature_scheme_editor_header_cell literature_scheme_editor_row_cell_color" title="' . esc_attr( $group_data['description'] ) . '">'
										. esc_html( $group_data['title'] )
										. '</span>';
						}
						$output .= '</div>';
						// Each row - it's a group of colors: text_light - alter_light - extra_light - ...
						foreach ( $colors as $color_name => $color_data ) {
							$output .= '<div class="literature_scheme_editor_row">'
										. '<span class="literature_scheme_editor_row_cell literature_scheme_editor_row_cell_caption" title="' . esc_attr( $color_data['description'] ) . '">'
										. esc_html( $color_data['title'] )
										. '</span>';
							foreach ( $groups as $group_name => $group_data ) {
								$slug    = 'main' == $group_name
											? $color_name
											: str_replace( 'text_', '', "{$group_name}_{$color_name}" );
								$used[]  = $slug;
								$output .= '<span class="literature_scheme_editor_row_cell literature_scheme_editor_row_cell_color"'
											. ' title="' . esc_attr( sprintf( '%1$s: %2$s', $group_data['description'], $color_data['description'] ) ) . '"'
											. '>'
												. ( isset( $v['colors'][ $slug ] )
													? "<input type=\"text\" name=\"{$slug}\" class=\""
														. ( 'tiny' == $field['colorpicker']
															? 'tinyColorPicker'
															: ( 'spectrum' == $field['colorpicker']
																? 'spectrumColorPicker'
																: 'iColorPicker'
																)
															) 
														. '"'
														. ' data-alpha-enabled="' . ( ! empty( $field['alpha'] ) ? 'true' : 'false' ) . '"'
														. ' value="' . esc_attr( $v['colors'][ $slug ] ) . '">'
													: ''
													)
											. '</span>';
							}
							$output .= '</div>';
						}
					}
					// Additional color ( defined by theme / skin developer ) - only in the main group
					foreach ( $v['colors'] as $slug => $color ) {
						if ( in_array( $slug, $used ) ) {
							continue;
						}
						$title   = ! empty( $colors[ $slug ][ 'title' ] )
										? $colors[ $slug ][ 'title' ]
										: ucfirst( join( ' ', explode( '_', $slug ) ) );
						$output .= '<div class="literature_scheme_editor_row">'
									. '<span class="literature_scheme_editor_row_cell literature_scheme_editor_row_cell_caption"'
										. ( ! empty( $colors[ $slug ][ 'description' ] )
											? ' title="' . esc_attr( $colors[ $slug ][ 'description' ] ) . '"'
											: '' )
									. '>'
										. esc_html( $title )
									. '</span>';
						foreach ( $groups as $group_name => $group_data ) {
							$fld = 'main' == $group_name
											? $slug
											: "{$group_name}_{$slug}";
							$used[]  = $fld;
							$output .= '<span class="literature_scheme_editor_row_cell literature_scheme_editor_row_cell_color">'
											. ( isset( $v['colors'][ $fld ] )
												? '<input type="text" name="' . esc_attr( $fld ) . '" class="'
													. ( 'tiny' == $field['colorpicker']
														? 'tinyColorPicker'
														: ( 'spectrum' == $field['colorpicker']
															? 'spectrumColorPicker'
															: 'iColorPicker'
															)
														) 
													. '" value="' . esc_attr( $v['colors'][ $fld ] ) . '">'
												: ''
												)
										. '</span>';
						}
						$output .= '</div>';
					}
					$first = false;
					// If all schemes contain similar colors - break
					break;
				}
				$output .= '</div>'
						. '</div>';
				break;
		}
		return apply_filters( 'literature_filter_show_custom_field', $output, $id, $field, $value );
	}
}


if ( ! function_exists( 'literature_refresh_linked_data' ) ) {
	/**
	 * Refresh data in the linked field according the main field value
	 * 
	 * @param mixed  $value        Value of the main field
	 * @param string $linked_name  Name of the linked field
	 */
	function literature_refresh_linked_data( $value, $linked_name ) {
		if ( 'parent_cat' == $linked_name ) {
			$tax   = literature_get_post_type_taxonomy( $value );
			$terms = ! empty( $tax ) ? literature_get_list_terms( false, $tax ) : array();
			$terms = literature_array_merge( array( 0 => literature_get_not_selected_text( esc_html__( 'Select category', 'literature' ) ) ), $terms );
			literature_storage_set_array2( 'options', $linked_name, 'options', $terms );
		}
	}
}


if ( ! function_exists( 'literature_callback_get_linked_data' ) ) {
	add_action( 'wp_ajax_literature_get_linked_data', 'literature_callback_get_linked_data' );
	/**
	 * AJAX handler to refresh data in the linked fields
	 * 
	 * @hooked 'wp_ajax_literature_get_linked_data'
	 */
	function literature_callback_get_linked_data() {
		literature_verify_nonce();
		$response  = array( 'error' => '' );
		if ( ! empty( $_REQUEST['chg_name'] ) ) {
			$chg_name  = wp_kses_data( wp_unslash( $_REQUEST['chg_name'] ) );
			$chg_value = wp_kses_data( wp_unslash( $_REQUEST['chg_value'] ) );
			if ( 'post_type' == $chg_name ) {
				$tax              = literature_get_post_type_taxonomy( $chg_value );
				$terms            = ! empty( $tax ) ? literature_get_list_terms( false, $tax ) : array();
				$response['list'] = literature_array_merge( array( 0 => literature_get_not_selected_text( esc_html__( 'Select category', 'literature' ) ) ), $terms );
			}
		}
		literature_ajax_response( $response );
	}
}
