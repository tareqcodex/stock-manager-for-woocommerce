<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Stock_Status{
    public function __construct() {
        add_action('init', [$this, 'remove_add_to_cart_button_for_variable_product']);
        add_action('admin_init', [$this, 'manage_stock']);        
    }

    public function manage_stock() {   
        /**
         * @param  @ Get the selected data from the setting page
         * @since    1.1
         * @access   public
         */

        $stock_status = get_option('select_stock_status_option');
        $apply_to_all_products = get_option('stock_status_all_products');
        $selected_products = get_option( 'stock_status_selected_products' );
        $selected_categories = get_option( 'stock_status_selected_categories' ); 
        $is_schedule = get_option('is_schedule');
        $new_stock_status = ($stock_status === 'outofstock') ? 'outofstock' : 'instock';
        $products_to_update = [];

        if('no' === $is_schedule){

            /**
             * @param  @ Apply stock status to over the all products
             * @since    1.1
             * @access   public
             */
        
            if ($apply_to_all_products === 'yes') {
                $products = get_posts(['post_type' => 'product', 'numberposts' => -1, 'fields' => 'ids']);
                $products_to_update = $products;
            } 
            
            /**
             * @param  @ Apply Stock Status to over the only selected products 
             * @since    1.1
             * @access   public
             */

            
            if (!empty($selected_products)) {
                    $products_to_update = array_map('intval', $selected_products);
            } 

            if (!empty($products_to_update)) {
                foreach ($products_to_update as $product_id) {
                    update_post_meta($product_id, '_stock_status', $new_stock_status);
                }
            }

            /**
             * @param  @ Apply Stock Status to over the all products according to the selected categories 
             * @since    1.1
             * @access   public
             */              
            
            if (!empty($selected_categories)) {    
                $category_slugs = [];
                foreach ($selected_categories as $category_id) {
                    $term = get_term($category_id, 'product_cat');
                    if ($term && !is_wp_error($term)) {
                        $category_slugs[] = $term->slug; // get the category slugs
                    }
                }     

                $args = array(
                    'limit' => -1,
                    'status' => 'publish',
                    'return' => 'ids', // return an array of product/post IDs
                    'category' => $category_slugs,
                );
                $products_to_update = wc_get_products( $args );

                if (!empty($products_to_update)) {
                    foreach ($products_to_update as $product_id) {
                        update_post_meta($product_id, '_stock_status', $new_stock_status);
                    }
                }                    
            }             
        }
    }


    /**
	 * @param  @ Initiate the hook for removing the button, quantity and variation from variable single product page
	 * @since    1.1
	 * @access   public
	 */

     public function remove_add_to_cart_button_for_variable_product(){
        add_action( 'woocommerce_single_product_summary', [$this,'remove_add_to_cart_button'], 1, 0 );    
    }
   
    /**
	 * @param  @ Hide add to cart button, quantity and variation from variable single product page
	 * @since    1.1
	 * @access   public
	 */

    function remove_add_to_cart_button() {
        global $product;        
        if ($product->is_type('variable')) {
            $parent_id = $product->get_ID();
            $parent_product = wc_get_product($parent_id);
            if ($parent_product->get_stock_status() == 'outofstock') {    
                remove_action('woocommerce_single_variation', 'woocommerce_single_variation', 10);
                remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);  
                add_filter('woocommerce_get_price_html', [$this,'show_out_of_stock_message_beside_price'], 10, 2);      
            }
        } else{
            add_filter('woocommerce_get_stock_html', [$this,'remove_out_of_stock_badge_for_simple_products'], 10, 2);
            add_filter('woocommerce_get_price_html', [$this,'show_out_of_stock_message_beside_price'], 10, 2); 
        }       
    }

    /**
	 * @param  @ Remove the default stock out alert message from simple product page
	 * @since    1.1
	 * @access   public
	 */

    public function remove_out_of_stock_badge_for_simple_products($availability, $product) {
        if (!$product->is_in_stock() && $product->is_type('simple')) {
            return ''; // Remove the badge for simple products
        }        
    }
   
    /**
	 * @param  @ Added new Stock status message for variable and single products beside the price
	 * @since    1.1
	 * @access   public
	 */
    
    public function show_out_of_stock_message_beside_price($price, $product) {
        if (!$product->is_in_stock()) {
            $message = __('Out of stock','stock-manager');
            $price .= sprintf( ' <span class="stock out-of-stock" style="color:red">%s</span>', $message );
        }
        return $price;
    }
}