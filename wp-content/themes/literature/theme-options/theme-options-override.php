<?php
/**
 * Override Theme Options on a posts and pages
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.29
 */


// -----------------------------------------------------------------
// -- Override Theme Options
// -----------------------------------------------------------------

if ( ! function_exists( 'literature_options_override_init' ) ) {
	add_action( 'after_setup_theme', 'literature_options_override_init' );
	/**
	 * Initialize the override options functionality - add actions and filters
	 */
	function literature_options_override_init() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', 'literature_options_override_add_scripts' );
			add_action( 'save_post', 'literature_options_override_save_options' );
			add_filter( 'literature_filter_override_options', 'literature_options_override_add_options' );
		}
	}
}


if ( ! function_exists( 'literature_options_allow_override' ) ) {
	/**
	 * Check if override options is allowed for specified post type. By default it is allowed for 'page' and 'post' post types.
	 * 
	 * @trigger 'literature_filter_allow_override_options'
	 * 
	 * @param string $post_type  Post type slug
	 * 
	 * @return bool  True if override options is allowed for the post type, false otherwise
	 */
	function literature_options_allow_override( $post_type ) {
		return apply_filters( 'literature_filter_allow_override_options', in_array( $post_type, array( 'page', 'post' ) ), $post_type );
	}
}

if ( ! function_exists( 'literature_options_override_add_scripts' ) ) {
	/**
	 * Load required styles and scripts for the admin mode
	 * 
	 * @hooked 'admin_enqueue_scripts'
	 */
	function literature_options_override_add_scripts() {
		// If current screen is 'Edit Page' - load font icons
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( is_object( $screen ) && literature_options_allow_override( ! empty( $screen->post_type ) ? $screen->post_type : $screen->id ) ) {
			wp_enqueue_style( 'literature-fontello', literature_get_file_url( 'css/font-icons/css/fontello.css' ), array(), null );
			wp_enqueue_script( 'jquery-ui-tabs', false, array( 'jquery', 'jquery-ui-core' ), null, true );
			wp_enqueue_script( 'jquery-ui-accordion', false, array( 'jquery', 'jquery-ui-core' ), null, true );
			wp_enqueue_script( 'literature-options', literature_get_file_url( 'theme-options/theme-options.js' ), array( 'jquery' ), null, true );
			wp_localize_script( 'literature-options', 'literature_dependencies', literature_get_theme_dependencies() );
		}
	}
}

if ( ! function_exists( 'literature_options_override_add_options' ) ) {
	/**
	 * Add a new section to the post/page options list to override theme options in the post/page meta box.
	 * 
	 * @hooked 'literature_filter_override_options'
	 * 
	 * @param array $list  An array of options sections to add a new section to it
	 * 
	 * @return array  An array of options sections with the new section added
	 */
	function literature_options_override_add_options( $list ) {
		global $post_type;
		if ( literature_options_allow_override( $post_type ) ) {
			$list[] = array(
				sprintf( 'literature_override_options_%s', $post_type ),
				esc_html__( 'Theme Options', 'literature' ),
				'literature_options_override_show',
				$post_type,
				'advanced',
				'default',
			);
		}
		return $list;
	}
}

