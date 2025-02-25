<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Schedule_Stock_Status{
    /**
	 * @param  @ If time is empty or not selected then it will use the default time (23:49) of the selected date
	 * @since    1.1
	 * @access   protected
	 */

    protected $default_time = "23:59";

    /**
	 * @param  @ intialize the hooks
	 * @since    1.1
	 * @access   public
	 */

    public function __construct() {        
        add_action('admin_init', [$this, 'schedule_manage_stock_status']);     
        // Scheduled event to update stock status
        add_action('scheduled_stock_status_update', [$this, 'update_stock_status']);   
    }

    /**
	 * @param  @ Update the product status according to schedule date and time
	 * @since    1.1
	 * @access   public
	 */

    public function schedule_manage_stock_status() {  
        
        $date = get_option('stock_change_date');
        $time = get_option('stock_change_time');
         // Default time to 23:59 if not provided
        if (empty($time)) {
            $time = $this->default_time;
        }

        if (!empty($date) && !empty($time)) {
            $scheduled_time = strtotime("$date $time");

            if ($scheduled_time > time()) {
                // Clear any previous scheduled event
                wp_clear_scheduled_hook('scheduled_stock_status_update');

                // Schedule the stock update
                wp_schedule_single_event($scheduled_time, 'scheduled_stock_status_update');
            }
        }        
    }


    public function update_stock_status() {
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

        /**
         * @param  @ Apply stock status to over the all products according to the selected schedule date and time
         * @since    1.1
         * @access   public
         */

        if('yes' === $is_schedule){
            if ($apply_to_all_products === 'yes') {
                $products = get_posts(['post_type' => 'product', 'numberposts' => -1, 'fields' => 'ids']);
                $products_to_update = $products;
            } 
            
            /**
             * @param  @ Apply Stock Status to over the only selected products according to the selected schedule date and time
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
             * @param  @ Apply Stock Status to over the all products according to the selected categories according to the selected schedule date and time
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
}