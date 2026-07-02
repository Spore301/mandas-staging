<?php
/**
 * Child-Theme functions and definitions
 */

// Load rtl.css because it is not autoloaded from the child theme
if ( ! function_exists( 'literature_child_load_rtl' ) ) {
	add_filter( 'wp_enqueue_scripts', 'literature_child_load_rtl', 3000 );
	function literature_child_load_rtl() {
		if ( is_rtl() ) {
			wp_enqueue_style( 'literature-style-rtl', get_template_directory_uri() . '/rtl.css' );
		}
	}
}

// Display product categories in the WooCommerce loops (below the title)
add_action( 'woocommerce_after_shop_loop_item_title', 'mandas_show_categories_in_loop', 13 );
if ( ! function_exists( 'mandas_show_categories_in_loop' ) ) {
	function mandas_show_categories_in_loop() {
		global $product;
		if ( ! empty( $product ) ) {
			$terms = get_the_terms( $product->get_id(), 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$links = array();
				foreach ( $terms as $term ) {
					// Check if category name has no Bengali characters (Espresso/English detection)
					$has_bengali = preg_match( '/[\x{0980}-\x{09FF}]/u', $term->name );
					$class = ! $has_bengali ? 'class="category-is-english"' : '';
					$links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '" ' . $class . '>' . esc_html( $term->name ) . '</a>';
				}
				echo '<div class="product_categories"><span class="meta-label">Category: </span>' . implode( ', ', $links ) . '</div>';
			}
		}
	}
}

// Wrap English-only product titles to style them in Cormorant Garamond
add_filter( 'the_title', 'mandas_english_title_class', 150, 2 );
if ( ! function_exists( 'mandas_english_title_class' ) ) {
	function mandas_english_title_class( $title, $post_id = 0 ) {
		if ( function_exists( 'literature_storage_get' ) 
			&& literature_storage_get( 'in_product_item' ) 
			&& get_post_type( $post_id ) == 'product' 
		) {
			// Check if title contains no Bengali characters
			$has_bengali = preg_match( '/[\x{0980}-\x{09FF}]/u', $title );
			if ( ! $has_bengali ) {
				return '<span class="title-is-english">' . $title . '</span>';
			}
		}
		return $title;
	}
}

// Display product short description in shop loop (beside categories)
add_action( 'woocommerce_after_shop_loop_item_title', 'mandas_open_meta_wrapper', 12 );
if ( ! function_exists( 'mandas_open_meta_wrapper' ) ) {
	function mandas_open_meta_wrapper() {
		echo '<div class="product-card-meta-wrap">';
	}
}

add_action( 'woocommerce_after_shop_loop_item_title', 'mandas_show_short_description', 14 );
if ( ! function_exists( 'mandas_show_short_description' ) ) {
	function mandas_show_short_description() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$short_description = $product->get_short_description();

		if ( ! empty( $short_description ) ) {
			// Check if short description contains Bengali characters
			$has_bengali = preg_match( '/[\x{0980}-\x{09FF}]/u', $short_description );
			$class = ! $has_bengali ? 'short-desc-is-english' : 'short-desc-is-bengali';
			echo '<div class="product-short-description ' . $class . '">';
			echo '<span class="meta-label">Description: </span>';
			echo apply_filters( 'woocommerce_short_description', $short_description );
			echo '</div>';
		}
	}
}

add_action( 'woocommerce_after_shop_loop_item_title', 'mandas_close_meta_wrapper', 15 );
if ( ! function_exists( 'mandas_close_meta_wrapper' ) ) {
	function mandas_close_meta_wrapper() {
		echo '</div>';
	}
}

// Add the same categories and short description layout inside the TRX Addons product carousel cards
add_action( 'trx_addons_woo_products_product_after_price', 'mandas_open_meta_wrapper', 12 );
add_action( 'trx_addons_woo_products_product_after_price', 'mandas_show_categories_in_loop', 13 );
add_action( 'trx_addons_woo_products_product_after_price', 'mandas_show_short_description', 14 );
add_action( 'trx_addons_woo_products_product_after_price', 'mandas_close_meta_wrapper', 15 );

