<?php
/**
 * The default template to display the content of the single page
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */
?>

<article id="post-<?php the_ID(); ?>"
	<?php
	post_class( 'post_item_single post_type_page' );
	literature_add_seo_itemprops();
	?>
>

	<?php
	do_action( 'literature_action_before_post_data' );

	literature_add_seo_snippets();

	do_action( 'literature_action_before_post_content' );
	?>

	<div class="post_content entry-content">
		<?php
			the_content();

			wp_link_pages(
				array(
					'before'      => '<div class="page_links"><span class="page_links_title">' . esc_html__( 'Pages:', 'literature' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
					'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'literature' ) . ' </span>%',
					'separator'   => '<span class="screen-reader-text">, </span>',
				)
			);
			?>
	</div>

	<?php
	do_action( 'literature_action_after_post_content' );

	do_action( 'literature_action_after_post_data' );
	?>

</article>
