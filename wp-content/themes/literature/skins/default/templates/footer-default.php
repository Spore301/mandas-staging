<?php
/**
 * The template to display default site footer
 *
 * @package LITERATURE
 * @since LITERATURE 1.0.10
 */

?>
<footer class="footer_wrap footer_default">
	<?php
	// Footer widgets area
	get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/footer-widgets' ) );
	// Copyright area
	get_template_part( apply_filters( 'literature_filter_get_template_part', 'templates/footer-copyright' ) );
	?>
</footer>