// Override parent theme function to fix "non-numeric value encountered" warning on PHP 7.1/8.x
if ( ! function_exists( 'literature_woocommerce_add_sale_percent' ) ) {
	function literature_woocommerce_add_sale_percent( $label, $post = '', $product = '' ) {
		$percent = '';
		if ( is_object( $product ) ) {
			if ( 'variable' === $product->get_type() ) {
				$prices  = $product->get_variation_prices();
				if ( ! is_array( $prices['regular_price'] ) && ! is_array( $prices['sale_price'] ) && $prices['regular_price'] > $prices['sale_price'] ) {
					$reg_price = floatval( $prices['regular_price'] );
					$sale_price = floatval( $prices['sale_price'] );
					if ( $reg_price > 0 ) {
						$percent = round( ( $reg_price - $sale_price ) / $reg_price * 100 );
					}
				} else if ( is_array( $prices['regular_price'] ) && is_array( $prices['sale_price'] ) ) {
					$max_percent = 0;
					foreach ( $prices['sale_price'] as $id => $sale_price ) {
						if ( ! empty( $prices['regular_price'][ $id ] ) && $prices['regular_price'][ $id ] > $sale_price ) {
							$reg_price = floatval( $prices['regular_price'][ $id ] );
							$s_price = floatval( $sale_price );
							if ( $reg_price > 0 ) {
								$cur_percent = round( ( $reg_price - $s_price ) / $reg_price * 100 );
								if ( $cur_percent > $max_percent ) {
									$max_percent = $cur_percent;
								}
							}
						}
					}
					if ( $max_percent > 0 ) {
						$percent = $max_percent;
					}
				}
			} else {
				// Get prices from the product object
				$price_old = floatval( $product->get_regular_price() );
				$price_new = floatval( $product->get_sale_price() );
				// Calculate percent
				if ( $price_old > 0 && $price_old > $price_new ) {
					$percent = round( ( $price_old - $price_new ) / $price_old * 100 );
				}
			}
		}
		return ! empty( $percent )
					? apply_filters( 'literature_filter_woocommerce_sale_flash',
										'<span class="onsale">-' . esc_html( $percent ) . '%</span>',
										$percent,
										$product
									)
					: $label;
	}
}

// Inject child theme style.css inline to completely bypass browser, CDN, and plugin caching
add_action( 'wp_head', 'mandas_inline_custom_css', 999 );
function mandas_inline_custom_css() {
	$css_file = get_stylesheet_directory() . '/style.css';
	if ( file_exists( $css_file ) ) {
		echo "\n<!-- Mandas Custom Inline CSS -->\n<style id=\"mandas-inline-css\">\n";
		echo file_get_contents( $css_file );
		echo "\n</style>\n";
	}
}

// Enqueue Google Fonts (Cormorant Garamond and DM Sans)
add_action( 'wp_enqueue_scripts', 'mandas_enqueue_google_fonts', 100 );
function mandas_enqueue_google_fonts() {
	wp_enqueue_style( 'mandas-google-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap', array(), null );
}




add_action( 'woocommerce_before_cart', 'mandas_add_cart_header', 5 );
function mandas_add_cart_header() {
    echo '<h1 class="mandas-cart-title" style="font-family: \'Cormorant Garamond\', \'Ruposhi Bangla UI Uni\', serif; font-size: 2.5rem; font-weight: 700; color: #1d1b18; margin-top: 0; margin-bottom: 30px;">My Cart</h1>';
}

// WooCommerce Tabbed Login/Register Form wrapper and switcher
add_action( 'woocommerce_before_customer_login_form', 'mandas_before_login_form_wrapper', 1 );
function mandas_before_login_form_wrapper() {
	echo '<div class="mandas-auth-card show-login">';
	echo '<div class="mandas-auth-tabs">';
	echo '<button class="mandas-auth-tab active" data-tab="login">' . esc_html__( 'Login', 'woocommerce' ) . '</button>';
	echo '<button class="mandas-auth-tab" data-tab="register">' . esc_html__( 'Sign Up', 'woocommerce' ) . '</button>';
	echo '</div>';
	echo '<div class="mandas-auth-content">';
}

add_action( 'woocommerce_after_customer_login_form', 'mandas_after_login_form_wrapper', 999 );
function mandas_after_login_form_wrapper() {
	echo '</div>'; // close mandas-auth-content
	echo '</div>'; // close mandas-auth-card
}