if ( ! function_exists( 'literature_options_override_show' ) ) {
	/**
	 * Callback function to show override options in the post/page meta box.
	 * 
	 * @param object|bool $post  Post object or false to use global post
	 * @param array|bool  $args  Additional arguments (not used)
	 */
	function literature_options_override_show( $post = false, $args = false ) {
		if ( empty( $post ) || ! is_object( $post ) || empty( $post->ID ) ) {
			global $post, $post_type;
			$mb_post_id   = $post->ID;
			$mb_post_type = $post_type;
		} else {
			$mb_post_id   = $post->ID;
			$mb_post_type = $post->post_type;
		}
		if ( literature_options_allow_override( $mb_post_type ) ) {
			// Load saved options
			$meta         = get_post_meta( $mb_post_id, 'literature_options', true );
			$tabs_titles  = array();
			$tabs_content = array();
			global $LITERATURE_STORAGE;
			// Refresh linked data if this field is controller for the another (linked) field
			// Do this before show fields to refresh data in the $LITERATURE_STORAGE
			foreach ( $LITERATURE_STORAGE['options'] as $k => $v ) {
				if ( ! isset( $v['override'] ) || strpos( $v['override']['mode'], $mb_post_type ) === false ) {
					continue;
				}
				if ( ! empty( $v['linked'] ) ) {
					$v['val'] = isset( $meta[ $k ] ) ? $meta[ $k ] : 'inherit';
					if ( ! empty( $v['val'] ) && ! literature_is_inherit( $v['val'] ) ) {
						literature_refresh_linked_data( $v['val'], $v['linked'] );
					}
				}
			}
			// Show fields
			foreach ( $LITERATURE_STORAGE['options'] as $k => $v ) {
				if ( ! isset( $v['override'] ) || strpos( $v['override']['mode'], $mb_post_type ) === false || 'hidden' == $v['type'] ) {
					continue;
				}
				if ( empty( $v['override']['section'] ) ) {
					$v['override']['section'] = esc_html__( 'General', 'literature' );
				}
				if ( ! isset( $tabs_titles[ $v['override']['section'] ] ) ) {
					$tabs_titles[ $v['override']['section'] ]  = $v['override']['section'];
					$tabs_content[ $v['override']['section'] ] = '';
				}
				$v['val'] = isset( $meta[ $k ] ) ? $meta[ $k ] : 'inherit';
				if ( 'group' == $v['type'] ) {
					// Fields set (group)
					if ( count( $v['fields'] ) > 0 ) {
						$tabs_content[ $v['override']['section'] ] .= literature_options_show_group( $k, $v, $mb_post_type );
					}
				} else {
					// Regular field
					$tabs_content[ $v['override']['section'] ] .= literature_options_show_field( $k, $v, $mb_post_type );
				}
			}

			// Display options
			if ( count( $tabs_titles ) > 0 ) {
				// Add Options presets
				$tabs_titles[ 'presets' ]  = esc_html__( 'Options presets', 'literature' );
				$tabs_content[ 'presets' ] = literature_options_show_field( 'presets', array(
												'title' => esc_html__( 'Options Presets', 'literature' ),
												'desc'  => esc_html__( 'Select a preset to override options of the current page or save current options as a new preset', 'literature' ),
												'type'  => 'presets',
											), $mb_post_type );
				?>
				<div class="literature_options literature_options_override">
					<input type="hidden" name="override_options_nonce" value="<?php echo esc_attr( wp_create_nonce( admin_url() ) ); ?>" />
					<input type="hidden" name="override_options_post_type" value="<?php echo esc_attr( $mb_post_type ); ?>" />
					<div id="literature_options_tabs" class="literature_tabs literature_tabs_vertical">
						<ul>
							<?php
							$cnt = 0;
							foreach ( $tabs_titles as $k => $v ) {
								$cnt++;
								?>
								<li><a href="#literature_options_<?php echo esc_attr( $cnt ); ?>"><?php echo esc_html( $v ); ?></a></li>
								<?php
							}
							?>
						</ul>
						<?php
						$cnt = 0;
						foreach ( $tabs_content as $k => $v ) {
							$cnt++;
							?>
							<div id="literature_options_<?php echo esc_attr( $cnt ); ?>" class="literature_tabs_section literature_options_section">
								<?php literature_show_layout( $v ); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
		}
	}
}


if ( ! function_exists( 'literature_options_override_save_options' ) ) {
	/**
	 * Save overriden options on the post/page save.
	 * 
	 * @hooked 'save_post'
	 * 
	 * @param int $post_id  Post ID to save options for
	 */
	function literature_options_override_save_options( $post_id ) {
		// verify nonce
		if ( ! wp_verify_nonce( literature_get_value_gp( 'override_options_nonce' ), admin_url() ) ) {
			return $post_id;
		}

		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$post_type = wp_kses_data( wp_unslash( isset( $_POST['override_options_post_type'] ) ? $_POST['override_options_post_type'] : $_POST['post_type'] ) );

		// Check permissions
		if ( empty( $post_id ) || ! literature_current_user_can_edit( $post_id, $post_type, 'page' ) ) {
			return $post_id;
		}

		// Save options
		$meta    = array();
		$options = literature_storage_get( 'options' );
		foreach ( $options as $k => $v ) {
			// Skip not overriden options
			if ( ! isset( $v['override'] ) || strpos( $v['override']['mode'], $post_type ) === false ) {
				continue;
			}
			// Skip inherited options
			if ( ! empty( $_POST[ "literature_options_inherit_{$k}" ] ) ) {
				continue;
			}
			// Skip hidden options
			if ( ! isset( $_POST[ "literature_options_field_{$k}" ] ) && 'hidden' == $v['type'] ) {
				continue;
			}
			// Get option value from POST
			$meta[ $k ] = isset( $_POST[ "literature_options_field_{$k}" ] )
							? literature_get_value_gp( "literature_options_field_{$k}" )
							: ( 'checkbox' == $v['type'] ? 0 : '' );
		}
		$meta = apply_filters( 'literature_filter_update_post_options', $meta, $post_id, $post_type );

		update_post_meta( $post_id, 'literature_options', $meta );

		// Save separate meta options to search template pages
		if ( 'page' == $post_type ) {
			$page_template = isset( $_POST['page_template'] )
								? $_POST['page_template']
								: get_post_meta( $post_id, '_wp_page_template', true );
			if ( 'blog.php' == $page_template ) {
				update_post_meta( $post_id, 'literature_options_post_type', isset( $meta['post_type'] ) ? $meta['post_type'] : 'post' );
				update_post_meta( $post_id, 'literature_options_parent_cat', isset( $meta['parent_cat'] ) ? $meta['parent_cat'] : 0 );
			}
		}
	}
}


//------------------------------------------------------
// Extra column for posts/pages lists with overriden options
//------------------------------------------------------

if ( ! function_exists( 'literature_add_options_column' ) ) {
	add_filter( 'manage_edit-post_columns', 'literature_add_options_column', 9 );
	add_filter( 'manage_edit-page_columns', 'literature_add_options_column', 9 );
	/**
	 * Create additional column in the posts/pages lists to show overriden options
	 * 
	 * @hooked 'manage_edit-post_columns', 9
	 * @hooked 'manage_edit-page_columns', 9
	 * 
	 * @param array $columns  An array of columns to add a new column to it
	 * 
	 * @return array  An array of columns with the new column added
	 */
	function literature_add_options_column( $columns ) {
		$columns['theme_options'] = esc_html__( 'Theme Options', 'literature' );
		return $columns;
	}
}

if ( ! function_exists( 'literature_fill_options_column' ) ) {
	add_filter( 'manage_post_posts_custom_column', 'literature_fill_options_column', 9, 2 );
	add_filter( 'manage_page_posts_custom_column', 'literature_fill_options_column', 9, 2 );
	/**
	 * Fill added columns with overriden options data
	 * 
	 * @hooked 'manage_post_posts_custom_column', 9
	 * @hooked 'manage_page_posts_custom_column', 9
	 * 
	 * @param string $column_name  Column name
	 * @param int    $post_id      Post ID to get options for
	 */
	function literature_fill_options_column( $column_name = '', $post_id = 0 ) {
		if ( 'theme_options' != $column_name ) {
			return;
		}
		$options = '';
		$props = get_post_meta( $post_id, 'literature_options', true);
		if ( $props ) {
			if ( is_array( $props ) && count( $props ) > 0 ) {
				foreach ( $props as $prop_name => $prop_value ) {
					if ( ! literature_is_inherit( $prop_value ) && literature_storage_get_array( 'options', $prop_name, 'type' ) != 'hidden' ) {
						$prop_title = literature_storage_get_array( 'options', $prop_name, 'title' );
						if ( empty( $prop_title ) ) {
							$prop_title = $prop_name;
						}
						$options .= '<div class="literature_options_prop_row">'
										. '<span class="literature_options_prop_name">' . esc_html( $prop_title ) . '</span>'
										. '&nbsp;=&nbsp;'
										. '<span class="literature_options_prop_value">'
											. ( is_array( $prop_value )
												? esc_html__('[Complex Data]', 'literature')
												: '"' . esc_html( literature_strshort( $prop_value, 80 ) ) . '"'
												)
										. '</span>'
									. '</div>';
					}
				}
			}
		}
		literature_show_layout( $options, '<div class="literature_options_list">', '</div>' );
	}
}

if ( ! function_exists( 'literature_display_post_states' ) ) {
	add_filter( 'display_post_states', 'literature_display_post_states', 9, 2 );
	/**
	 * Display 'Blog archive' as post state for the page with 'blog.php' template.
	 * 
	 * @hooked 'display_post_states', 9
	 * 
	 * @param array $post_states  An array of post states to add a new state to it
	 * @param object $post        Post object to check if it is a page with '
	 * 
	 * @return array  An array of post states with the new state added
	 */
	function literature_display_post_states( $post_states, $post ) {
		if ( is_object( $post ) && ! empty( $post->post_type ) && 'page' == $post->post_type ) {
			if ( get_post_meta( $post->ID, '_wp_page_template', true ) == 'blog.php' ) {
				$props = get_post_meta( $post->ID, 'literature_options', true);
				$post_type_and_cat = '';
				if ( empty( $props['post_type'] ) ) {
					if ( ! is_array( $props ) ) {
						$props = array();
					}
					$props['post_type'] = 'post';
				}
				$post_obj = get_post_type_object( $props['post_type'] );
				$post_type_and_cat = is_object( $post_obj )
										? $post_obj->labels->name
										: $props['post_type'];
				if ( ! empty( $props['parent_cat'] ) ) {
					$term = get_term_by( 'id', $props['parent_cat'], literature_get_post_type_taxonomy( $props['post_type'] ), OBJECT );
					if ( $term ) {
						$post_type_and_cat .= ' -> ' . $term->name;
					}
				}
				$post_states[] = ! empty( $post_type_and_cat )
									// Translators: Add post type and category to the page state
									? sprintf( esc_html__( 'Blog archive for "%s"', 'literature' ), $post_type_and_cat )
									: esc_html__( 'Blog archive', 'literature' );
			}
		}
		return $post_states;
	}
}


//------------------------------------------------------
// Options presets
//------------------------------------------------------

if ( ! function_exists( 'literature_callback_add_options_preset' ) ) {
	add_action( 'wp_ajax_literature_add_options_preset', 'literature_callback_add_options_preset' );
	/**
	 * AJAX handler to add (save) a new preset with options data.
	 * 
	 * @hooked 'wp_ajax_literature_add_options_preset'
	 */
	function literature_callback_add_options_preset() {
		literature_verify_nonce();
		if ( ! current_user_can( 'manage_options' ) ) {
			literature_forbidden( esc_html__( 'Sorry, you are not allowed to manage options.', 'literature' ) );
		}
		$response  = array( 'error' => '', 'success' => '' );
		if ( ! empty( $_REQUEST['preset_name'] ) && ! empty( $_REQUEST['preset_data'] ) ) {
			$preset_name = wp_kses_data( wp_unslash( $_REQUEST['preset_name'] ) );
			$preset_data = wp_kses_data( wp_unslash( $_REQUEST['preset_data'] ) );
			$preset_type = wp_kses_data( wp_unslash( $_REQUEST['preset_type'] ) );
			if ( empty( $preset_type ) ) {
				$preset_type = '#';
			}
			$presets = get_option( 'literature_options_presets' );
			if ( empty( $presets ) || ! is_array( $presets ) ) {
				$presets = array();
			}
			if ( empty( $presets[ $preset_type ] ) || ! is_array( $presets[ $preset_type ] ) ) {
				$presets[ $preset_type ] = array();
			}
			$presets[ $preset_type ][ $preset_name ] = $preset_data;
			update_option( 'literature_options_presets', $presets );
			// Translators: Add preset name to the message
			$response['success'] = esc_html( sprintf( __( 'Preset "%s" is added!', 'literature' ), $preset_name ) );
		} else {
			$response['error'] = esc_html__( 'Wrong preset name or options data is received! Preset is not added!', 'literature' );
		}
		literature_ajax_response( $response );
	}
}

if ( ! function_exists( 'literature_callback_delete_options_preset' ) ) {
	add_action( 'wp_ajax_literature_delete_options_preset', 'literature_callback_delete_options_preset' );
	/**
	 * AJAX handler to delete a preset with options data.
	 * 
	 * @hooked 'wp_ajax_literature_delete_options_preset'
	 */
	function literature_callback_delete_options_preset() {
		literature_verify_nonce();
		if ( ! current_user_can( 'manage_options' ) ) {
			literature_forbidden( esc_html__( 'Sorry, you are not allowed to manage options.', 'literature' ) );
		}
		$response  = array( 'error' => '', 'success' => '' );
		if ( ! empty( $_REQUEST['preset_name'] ) ) {
			$preset_name = wp_kses_data( wp_unslash( $_REQUEST['preset_name'] ) );
			$preset_type = wp_kses_data( wp_unslash( $_REQUEST['preset_type'] ) );
			if ( empty( $preset_type ) ) {
				$preset_type = '#';
			}
			$presets = get_option( 'literature_options_presets' );
			if ( isset( $presets[ $preset_type ][ $preset_name ] ) ) {
				unset( $presets[ $preset_type ][ $preset_name ] );
				update_option( 'literature_options_presets', $presets );
			}
			// Translators: Add preset name to the message
			$response['success'] = esc_html( sprintf( __( 'Preset "%s" is deleted!', 'literature' ), $preset_name ) );
		} else {
			$response['error'] = esc_html__( 'Wrong preset name is received! Preset is not deleted!', 'literature' );
		}
		literature_ajax_response( $response );
	}
}
