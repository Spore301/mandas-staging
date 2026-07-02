<?php
/**
 * The template for displaying search results pages
 *
 * @package LITERATURE-CHILD
 */

get_header();

// Sureshot Warning Fix: Extract posts locally and clear the main query arrays 
// to prevent WordPress core and widgets from throwing Undefined array key warnings.
global $wp_query;
$all_results = ! empty( $wp_query->posts ) && is_array( $wp_query->posts ) ? $wp_query->posts : array();

// Clean query variables to prevent offset errors
$wp_query->posts = array();
$wp_query->post_count = 0;
$wp_query->current_post = -1;

$search_query = get_search_query();

// Filter search results to keep only products/books
$products = array();
foreach ( $all_results as $post_item ) {
	if ( is_object( $post_item ) && 'product' === $post_item->post_type ) {
		$products[] = $post_item;
	}
}

$total_results = count( $products );
$is_bengali = strpos( get_locale(), 'bn' ) !== false;

// Translate numbers to Bengali digits if site is in Bengali
if ( $is_bengali ) {
	$bn_digits = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );
	$total_results_str = '';
	foreach ( str_split( (string) $total_results ) as $char ) {
		$total_results_str .= isset( $bn_digits[ (int) $char ] ) ? $bn_digits[ (int) $char ] : $char;
	}
	$meta_text = sprintf( __( 'মোট %s টি ফলাফল পাওয়া গেছে', 'literature' ), $total_results_str );
} else {
	$meta_text = sprintf( _n( '%s result found', '%s results found', $total_results, 'literature' ), number_format_i18n( $total_results ) );
}
?>
<div class="content_wrap mandas-search-page-wrap">
	<main class="content">
		<!-- Search Page Header -->
		<header class="page_header_wrap mandas-search-header">
			<span class="search-subtitle"><?php esc_html_e( 'Search Results for', 'literature' ); ?></span>
			<h1 class="page_title mandas-search-title">
				&ldquo;<?php echo esc_html( $search_query ); ?>&rdquo;
			</h1>
			<div class="search-results-meta">
				<?php echo esc_html( $meta_text ); ?>
			</div>
		</header>

		<div class="mandas-search-content">
			<?php
			if ( ! empty( $products ) ) {
				echo '<div class="mandas-search-section products-found">';
				echo '<div class="woocommerce columns-4">';
				woocommerce_product_loop_start();
				foreach ( $products as $product_post ) {
					global $post;
					$post = $product_post;
					setup_postdata( $post );
					wc_get_template_part( 'content', 'product' );
				}
				wp_reset_postdata();
				woocommerce_product_loop_end();
				echo '</div></div>';
			} else {
				// No results found
				?>
				<div class="mandas-no-results">
					<div class="no-results-icon">
						<svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
							<line x1="8" y1="11" x2="14" y2="11"></line>
						</svg>
					</div>
					<h3 class="no-results-title"><?php esc_html_e( 'No Results Found', 'literature' ); ?></h3>
					<p class="no-results-desc">
						<?php esc_html_e( 'We couldn\'t find any products matching your search term. Please try another query or search for a different topic.', 'literature' ); ?>
					</p>
					<div class="no-results-search-form">
						<?php get_search_form(); ?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</main>
</div>
<?php
get_footer();