// Client-side switcher script for tabs and welcome headers
add_action( 'wp_footer', 'mandas_auth_switcher_script' );
function mandas_auth_switcher_script() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || is_user_logged_in() ) {
		return;
	}
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		var card = document.querySelector('.mandas-auth-card');
		if (!card) return;

		var tabs = card.querySelectorAll('.mandas-auth-tab');
		var col1 = card.querySelector('#customer_login .col-1');
		var col2 = card.querySelector('#customer_login .col-2');

		// Insert Welcome Headers dynamically at the top of col-1 and col-2
		if (col1) {
			var loginHeader = document.createElement('div');
			loginHeader.className = 'mandas-auth-header login-header';
			loginHeader.innerHTML = '<span class="mandas-auth-sub">Welcome Back</span>' +
				'<h3 class="mandas-auth-title">Sign in to Mandas</h3>' +
				'<p class="mandas-auth-desc">Access your orders, saved products, and account details.</p>';
			col1.insertBefore(loginHeader, col1.firstChild);

			// Add placeholders to Login fields
			var userField = col1.querySelector('#username');
			if (userField) userField.setAttribute('placeholder', 'you@example.com');
			var passField = col1.querySelector('#password');
			if (passField) passField.setAttribute('placeholder', 'Enter your password');
		}

		if (col2) {
			var registerHeader = document.createElement('div');
			registerHeader.className = 'mandas-auth-header register-header';
			registerHeader.innerHTML = '<span class="mandas-auth-sub">Start Your Journey</span>' +
				'<h3 class="mandas-auth-title">Create an Account</h3>' +
				'<p class="mandas-auth-desc">Access your orders, manage downloads, and speed up checkout.</p>';
			col2.insertBefore(registerHeader, col2.firstChild);

			// Add placeholders to Register fields
			var regEmailField = col2.querySelector('#reg_email');
			if (regEmailField) regEmailField.setAttribute('placeholder', 'you@example.com');
		}

		// Tab switching logic
		tabs.forEach(function(tab) {
			tab.addEventListener('click', function(e) {
				e.preventDefault();
				
				// Remove active class from all tabs
				tabs.forEach(function(t) { t.classList.remove('active'); });
				
				// Add active class to current tab
				tab.classList.add('active');
				
				// Switch layout view
				var targetTab = tab.getAttribute('data-tab');
				if (targetTab === 'register') {
					card.classList.remove('show-login');
					card.classList.add('show-register');
				} else {
					card.classList.remove('show-register');
					card.classList.add('show-login');
				}
			});
		});
	});
	</script>
	<?php
}

// Dynamic Header Account Label based on User Login Status
add_filter( 'elementor/widget/render_content', 'mandas_dynamic_header_account_label', 10, 2 );
function mandas_dynamic_header_account_label( $content, $widget ) {
	if ( 'icon-box' === $widget->get_name() 
		&& strpos( $content, 'lucide-user' ) !== false 
		&& ( strpos( $content, '/my-account/' ) !== false || strpos( $content, 'my-account' ) !== false ) 
	) {
		$label_text = is_user_logged_in() ? __( 'My Account', 'woocommerce' ) : __( 'Login / Sign-up', 'woocommerce' );
		$label_html = '<span class="mandas-header-auth-label">' . esc_html( $label_text ) . '</span>';
		$content = str_replace( 'class="elementor-icon"', 'class="elementor-icon mandas-header-account-link"', $content );
		$content = str_replace( '</a>', $label_html . '</a>', $content );
	}
	return $content;
}

// Shop Layout wrappers to create a sidebar layout with WOOF filters
add_action( 'woocommerce_before_shop_loop', 'mandas_shop_layout_start', 9 );
if ( ! function_exists( 'mandas_shop_layout_start' ) ) {
	function mandas_shop_layout_start() {
		// Only run on shop page, product category page, product tag page, or product taxonomy page
		if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
			?>
			<div class="mandas-shop-layout">
				<aside class="mandas-shop-sidebar">
					<h4 class="mandas-sidebar-title" style="font-family: 'Cormorant Garamond', 'Ruposhi Bangla UI Uni', serif; font-size: 1.5rem; font-weight: 700; color: #1d1b18; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid rgba(29, 27, 24, 0.1); padding-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Filters</h4>
					<div class="mandas-shop-filters-wrap">
						<?php echo do_shortcode( '[woof]' ); ?>
					</div>
				</aside>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$(document).on('click', '.mandas-sidebar-title', function() {
							if (window.innerWidth <= 1024) {
								var $sidebar = $('.mandas-shop-sidebar');
								$sidebar.toggleClass('is-expanded');
								$('.mandas-shop-filters-wrap').slideToggle(300);
							}
						});
					});
				</script>
				<div class="mandas-shop-content">
			<?php
		}
	}
}

