<?php
/**
 * The template to display the Author bio
 *
 * @package LITERATURE
 * @since LITERATURE 1.0
 */
?>

<div class="author_info author vcard"<?php
	if ( literature_is_on( literature_get_theme_option( 'seo_snippets' ) ) ) {
		?> itemprop="author" itemscope="itemscope" itemtype="<?php echo esc_attr( literature_get_protocol( true ) ); ?>//schema.org/Person"<?php
	}
?>>

	<div class="author_avatar"<?php
		if ( literature_is_on( literature_get_theme_option( 'seo_snippets' ) ) ) {
			?> itemprop="image"<?php
	}
	?>>
		<?php
		$literature_mult = literature_get_retina_multiplier();
		echo get_avatar( get_the_author_meta( 'user_email' ), 120 * $literature_mult );
		?>
	</div>

	<div class="author_description">
		<h6 class="author_title"<?php
			if ( literature_is_on( literature_get_theme_option( 'seo_snippets' ) ) ) {
				?> itemprop="name"<?php
			}
		?>><a class="author_link fn" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author"><?php
			the_author();
		?></a></h6>
		<div class="author_label"><?php esc_html_e( 'About Author', 'literature' ); ?></div>
		<div class="author_bio"<?php
			if ( literature_is_on( literature_get_theme_option( 'seo_snippets' ) ) ) {
				?> itemprop="description"<?php
			}
		?>>
			<?php echo wp_kses( wpautop( get_the_author_meta( 'description' ) ), 'literature_kses_content' ); ?>
			<div class="author_links">
				<?php do_action( 'literature_action_user_meta', 'author-bio' ); ?>
			</div>
		</div>

	</div>

</div>
