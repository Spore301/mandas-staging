<?php
/**
 * The template to display the background video in the header
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.14
 */
$literature_header_video = literature_get_header_video();
$literature_embed_video  = '';
if ( ! empty( $literature_header_video ) && ! literature_is_from_uploads( $literature_header_video ) ) {
	if ( literature_is_youtube_url( $literature_header_video ) && preg_match( '/[=\/]([^=\/]*)$/', $literature_header_video, $matches ) && ! empty( $matches[1] ) ) {
		?><div id="background_video" data-youtube-code="<?php echo esc_attr( $matches[1] ); ?>"></div>
		<?php
	} else {
		?>
		<div id="background_video"><?php literature_show_layout( literature_get_embed_video( $literature_header_video ) ); ?></div>
		<?php
	}
}