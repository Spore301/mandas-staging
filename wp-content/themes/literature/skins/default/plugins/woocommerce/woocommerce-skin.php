<?php
/* WooCommerce skin-specific functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 3 - add/remove Theme Options elements

if ( ! function_exists( 'literature_woocommerce_skin_theme_setup3' ) ) {
	add_action( 'after_setup_theme', 'literature_woocommerce_skin_theme_setup3', 3 );
	function literature_woocommerce_skin_theme_setup3() {
		if ( literature_exists_woocommerce() ) {
			// Panel 'Shop' with skin-specific options
			literature_storage_set_array_after( 'options', 'shop_single', literature_options_get_list_cpt_options_body( 'shop', esc_html__( 'Product', 'literature' ), 'single' ) );
			// Hide 'shop_mode'
			literature_storage_set_array2( 'options', 'shop_mode', 'type', 'hidden' );
			// Hide 'single_product_gallery_thumbs'
			literature_storage_set_array2( 'options', 'single_product_gallery_thumbs', 'type', 'hidden' );
			// Hide 'shop_buttons'
			literature_storage_set_array2( 'options', 'shop_hover', 'std', 'none' );
			literature_storage_set_array2( 'options', 'shop_hover', 'type', 'hidden' );
			// Number of related products by default
			literature_storage_set_array2( 'options', 'related_posts_shop', 'std', 4);
			literature_storage_set_array2( 'options', 'related_columns_shop', 'std', 4);
		}
	}
}


// Remove\Register Action\filters
if ( ! function_exists( 'literature_woocommerce_skin_woocommerce_remove_action' ) ) {
	add_action( 'init', 'literature_woocommerce_skin_woocommerce_remove_action', 11 );
	function literature_woocommerce_skin_woocommerce_remove_action() {
		if ( literature_exists_woocommerce() ) {
			add_filter( 'literature_filter_woocommerce_sale_flash', 'literature_change_woocommerce_sale_flash', 10, 3 );
		}
	}
}


// Show/Hide product's tags before the title
if ( ! function_exists( 'literature_woocommerce_skin_show_title' ) ) {
	add_filter( 'literature_filter_show_woocommerce_title', 'literature_woocommerce_skin_show_title' );
	function literature_woocommerce_skin_show_title() {
		return false;
	}
}


// Add label "UP TO"
if ( ! function_exists( 'literature_change_woocommerce_sale_flash' ) ) {
	function literature_change_woocommerce_sale_flash($new_sale, $percent, $product) {
		if( 'variable' === $product->get_type() ){
			$new_sale = '<span class="onsale"><span class="onsale_up">'. esc_html__('Up to', 'literature') .'</span> - '. esc_html( $percent ) . '%</span>';
		}
		return $new_sale;
	}
}

// Image width for thumbnails gallery
if ( ! function_exists( 'literature_filter_woocommerce_skin_theme_support' ) ) {
	add_filter( 'literature_filter_woocommerce_theme_support', 'literature_filter_woocommerce_skin_theme_support' );
	function literature_filter_woocommerce_skin_theme_support( $arr ) {
		$arr['gallery_thumbnail_image_width'] = 300;
		return $arr;
	}
}