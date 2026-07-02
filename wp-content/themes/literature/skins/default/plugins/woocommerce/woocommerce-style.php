<?php
// Add plugin-specific colors and fonts to the custom CSS
if ( ! function_exists( 'literature_woocommerce_get_css' ) ) {
	add_filter( 'literature_filter_get_css', 'literature_woocommerce_get_css', 10, 2 );
	function literature_woocommerce_get_css( $css, $args ) {

		if ( isset( $css['fonts'] ) && isset( $args['fonts'] ) ) {
			$fonts         = $args['fonts'];
			$css['fonts'] .= <<<CSS

/* product archive title */
.woocommerce ul.products li.product .woocommerce-loop-category__title,
.woocommerce ul.products li.product .woocommerce-loop-product__title,
.woocommerce ul.products li.product h3 {
	{$fonts['h6_font-family']}
	{$fonts['h6_font-size']}
	{$fonts['h6_font-weight']}
	{$fonts['h6_font-style']}
	{$fonts['h6_line-height']}
	{$fonts['h6_text-decoration']}
	{$fonts['h6_text-transform']}
	{$fonts['h6_letter-spacing']}
}

/* product archive price */
.woocommerce ul.products li.product .price {
	{$fonts['h6_font-family']}
	{$fonts['h6_font-size']}
	{$fonts['h6_font-weight']}
	{$fonts['h6_font-style']}
	{$fonts['h6_line-height']}
	{$fonts['h6_text-decoration']}
	{$fonts['h6_text-transform']}
	{$fonts['h6_letter-spacing']}
}

/* single product price */
.woocommerce div.product p.price,
.woocommerce div.product span.price {
	{$fonts['h5_font-family']}
	{$fonts['h5_font-size']}
	{$fonts['h5_font-weight']}
	{$fonts['h5_font-style']}
	{$fonts['h5_line-height']}
	{$fonts['h5_text-decoration']}
	{$fonts['h5_text-transform']}
	{$fonts['h5_letter-spacing']}
}

/* single product reviews */
.woocommerce #reviews #comments ol.commentlist li .comment-text p.meta {
	{$fonts['info_font-family']}
	{$fonts['info_font-size']}
	{$fonts['info_font-weight']}
	{$fonts['info_font-style']}
	{$fonts['info_line-height']}
	{$fonts['info_text-decoration']}
	{$fonts['info_text-transform']}
	{$fonts['info_letter-spacing']}
}
.woocommerce #reviews #review_form_wrapper .comment-reply-title {
	{$fonts['h4_font-family']}
	{$fonts['h4_font-size']}
	{$fonts['h4_font-weight']}
	{$fonts['h4_font-style']}
	{$fonts['h4_line-height']}
	{$fonts['h4_text-decoration']}
	{$fonts['h4_text-transform']}
	{$fonts['h4_letter-spacing']}
}
.woocommerce #reviews #comments ol.commentlist li .comment-text p.meta strong {
	{$fonts['h6_font-family']}
	{$fonts['h6_font-size']}
	{$fonts['h6_font-weight']}
	{$fonts['h6_font-style']}
	{$fonts['h6_line-height']}
	{$fonts['h6_text-decoration']}
	{$fonts['h6_text-transform']}
	{$fonts['h6_letter-spacing']}
}

/* single product tabs */
.woocommerce div.product .woocommerce-tabs ul.tabs li a {
	{$fonts['h6_font-family']}
	{$fonts['h6_font-size']}
	{$fonts['h6_font-weight']}
	{$fonts['h6_font-style']}
	{$fonts['h6_line-height']}
	{$fonts['h6_text-decoration']}
	{$fonts['h6_text-transform']}
	{$fonts['h6_letter-spacing']}
}

/* tables */
.woocommerce table.shop_table th,
.woocommerce table.shop_table tbody th,
.woocommerce table.shop_table tfoot th {
	{$fonts['h6_font-family']}
	{$fonts['h6_font-size']}
	{$fonts['h6_font-weight']}
	{$fonts['h6_font-style']}
	{$fonts['h6_line-height']}
	{$fonts['h6_text-decoration']}
	{$fonts['h6_text-transform']}
	{$fonts['h6_letter-spacing']}
}
.woocommerce table.shop_table_responsive tr td:before,
.woocommerce-page table.shop_table_responsive tr td:before {
	{$fonts['h6_font-family']}
	{$fonts['h6_font-weight']}
}

