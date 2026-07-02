<?php
/**
 * The template to display the copyright info in the footer
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.10
 */

// Copyright area
?> 
<div class="footer_copyright_wrap">
	<div class="footer_copyright_inner">
		<div class="content_wrap">
			<div class="copyright_text">
				<?php
					$literature_copyright = literature_get_theme_option( 'copyright' );
					if ( ! empty( $literature_copyright ) ) {
						// Replace {{Y}} or {Y} with the current year
						$literature_copyright = str_replace( array( '{{Y}}', '{Y}' ), date( 'Y' ), $literature_copyright );
						// Replace {{...}} and ((...)) on the <i>...</i> and <b>...</b>
						$literature_copyright = literature_prepare_macros( $literature_copyright );
						// Display copyright
						echo wp_kses( nl2br( $literature_copyright ), 'literature_kses_content' );
					}
				?>
			</div>
		</div>
	</div>
</div>