add_action( 'woocommerce_after_shop_loop', 'mandas_shop_layout_end', 30 );
if ( ! function_exists( 'mandas_shop_layout_end' ) ) {
	function mandas_shop_layout_end() {
		// Only run on shop page, product category page, product tag page, or product taxonomy page
		if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
			?>
				</div><!-- .mandas-shop-content -->
			</div><!-- .mandas-shop-layout -->
			<?php
		}
	}
}

// Wrap WooCommerce results count and catalog ordering in a header container
add_action( 'woocommerce_before_shop_loop', 'mandas_shop_content_header_start', 15 );
if ( ! function_exists( 'mandas_shop_content_header_start' ) ) {
	function mandas_shop_content_header_start() {
		if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
			echo '<div class="mandas-shop-content-header">';
		}
	}
}

add_action( 'woocommerce_before_shop_loop', 'mandas_shop_content_header_end', 35 );
if ( ! function_exists( 'mandas_shop_content_header_end' ) ) {
	function mandas_shop_content_header_end() {
		if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
			echo '</div>';
		}
	}
}

// Dynamic Toggle Script for Featured Categories Grid
add_action( 'wp_footer', 'mandas_featured_categories_toggle_script', 99 );
if ( ! function_exists( 'mandas_featured_categories_toggle_script' ) ) {
	function mandas_featured_categories_toggle_script() {
		?>
		<script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function() {
			var wrapper = document.querySelector('.featured-cat-img-wrapper');
			if (!wrapper) return;

			// Prevent duplicate instantiation
			if (wrapper.querySelector('.featured-cat-toggle-card')) return;

			var children = wrapper.children;
			if (children.length === 0) return;

			// Detect current language of the website to translate trigger button text
			var isBengali = document.documentElement.lang.includes('bn');
						var textMore = isBengali ? 'আরও দেখুন' : 'Show More';
			var textLess = isBengali ? 'কম দেখুন' : 'Show Less';

			// Create the toggle card element
			var toggleCard = document.createElement('div');
			toggleCard.className = 'elementor-element featured-cat-toggle-card';
			toggleCard.setAttribute('role', 'button');
			toggleCard.setAttribute('tabindex', '0');
			toggleCard.setAttribute('aria-expanded', 'false');
			
			toggleCard.innerHTML = 
				'<div class="toggle-card-content">' +
					'<div class="toggle-text-wrap">' +
						'<h3 class="toggle-card-title">' + textMore + '</h3>' +
						'<svg class="brush-stroke" viewBox="0 0 100 10" preserveAspectRatio="none" width="60" height="6">' +
							'<path d="M 0 5 C 20 2, 40 8, 60 4 C 80 1, 90 7, 100 5" stroke="#d41f22" stroke-width="3.5" fill="none" stroke-linecap="round" />' +
						'</svg>' +
					'</div>' +
					'<svg class="chevron-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">' +
						'<polyline points="6 9 12 15 18 9"></polyline>' +
					'</svg>' +
				'</div>';

			wrapper.appendChild(toggleCard);

			// Click handler
			function handleToggle() {
				var isExpanded = wrapper.classList.contains('expanded');
				if (!isExpanded) {
					wrapper.classList.add('expanded');
					toggleCard.setAttribute('aria-expanded', 'true');
					toggleCard.querySelector('.toggle-card-title').textContent = textLess;
				} else {
					wrapper.classList.remove('expanded');
					toggleCard.setAttribute('aria-expanded', 'false');
					toggleCard.querySelector('.toggle-card-title').textContent = textMore;
					
					// Smooth scroll back to parent section header so user stays aligned
					var parentSection = wrapper.parentElement;
					if (parentSection) {
						parentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				}
			}

			toggleCard.addEventListener('click', handleToggle);
			toggleCard.addEventListener('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					handleToggle();
				}
			});

			// Check layout dynamically based on window width and child items count
			function checkLayout() {
				var width = window.innerWidth;
				var limit = 7; // Desktop default (7 cols)
				if (width <= 480) {
					limit = 3; // Mobile (3 cols)
				} else if (width <= 768) {
					limit = 4; // Small Tablet (4 cols)
				} else if (width <= 1200) {
					limit = 5; // Large Tablet / Laptop (5 cols)
				}

				// The number of actual categories (excluding the toggle card itself)
				var categoriesCount = Array.prototype.filter.call(children, function(child) {
					return !child.classList.contains('featured-cat-toggle-card');
				}).length;

				if (categoriesCount <= limit) {
					toggleCard.style.display = 'none';
					wrapper.classList.add('no-collapse');
					wrapper.classList.remove('expanded');
				} else {
					toggleCard.style.display = 'flex';
					wrapper.classList.remove('no-collapse');
					if (toggleCard.getAttribute('aria-expanded') === 'true') {
						wrapper.classList.add('expanded');
					} else {
						wrapper.classList.remove('expanded');
					}
				}
			}

			// Run layout checks on load and resize
			checkLayout();
			window.addEventListener('resize', checkLayout);
		});
		</script>
		<?php
	}
}

