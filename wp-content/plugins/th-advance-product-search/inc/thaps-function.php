<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TH_Advancde_Product_Search_Functions' ) ):

	class TH_Advancde_Product_Search_Functions {

		/**
		 * Member Variable
		 *
		 * @var object instance
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {

				self::$instance = new self();
			}

			return self::$instance;

		}

        /**
		 * Constructor
		 */
		public function __construct(){

          add_filter( 'posts_search',   [ $this, 'modify_search_sql' ], 501, 2 );
           add_action( 'wp_ajax_thaps_ajax_get_search_value',array( $this, 'thaps_ajax_get_search_value' ));
           add_action( 'wp_ajax_nopriv_thaps_ajax_get_search_value',array( $this, 'thaps_ajax_get_search_value' ));


		}

 /* --------------------------------------------------------------------- */
	/*  FUZZY + SYNONYMS EXPANSION LOGIC                                     */
	/* --------------------------------------------------------------------- */
	private function expand_synonyms( $term ) {
		$raw = th_advance_product_search()->get_option( 'tapsp_synonym_list' );
		$dictionary = [];

		if ( ! empty( $raw ) ) {
			$groups = array_filter( array_map( 'trim', explode( '|', $raw ) ) );
			foreach ( $groups as $group ) {
				$words = array_filter( array_map( 'trim', explode( ',', $group ) ) );
				foreach ( $words as $word ) {
					$dictionary[ strtolower( $word ) ] = $words;
				}
			}
		}

		$synonyms = $dictionary[ strtolower( $term ) ] ?? [];

		// Apply fuzzy matching if enabled
		if ( th_advance_product_search()->get_option( 'tapsp_enable_fuzzy' ) ) {
			$level = (int) th_advance_product_search()->get_option( 'tapsp_fuzzy_level' );
			foreach ( array_keys( $dictionary ) as $key ) {
				similar_text( strtolower( $term ), $key, $percent );
				if ( $percent >= $level && $key !== strtolower( $term ) ) {
					$synonyms = array_merge( $synonyms, $dictionary[ $key ] );
				}
			}
		}

		return array_unique( array_merge( [ $term ], $synonyms ) );
	}
		 
 /* --------------------------------------------------------------------- */
	/*  1. UNIVERSAL SEARCH SQL (Fuzzy + Synonyms + Index + All Post Types)  */
	/* --------------------------------------------------------------------- */
	public function modify_search_sql( $search, $wp_query ) {
		global $wpdb;

		if ( empty( $search ) || empty( $wp_query->query_vars['s'] ) ) {
			return $search;
		}

		$start_time = microtime(true);
		$q         = $wp_query->query_vars;
		$term      = sanitize_text_field( $q['s'] );
		$post_type = $q['post_type'] ?? 'any';

		// 🔹 Expand synonyms + fuzzy
			$search_terms = preg_split(
			    '/\s+/u',
			    trim( $term )
			);

			$search_terms = array_filter( $search_terms );

		// 🔹 Indexed table first
		// 🔹 Indexed table only for products
		// $indexed = false;
		// if ( $post_type === 'product' ) {
		// $indexed = $this->indexed_ids( $term );
		// }
		
		// if ( $indexed ) {
		// 	$ids = implode( ',', array_map( 'intval', $indexed ) );
		// 	$sql  = " AND ({$wpdb->posts}.ID IN ($ids)) ";
		// 	if ( ! is_user_logged_in() ) {
		// 		$sql .= " AND ({$wpdb->posts}.post_password = '') ";
		// 	}
		// 	//error_log("⚡ Indexed Search used for: {$term}");
		// 	return $sql;
		// }

		// // 🔹 Fallback fuzzy search
		// $clauses = [];
		$indexed = false;
		// if ( $post_type === 'product' ) {
		// $indexed = $this->indexed_ids( $term );
		// }

		$clauses = [];

		if ( $indexed ) {
		$clauses[] = "({$wpdb->posts}.ID IN (" . implode(',', $indexed) . "))";
		}
		foreach ( $search_terms as $like_term ) {
			$like = "%" . $wpdb->esc_like( $like_term ) . "%";

			$clauses[] = $wpdb->prepare(
				"({$wpdb->posts}.post_title LIKE %s)",
				$like
			);

			
			if ( th_advance_product_search()->get_option( 'tapsp_search-in-description' ) ) {
				$clauses[] = $wpdb->prepare(
					"({$wpdb->posts}.post_content LIKE %s)",
					$like
				);
			}
			
			if ( th_advance_product_search()->get_option( 'tapsp_search-in-short-description' ) ) {
				$clauses[] = $wpdb->prepare(
					"({$wpdb->posts}.post_excerpt LIKE %s)",
					$like
				);
			}

			// SKU (product only)
			if ( $post_type === 'product' && th_advance_product_search()->get_option( 'tapsp_search_in_product_sku' ) ) {
				$ids = $this->sku_ids( $like_term );
				if ( $ids ) $clauses[] = "({$wpdb->posts}.ID IN (" . implode( ',', $ids ) . "))";
			}

			// Category / tag
			if ( th_advance_product_search()->get_option( 'tapsp_search-in-category' ) ) {
				$tax = $post_type === 'product' ? 'product_cat' : 'category';
				$ids = $this->term_ids( [ $tax ], $like_term );
				if ( $ids ) $clauses[] = "({$wpdb->posts}.ID IN (" . implode( ',', $ids ) . "))";
			}
			if ( th_advance_product_search()->get_option( 'tapsp_search-in-tag' ) ) {
				$tax = $post_type === 'product' ? 'product_tag' : 'post_tag';
				$ids = $this->term_ids( [ $tax ], $like_term );
				if ( $ids ) $clauses[] = "({$wpdb->posts}.ID IN (" . implode( ',', $ids ) . "))";
			}

			// Attributes (product)
			if ( $post_type === 'product' && th_advance_product_search()->get_option( 'tapsp_search-in-attribute' ) ) {
				$ids = $this->attribute_ids( $like_term );
				if ( $ids ) $clauses[] = "({$wpdb->posts}.ID IN (" . implode( ',', $ids ) . "))";
			}

			/* ✅ BRAND SEARCH */
				if ( $post_type === 'product' && th_advance_product_search()->get_option( 'tapsp_search-in-brand' ) ) {
				$ids = $this->term_ids( [ 'product_brand', 'pa_brand' ], $like_term );
				if ( $ids ) $clauses[] = "({$wpdb->posts}.ID IN (" . implode( ',', $ids ) . "))";
				}

				// Custom fields
			if ( th_advance_product_search()->get_option( 'tapsp_search_in_custom_fld' ) ) {
				$ids = $this->custom_field_ids( $like_term, $post_type );
				if ( $ids ) $clauses[] = "({$wpdb->posts}.ID IN (" . implode( ',', $ids ) . "))";
			}

			
		}

		$search = $clauses ? ' AND (' . implode( ' OR ', array_unique( $clauses ) ) . ')' : '';
		if ( ! is_user_logged_in() ) {
			$search .= " AND ({$wpdb->posts}.post_password = '') ";
		}

		$execution_time = round(microtime(true) - $start_time, 4);
		//error_log("✅ TAPSP Search → {$term} | Type: {$post_type} | Clauses: " . count($clauses) . " | {$execution_time}s");

		return $search;
	}

	/** Generic taxonomy (cat / tag) */
	private function term_ids( array $taxonomies, $term ) {
		global $wpdb;
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		$placeholders = implode( ',', array_fill( 0, count( $taxonomies ), '%s' ) );
		$sql = $wpdb->prepare(
			"SELECT DISTINCT p.ID
			 FROM {$wpdb->posts} p
			 JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			 JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			 JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
			 WHERE tt.taxonomy IN ($placeholders) AND t.name LIKE %s",
			array_merge( $taxonomies, [ $like ] )
		);
		return array_map( 'intval', $wpdb->get_col( $sql ) );
	}

	/** Product attributes (pa_*) */
	private function attribute_ids( $term ) {
		$taxes = $this->attribute_taxonomies();
		return $taxes ? $this->term_ids( $taxes, $term ) : [];
	}

	/** Custom meta fields */
	private function custom_field_ids( $term, $post_type ) {
		$keys = array_filter( array_map( 'trim', explode( ',', th_advance_product_search()->get_option( 'tapsp_search_in_custom_fld' ) ) ) );
		if ( ! $keys ) { return []; }

		global $wpdb;
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		$placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );
		$sql = $wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key IN ($placeholders) AND meta_value LIKE %s",
			array_merge( $keys, [ $like ] )
		);
		return array_map( 'intval', $wpdb->get_col( $sql ) );
	}

	/** Generic meta lookup */
	private function meta_ids( $key, $value, $post_type ) {
		global $wpdb;
		$like = '%' . $wpdb->esc_like( $value ) . '%';
		return array_map( 'intval', $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND meta_value LIKE %s",
				$key, $like
			)
		) );
	}

		/** SKU → product IDs (incl. variation → parent) */
	private function sku_ids( $term ) {
		$ids = $this->meta_ids( '_sku', $term, 'product' );
		$var = $this->meta_ids( '_sku', $term, 'product_variation' );
		foreach ( $var as $v ) {
			$p = wp_get_post_parent_id( $v );
			if ( $p ) { $ids[] = $p; }
		}
		return array_unique( $ids );
	}

	 /**
     * Get tax query
     *
     * return array
     */
     public function getTaxQuery(){

        $product_visibility_term_ids = wc_get_product_visibility_term_ids();

        $tax_query = array(

            'relation' => 'AND',
        );

        $tax_query[] = array(

            'taxonomy' => 'product_visibility',
            'field'    => 'term_taxonomy_id',
            'terms'    => $product_visibility_term_ids['exclude-from-search'],
            'operator' => 'NOT IN',
        );

        // Exclude out of stock products from suggestions

        if (get_option('woocommerce_hide_out_of_stock_items')=='yes') {

            $tax_query[] = array(

                'taxonomy' => 'product_visibility',
                'field'    => 'term_taxonomy_id',
                'terms'    => $product_visibility_term_ids['outofstock'],
                'operator' => 'NOT IN',
            );
        }

        return $tax_query;

    }

	/**************/
	//Heading Show
	/**************/
	public function thaps_show_heading($heading_label){

	      return  array(
	                    'value'  => $heading_label,
	                    'type'   => 'heading',
	                );
	}

	/**************/
	//No Reult Show
	/**************/
	public function thaps_show_no_result($no_reult_label){

	      return  array(
	                   'value'  => $no_reult_label,
	                   'type'   => 'no-result',
	                );
	}

    /**************/
	//Get image
	/**************/
	public function thaps_getImages_src( $id, $size, $enble){
		$src = '';

       if($enble == true){

          $thumbnail_id = get_term_meta($id, 'thumbnail_id', true ); 

          $imageSrc = wp_get_attachment_image_src( $thumbnail_id, $size );

				    if ( is_array( $imageSrc ) && !empty($imageSrc[0]) ){

				       $src = $imageSrc[0];
		  }

		  return $src;

		}else{

			return $src;
		}


	}
   
	/**********************/
	//Get Product Category
	/*********************/
	public function thaps_ajax_product_getCategories( $keyword, $limit, $img_enable){

	        $results = array();

	        $args = array(

	            'taxonomy' => 'product_cat',
	        );

	        $productCategories = get_terms( 'product_cat', $args );
	        $keywordUnslashed = wp_unslash( $keyword );
	  
	        $i = 0;

	        foreach ( $productCategories as $cat ) {

	            if ( $i < $limit ) {

	                $catName = html_entity_decode( $cat->name );

	                $pos = strpos( mb_strtolower( $catName ), mb_strtolower( $keywordUnslashed ) );
				    
	                if ( $pos !== false ) {
	                    $results[$i] = array(
	                        'term_id'     => $cat->term_id,
	                        'taxonomy'    => 'product_cat',
	                        'value'       => $catName,
	                        'url'         => get_term_link( $cat, 'product_cat' ),
	                        'cat_img'     => $this->thaps_getImages_src($cat->term_id,'woocommerce_thumbnail', $img_enable),
	                        'type'        => 'taxonomy-product-cat',
	                    );
	                    $i++;

	                }
	            
	            }
	        
	        }

	        return $results;
	}

	/**********************/
	//Get Post Category
	/*********************/
	public function thaps_ajax_post_getCategories( $keyword, $limit){

	        $results = array();

	        $args = array(

	            'taxonomy' => 'category',
	        );

	        $productCategories = get_terms( 'category', $args );

	        $keywordUnslashed = wp_unslash( $keyword );
	  
	        $i = 0;

	        foreach ( $productCategories as $cat ) {

	            if ( $i < $limit ) {

	                $catName = html_entity_decode( $cat->name );

	                $pos = strpos( mb_strtolower( $catName ), mb_strtolower( $keywordUnslashed ) );

	                if ( $pos !== false ) {
	                    $results[$i] = array(
	                        'term_id'     => $cat->term_id,
	                        'taxonomy'    => 'category',
	                        'value'       => $catName,
	                        'url'         => get_term_link( $cat, 'category' ),
	                        'type'        => 'taxonomy-post-cat',
	                    );

	                    $i++;
	                }
	            
	            }
	        
	        }

	        return $results;
	}

		/*************/
		//Show More
		/*************/
		public function thaps_show_more( $count, $more_reult_label, $match, $select_srch_type){
		            
		                $moreproduct = array(
		                    'id'    => 'more-result',
		                    'value' => '',
		                    'text'  => $more_reult_label,
		                    'total' => $count,
		                    'type'  => 'more_item',
		                );

		                if($select_srch_type == 'product_srch'){

		                   $moreproduct['url'] = add_query_arg( array(
		                    's'         => $match,
		                    'post_type' => 'product',
		                ), home_url() );

		                }elseif($select_srch_type == 'post_srch'){

		                     $moreproduct['url'] = add_query_arg( array(
		                    's'         => $match,
		                    'post_type' => 'post',
		                ), home_url() );

		                }elseif($select_srch_type == 'page_srch'){

		                    $moreproduct['url'] = add_query_arg( array(
		                    's'         => $match,
		                    'post_type' => 'page',
		                ), home_url() );

		                }

		                return $moreproduct; 
		         

		}


    /*****************/		
    // Excerpt Length
    /****************/   
	public function thaps_excerpt_shw( $id , $length){

		$excerpt = wp_strip_all_tags( get_the_excerpt($id) );

		if ( strlen($excerpt) <= $length ) {

			return $excerpt;

		} else {

			$excerpte = substr($excerpt, 0, $length);
			$result   = substr($excerpte, 0, strrpos($excerpte, ' '));
			return $result . ' ...';

		}

	}


	/************************/
	// Get Product id by sku
	/************************/
	public function get_product_sku($skus){

		    global $wpdb; 

		    $return = array();

		     foreach ($skus as $sku){

		         if (empty($sku)) {
		             continue;
		         }

		         $return[] = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
		     }

		     return $return;
		}

	/************************/
	// Get Array To String
	/************************/
	public function to_convert_array($string){  

		if($string !==''){ 

		$toarray = explode(', ', $string);

		return $toarray;

		 }

	}


	/*************************/
    // search result function
	/*************************/
		public function thaps_ajax_get_search_value(){

        check_ajax_referer( 'th_advance_product_search', 'nonce' );

        //setting value
        $select_srch_type = esc_html(th_advance_product_search()->get_option( 'select_srch_type' ));
    
        $limit =  esc_html(th_advance_product_search()->get_option( 'result_length' ));

        $no_reult_label =  esc_html(th_advance_product_search()->get_option( 'no_reult_label' ));
        $more_reult_label =  esc_html(th_advance_product_search()->get_option( 'more_reult_label' ));

        $enable_group_heading =  esc_html(th_advance_product_search()->get_option( 'enable_group_heading' ));

        $enable_product_image =  esc_html(th_advance_product_search()->get_option( 'enable_product_image' ));

        $enable_product_price =  esc_html(th_advance_product_search()->get_option( 'enable_product_price' ));

        $enable_product_desc =  esc_html(th_advance_product_search()->get_option( 'enable_product_desc' ));

        $enable_product_sku =  esc_html(th_advance_product_search()->get_option( 'enable_product_sku' ));


        $show_category_in =  esc_html(th_advance_product_search()->get_option( 'show_category_in' ));

        $enable_cat_image =  esc_html(th_advance_product_search()->get_option( 'enable_cat_image' ));


        $category_hd = __('Category','th-advance-product-search');

        $product_hd  = __('Product','th-advance-product-search');
        
        $product_exclude = esc_html(th_advance_product_search()->get_option( 'exclude_product_sku' ));
        
        $exp_length = esc_html(th_advance_product_search()->get_option( 'desc_excpt_length' ));

        // Post

        $post_hd  = __('Post','th-advance-product-search');

        $enable_post_image =  esc_html(th_advance_product_search()->get_option( 'enable_post_image' ));

        $enable_post_desc =  esc_html(th_advance_product_search()->get_option( 'enable_post_desc' ));

        // Page

        $page_hd  = __('Pages','th-advance-product-search');

        $enable_page_image =  esc_html(th_advance_product_search()->get_option( 'enable_page_image' ));

        $enable_page_desc =  esc_html(th_advance_product_search()->get_option( 'enable_page_desc' ));

        $highlight_sale     = esc_html( th_advance_product_search()->get_option( 'tapsp_highlight-sale' ) );
		$highlight_featured = esc_html( th_advance_product_search()->get_option( 'tapsp_highlight-featured' ) );
		$stock_availability = esc_html( th_advance_product_search()->get_option( 'tapsp_stock-availablity' ) );

        /*********************/
        //fetch product result
        /*********************/
         if (isset($_REQUEST['match']) && $_REQUEST['match'] != ''){

            $match_ = sanitize_text_field($_REQUEST['match']);

            if ($select_srch_type=='product_srch'){ 
              $args = array(
                'posts_per_page'      => -1,
                'post_type'           => 'product',
                'post_status'         => array('publish'),
                'ignore_sticky_posts' => 1,
                'order'               => 'DESC',
                's'                   => $match_,
             ); 

             
             $args['tax_query'] = $this->getTaxQuery();

             if( $product_exclude !==''){

                 $exclude_array = $this->to_convert_array($product_exclude);

                 $args['post__not_in'] = $this->get_product_sku($exclude_array);
             }
             
          
             $results = new WP_Query($args);

             $count = ( isset( $results->posts ) ? count( $results->posts ) : 0 );

             $items = array();

             // category show 

             if($show_category_in == true){

                if($enable_group_heading == true){

                    $items['suggestions'][] = $this->thaps_show_heading($category_hd);

               }

               $categories = $this->thaps_ajax_product_getCategories( $match_, $limit, $enable_cat_image );

               if(!empty($categories)){

               foreach ( $categories as $result ){

                    $items['suggestions'][] = $result;
                }

              }else{

                    $items['suggestions'][] = $this->thaps_show_no_result($no_reult_label);
                }
             
              }


              if($enable_group_heading == true){

                  $items['suggestions'][] = $this->thaps_show_heading($product_hd);
              }

              if (!empty($results->posts)){

                 

              foreach (array_slice($results->posts,0,$limit) as $result){

                $product = wc_get_product($result->ID);

                $r = array(
                  'value'   => $result->post_title,
                  'title'   => $result->post_title,
                  'id'      => $result->ID,
                  'url'     => get_permalink($result->ID), 
                  'type'    => 'product', 
                );

                if ( $enable_product_image == true) {

                        $r['imgsrc'] = wp_get_attachment_url($product->get_image_id(), 'woocommerce_thumbnail'); 

                }
                if ( $enable_product_price == true) {

                        $r['price'] = $product->get_price_html();

                }
                if ( $enable_product_sku == true) {

                        $r['sku'] = $product->get_sku();

                }

                if ( $highlight_sale == true ) {
				    $r['sale'] = $product->is_on_sale();
				}

				if ( $highlight_featured == true ) {
				    $r['featured'] = $product->is_featured();
				}

				if ( $stock_availability == true ) {
				    $r['stock'] = $product->get_stock_quantity();
				}

                
                if ( $enable_product_desc == true) {

                        $r['desc'] = $this->thaps_excerpt_shw($result->ID , $exp_length);

                }

                $items['suggestions'][] = $r;

              }

                if($limit < $count){

                    $items['suggestions'][] = $this->thaps_show_more($count, $more_reult_label, $match_, $select_srch_type);

                }
                 
               
             
            }else{

                $items['suggestions'][] = $this->thaps_show_no_result($no_reult_label);
            }

        //search type product close 

        // Start Post Search  
         
        }elseif($select_srch_type=='post_srch'){

          $results = new WP_Query(
	            array(
	              'post_type'     => 'post',
	              'post_status'   => array('publish'),
	              'nopaging'      => true,
	              'posts_per_page' => 100,
	              's'             => $match_,
	             )
           );  

             $count = ( isset( $results->posts ) ? count( $results->posts ) : 0 );

             $items = array();   

             // category show 
             if($show_category_in == true){

                if($enable_group_heading == true){

                  $items['suggestions'][] = $this->thaps_show_heading($category_hd);

               }

               $categories = $this->thaps_ajax_post_getCategories( $match_, $limit );

               if(!empty($categories)){

               foreach ( $categories as $result ){

                    $items['suggestions'][] = $result;
                }

              }else{

                    $items['suggestions'][] = $this->thaps_show_no_result($no_reult_label);

                }
             
              }   

             if($enable_group_heading == true){   

             $items['suggestions'][] = $this->thaps_show_heading($post_hd);

             }    

             if (!empty($results->posts)){

             

             foreach (array_slice($results->posts,0,$limit) as $result){

                $r = array(
                  'value'   => $result->post_title,
                  'title'   => $result->post_title,
                  'id'      => $result->ID,
                  'url'     => get_permalink($result->ID), 
                  'type'    => 'post', 
                ); 

              if ( $enable_post_image == true) {

               $post_imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($result->ID),'thaps-thumb-img');

                if ( is_array( $post_imgsrc ) && ! empty( $post_imgsrc[0] ) ) {

                    $src = $post_imgsrc[0];

                    $r['imgsrc'] = $src;
                 }

                }
              if ( $enable_post_desc == true) {  

                $r['desc'] = $this->thaps_excerpt_shw($result->ID , $exp_length);

              }

                $items['suggestions'][] = $r;
                
               
             }
             if($limit < $count){
                    $items['suggestions'][] = $this->thaps_show_more($count, $more_reult_label, $match_, $select_srch_type);
                }

             }else{

                $items['suggestions'][] = $this->thaps_show_no_result($no_reult_label);
             }

          //search type product close 

          // Start Page Search

         }elseif($select_srch_type=='page_srch'){

             $results = new WP_Query(
	             	array(
	              'post_type'     => 'page',
	              'post_status'   => array('publish'),
	              'nopaging'      => true,
	              'posts_per_page' => 100,
	              's'             => $match_,
	             )
             );  


             $count = ( isset( $results->posts ) ? count( $results->posts ) : 0 );

             $items = array();   

                   
             if (!empty($results->posts)){

             if($enable_group_heading == true){   

               $items['suggestions'][] = $this->thaps_show_heading($page_hd);

             }
             foreach (array_slice($results->posts,0,$limit) as $result){

                $r = array(
                  'value'   => $result->post_title,
                  'title'   => $result->post_title,
                  'id'      => $result->ID,
                  'url'     => get_permalink($result->ID), 
                  'type'    => 'page', 
                ); 

              if ( $enable_page_image == true) { 

               $post_imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($result->ID),'thaps-thumb-img');

                if ( is_array( $post_imgsrc ) && ! empty( $post_imgsrc[0] ) ) {

                    $src = $post_imgsrc[0];

                    $r['imgsrc'] = $src;

                }

               }

               if ( $enable_page_desc == true) {  

                $r['desc'] = $this->thaps_excerpt_shw($result->ID , $exp_length);

               }


                $items['suggestions'][] = $r;
                
               
             }

             if($limit < $count){

                    $items['suggestions'][] = $this->thaps_show_more($count, $more_reult_label, $match_, $select_srch_type);
                }

             }else{

                $items['suggestions'][] = $this->thaps_show_no_result($no_reult_label);
             }


          //search type product close 
         }
            
        echo json_encode($items);

            die();
         }
    }

	

	}
endif;	

TH_Advancde_Product_Search_Functions::get_instance();