/* buttons */
#add_payment_method .wc-proceed-to-checkout a.checkout-button,
.woocommerce-cart .wc-proceed-to-checkout a.checkout-button,
.woocommerce-checkout .wc-proceed-to-checkout a.checkout-button,

.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce #respond input#submit,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce a.button,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce button.button,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce input.button,

.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit.disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit:disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit:disabled[disabled],
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button.disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button:disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button:disabled[disabled],
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button.disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button:disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button:disabled[disabled],
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button.disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button:disabled,
.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button:disabled[disabled],
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce #respond input#submit.disabled,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce #respond input#submit:disabled,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce #respond input#submit:disabled[disabled],
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce a.button.disabled,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce a.button:disabled,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce a.button:disabled[disabled],
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce button.button.disabled, 
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce button.button:disabled,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce button.button:disabled[disabled],
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce input.button.disabled, 
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce input.button:disabled,
:where(body:not(.woocommerce-block-theme-has-button-styles)):where(:not(.edit-post-visual-editor)) .woocommerce input.button:disabled[disabled] {
	{$fonts['button_font-family']}
	{$fonts['button_font-size']}
	{$fonts['button_font-weight']}
	{$fonts['button_font-style']}
	{$fonts['button_line-height']}
	{$fonts['button_text-decoration']}
	{$fonts['button_text-transform']}
	{$fonts['button_letter-spacing']}
	{$fonts['button_padding']}
	{$fonts['button_border-radius']}
	{$fonts['button_border-width']}
	{$fonts['button_border-style']}
}

/* input */
.woocommerce form .form-row .input-text,
.woocommerce form .form-row select {
	{$fonts['input_font-family']}
	{$fonts['input_font-size']}
	{$fonts['input_font-weight']}
	{$fonts['input_font-style']}
	{$fonts['input_line-height']}
	{$fonts['input_text-decoration']}
	{$fonts['input_text-transform']}
	{$fonts['input_letter-spacing']}
	{$fonts['input_padding']}
	{$fonts['input_border-radius']}
	{$fonts['input_border-width']}
	{$fonts['input_border-style']}
}

/* WooCommerce Blocks in GB editor */
/* grid product button */
.wc-block-grid__product-add-to-cart.wp-block-button .wp-block-button__link {
	{$fonts['button_font-size']}
}
/* grid product title */
.wc-block-grid__product .wc-block-grid__product-title {
    {$fonts['h6_font-family']}
	{$fonts['h6_font-size']}
	{$fonts['h6_font-weight']}
	{$fonts['h6_font-style']}
	{$fonts['h6_line-height']}
	{$fonts['h6_text-decoration']}
	{$fonts['h6_text-transform']}
	{$fonts['h6_letter-spacing']}
}
/* grid product price */
.wc-block-grid__product .wc-block-grid__product-price {
	{$fonts['h6_font-family']}
	{$fonts['h6_font-size']}
	{$fonts['h6_font-weight']}
	{$fonts['h6_font-style']}
	{$fonts['h6_line-height']}
	{$fonts['h6_text-decoration']}
	{$fonts['h6_text-transform']}
	{$fonts['h6_letter-spacing']}
}
/* components button */
.wc-block-components-button {
	{$fonts['button_font-family']}
	{$fonts['button_font-size']}
	{$fonts['button_font-weight']}
	{$fonts['button_font-style']}
	{$fonts['button_line-height']}
	{$fonts['button_text-decoration']}
	{$fonts['button_text-transform']}
	{$fonts['button_letter-spacing']}
	{$fonts['button_border-radius']}
	{$fonts['button_border-width']}
	{$fonts['button_border-style']}
}


CSS;
		}

		return $css;
	}
}

// Load skin-specific functions
$fdir = literature_get_file_dir( 'plugins/woocommerce/woocommerce-skin.php' );
if ( ! empty( $fdir ) ) {
	require_once $fdir;
}