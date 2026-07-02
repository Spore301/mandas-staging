<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: //codex.wordpress.org/Template_Hierarchy
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */

$literature_template = apply_filters( 'literature_filter_get_template_part', literature_blog_archive_get_template() );

if ( ! empty( $literature_template ) && 'index' != $literature_template ) {

	get_template_part( $literature_template );

} else {

	literature_storage_set( 'blog_archive', true );

	get_header();

	if ( have_posts() ) {

		// Query params
		$literature_stickies   = is_home()
								|| ( in_array( literature_get_theme_option( 'post_type' ), array( '', 'post' ) )
									&& (int) literature_get_theme_option( 'parent_cat' ) == 0
									)
										? get_option( 'sticky_posts' )
										: false;
		$literature_post_type  = literature_get_theme_option( 'post_type' );
		$literature_args       = array(
								'blog_style'     => literature_get_theme_option( 'blog_style' ),
								'post_type'      => $literature_post_type,
								'taxonomy'       => literature_get_post_type_taxonomy( $literature_post_type ),
								'parent_cat'     => literature_get_theme_option( 'parent_cat' ),
								'posts_per_page' => literature_get_theme_option( 'posts_per_page' ),
								'sticky'         => literature_get_theme_option( 'sticky_style', 'inherit' ) == 'columns'
															&& is_array( $literature_stickies )
															&& count( $literature_stickies ) > 0
															&& get_query_var( 'paged' ) < 1
								);

		literature_blog_archive_start();

		do_action( 'literature_action_blog_archive_start' );

		if ( is_author() ) {
			do_action( 'literature_action_before_page_author' );
			get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/author-page' ) );
			do_action( 'literature_action_after_page_author' );
		}

		if ( literature_get_theme_option( 'show_filters', 0 ) ) {
			do_action( 'literature_action_before_page_filters' );
			literature_show_filters( $literature_args );
			do_action( 'literature_action_after_page_filters' );
		} else {
			do_action( 'literature_action_before_page_posts' );
			literature_show_posts( array_merge( $literature_args, array( 'cat' => $literature_args['parent_cat'] ) ) );
			do_action( 'literature_action_after_page_posts' );
		}

		do_action( 'literature_action_blog_archive_end' );

		literature_blog_archive_end();

	} else {

		if ( is_search() ) {
			get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/content', 'none-search' ), 'none-search' );
		} else {
			get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/content', 'none-archive' ), 'none-archive' );
		}
	}

	get_footer();
}