// WooCommerce Mini Cart Quantity update AJAX handler
add_action( 'wp_ajax_mandas_update_mini_cart_quantity', 'mandas_update_mini_cart_quantity' );
add_action( 'wp_ajax_nopriv_mandas_update_mini_cart_quantity', 'mandas_update_mini_cart_quantity' );
if ( ! function_exists( 'mandas_update_mini_cart_quantity' ) ) {
	function mandas_update_mini_cart_quantity() {
		if ( ! isset( $_POST['cart_item_key'] ) || ! isset( $_POST['qty'] ) ) {
			wp_send_json_error( array( 'message' => 'Missing parameters' ) );
		}

		$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
		$qty = floatval( $_POST['qty'] );

		// Retrieve the cart
		$cart = WC()->cart->get_cart();
		if ( isset( $cart[ $cart_item_key ] ) ) {
			// Update quantity
			WC()->cart->set_quantity( $cart_item_key, $qty, true );
			
			// Re-calculate cart totals
			WC()->cart->calculate_totals();
			
			// Send refreshed fragments
			WC_AJAX::get_refreshed_fragments();
		} else {
			wp_send_json_error( array( 'message' => 'Cart item not found' ) );
		}
		wp_die();
	}
}

// Mini Cart Quantity Plus/Minus Buttons Renderer JS
add_action( 'wp_footer', 'mandas_mini_cart_quantity_script', 100 );
if ( ! function_exists( 'mandas_mini_cart_quantity_script' ) ) {
	function mandas_mini_cart_quantity_script() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Helper: Parse Bengali and English digits into standard numbers
			function parseBengaliNum(str) {
				if (!str) return 0;
				var bnDigits = {'০':'0','১':'1','২':'2','৩':'3','৪':'4','৫':'5','৬':'6','৭':'7','৮':'8','৯':'9'};
				var res = '';
				for (var i = 0; i < str.length; i++) {
					var char = str.charAt(i);
					if (bnDigits[char] !== undefined) {
						res += bnDigits[char];
					} else if (char >= '0' && char <= '9') {
						res += char;
					}
				}
				return parseInt(res, 10) || 0;
			}

			// Helper: Format English numbers back to Bengali digits if site is in Bengali
			function toBengaliNum(num) {
				var lang = (document.documentElement && document.documentElement.lang) || '';
				var isBengali = lang.indexOf('bn') !== -1;
				if (!isBengali) return num;
				var bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
				return String(num).split('').map(function(char) {
					return bnDigits[parseInt(char, 10)] || char;
				}).join('');
			}

			function initMiniCartQuantities() {
				$('.woocommerce-mini-cart-item').each(function() {
					var $item = $(this);
					// Prevent double initialization
					if ($item.find('.mini-cart-qty-wrap').length) return;

					var $removeBtn = $item.find('a.remove_from_cart_button');
					var cartKey = $removeBtn.attr('data-cart_item_key') || $removeBtn.data('cart_item_key');
					if (!cartKey) return;

					var $qtySpan = $item.find('.quantity');
					if (!$qtySpan.length) return;

					var qtyHtml = $qtySpan.html();
					// Support standard digits and Bengali unicode digit range [0-9\u09E6-\u09EF]
					var match = qtyHtml.match(/^([0-9\u09E6-\u09EF]+)\s*[×&times;]\s*(.*)$/i);
					if (match) {
						var qty = parseBengaliNum(match[1]);
						var priceHtml = match[2];

						var newHtml = `
							<div class="mini-cart-qty-wrap" data-cart-key="${cartKey}" data-current-qty="${qty}">
								<button type="button" class="mini-cart-qty-btn minus" aria-label="Decrease quantity">-</button>
								<span class="mini-cart-qty-val">${toBengaliNum(qty)}</span>
								<button type="button" class="mini-cart-qty-btn plus" aria-label="Increase quantity">+</button>
								<span class="mini-cart-price">${priceHtml}</span>
							</div>
						`;
						$qtySpan.html(newHtml);
					}
				});
				updateCartCountPill();
			}

			function updateCartCountPill() {
				var totalQty = 0;
				$('.woocommerce-mini-cart').first().find('.woocommerce-mini-cart-item').each(function() {
					var $item = $(this);
					var $qtyWrap = $item.find('.mini-cart-qty-wrap');
					if ($qtyWrap.length) {
						totalQty += parseInt($qtyWrap.attr('data-current-qty') || 0, 10);
					} else {
						var $qtySpan = $item.find('.quantity');
						if ($qtySpan.length) {
							var qtyHtml = $qtySpan.html();
							var match = qtyHtml.match(/^([0-9\u09E6-\u09EF]+)/);
							if (match) {
								totalQty += parseBengaliNum(match[1]);
							}
						}
					}
				});
				$('.sc_layouts_cart_items_short').text(toBengaliNum(totalQty));
			}

			function initCartPanelCloseButton() {
				var $header = $('.sc_layouts_cart_panel_header');
				if ($header.length && !$header.find('.sc_layouts_cart_panel_close_btn').length) {
					var $title = $header.find('.sc_layouts_cart_panel_title');
					var $closeBtn = $(`
						<button type="button" class="sc_layouts_cart_panel_close_btn" aria-label="Close cart">
							<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
								<line x1="18" y1="6" x2="6" y2="18"></line>
								<line x1="6" y1="6" x2="18" y2="18"></line>
							</svg>
						</button>
					`);
					
					if ($title.length) {
						$title.append($closeBtn);
					} else {
						$header.append($closeBtn);
					}
				}
			}

			// Initialize on load
			initMiniCartQuantities();
			initCartPanelCloseButton();
			updateCartCountPill();
			setTimeout(updateCartCountPill, 150);

			// Re-initialize on WooCommerce fragment refresh
			$(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
				initMiniCartQuantities();
				initCartPanelCloseButton();
				updateCartCountPill();
				setTimeout(updateCartCountPill, 150);
			});

			// Handle Close Button Click
			$(document.body).on('click', '.sc_layouts_cart_panel_close_btn', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var $nativeClose = $('.sc_layouts_panel_close, .sc_layouts_cart_panel_close, .trx_addons_panel_close, .sc_layouts_panel_close_icon');
				if ($nativeClose.length) {
					$nativeClose.first().click();
				} else {
					$('.sc_layouts_panel, .sc_layouts_cart_panel, .trx_addons_panel_opened').removeClass('opened trx_addons_panel_opened');
					$('html').removeClass('trx_addons_panel_opened');
				}
			});

			// Handle button clicks
			$(document.body).on('click', '.mini-cart-qty-btn', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var $btn = $(this);
				var $wrap = $btn.closest('.mini-cart-qty-wrap');
				var cartKey = $wrap.data('cart-key');
				var currentQty = parseInt($wrap.attr('data-current-qty'), 10);
				var isPlus = $btn.hasClass('plus');
				var newQty = isPlus ? currentQty + 1 : currentQty - 1;

				if (newQty < 0) return;

				// Show loading state
				$wrap.css('opacity', '0.5');

				$.ajax({
					type: 'POST',
					url: typeof wc_cart_fragments_params !== 'undefined' ? wc_cart_fragments_params.ajax_url : '/wp-admin/admin-ajax.php',
					data: {
						action: 'mandas_update_mini_cart_quantity',
						cart_item_key: cartKey,
						qty: newQty
					},
					success: function(response) {
						if (response && response.fragments) {
							var fragments = response.fragments;
							$.each(fragments, function(key, value) {
								$(key).replaceWith(value);
							});
							$(document.body).trigger('wc_fragments_refreshed');
						} else {
							$wrap.css('opacity', '1');
						}
					},
					error: function() {
						$wrap.css('opacity', '1');
					}
				});
			});
		});
		</script>
		<?php
	}
}

// Force child theme search template for search queries to bypass Elementor/parent theme overrides
add_filter( 'template_include', 'mandas_force_search_template', 99999 );
function mandas_force_search_template( $template ) {
	if ( is_search() ) {
		$search_template = get_stylesheet_directory() . '/search.php';
		if ( file_exists( $search_template ) ) {
			return $search_template;
		}
	}
	return $template;
}




