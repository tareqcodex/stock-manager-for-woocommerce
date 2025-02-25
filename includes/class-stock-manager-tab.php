<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Stock_Manager_Tab{
    public function __construct(){
        add_filter('woocommerce_settings_tabs_array', [$this,'stock_manager_settings_tabs'], 50);
        add_action('woocommerce_settings_tabs_stock_manager', [$this,'stock_manager_tab_settings_fields'], 50);
        add_action('woocommerce_settings_save_stock_manager', [$this,'save_stock_manager_settings']);
    }
    // Add custom tab to WooCommerce settings
    public function stock_manager_settings_tabs($tabs) {
        $tabs['stock_manager'] = __('Stock Manager', 'stock-manager');
        return $tabs;
    }
    public function stock_manager_tab_settings_fields() {
      
        // Get all the products
        $allproducts = get_posts(array(
            'post_type' => 'product',
            'numberposts' => -1,
        ));

        $products = array();
        foreach ($allproducts as $product) {
            $products[$product->ID] = $product->post_title;
        }

        // Get all the product categories
        $allcategories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));

        $categories = array();
        foreach ($allcategories as $category) {
            $categories[$category->term_id] = $category->name ;
        }
        wp_nonce_field('stock_manager_option_fields', 'stock_manager_option_fields');
        woocommerce_admin_fields(
            array(
                'section_title' => array(
                'name' => __('Stock Manager Options', 'stock-manager'),
                'type' => 'title',
                'desc' => '',
                'id'   => 'stock_manager_option'
            ),          
            array(
                'title'     => __('Stock Status', 'stock-manager'),
                'desc_tip'  => __('Stock status will be applied all products.', 'stock-manager'),
                'id'        => 'select_stock_status_option',
                'class'     => 'chosen_select',
                'type'      => 'select',
                'options'   => [                                
                    'instock'           => __('In Stock', 'stock-manager'),
                    'outofstock'        => __('Stock Out', 'stock-manager'),
                ]
            ), 
            
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'stock_manager_option_end'
            ),                               
        ));
        woocommerce_admin_fields(
            array(
                'section_title' => array(
                'name' => __('Apply To', 'stock-manager'),
                'type' => 'title',
                'desc' => '',
                'id'   => 'stock_manager_apply_section'
            ),          
            array(
                'title'     => __('All Products', 'stock-manager'),
                'desc'      => __('Apply to the all products', 'stock-manager'),
                'desc_tip'  => __('If you want to apply the Stock status over the all products then you can select it.', 'stock-manager'), 
                'id'        => 'stock_status_all_products',
                'type'      => 'checkbox',  
                
            ), 
            array(
                'title'     => __('Selected Products', 'stock-manager'),
                'desc_tip'  => __('Stock status will be applied over the selected products only.', 'stock-manager'),
                'id'        => 'stock_status_selected_products',
                'class'     => 'chosen_select',
                'type'      => 'multiselect',
                'options'   => $products,
            ),  
            array(
                'title'     => __('Selected Categories', 'stock-manager'),
                'desc_tip'  => __('Stock status will be applied over the selected category\'s products only.', 'stock-manager'),
                'id'        => 'stock_status_selected_categories',
                'class'     => 'chosen_select',
                'type'      => 'multiselect',
                'options'   => $categories,
            ),  

            array(
                'title'     => __('Schedule Stock Management', 'stock-manager'),
                'desc'      => __('Schedule for Stock Management?', 'stock-manager'),
                'desc_tip'  => __('You can schedule the stock management. The product status will be changing upon selected date and time.', 'stock-manager'), 
                'id'        => 'is_schedule',
                'type'      => 'checkbox',  
                
            ), 
            
            array(
                'title'    => __('Stock Schedule Date', 'stock-manager'),
                'id'       => 'stock_change_date',
                'type'     => 'text',
                'custom_attributes' => [
                'readonly' => 'readonly',
                'required' => 'required' // Make field required
                ],
                'desc'     => __('Select the date for stock status change.', 'stock-manager'),
                'desc_tip' => true
            ),
            
            array(
                'title'    => __('Stock Schedule Time', 'stock-manager'),
                'id'       => 'stock_change_time',
                'type'     => 'time',
                'desc'     => __('Select the time for stock status change. If empty then default time will be the 23:59 on the selected date.', 'stock-manager'),
                'desc_tip' => true
            ),
            
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'stock_manager_apply_section_end'
            ),                               
        ));
    }


    public function save_stock_manager_settings() {
        // Verify nonce
        if (!isset($_POST['stock_manager_option_fields']) || !wp_verify_nonce($_POST['stock_manager_option_fields'], 'stock_manager_option_fields')) {
            return;
        }
        // Save the settings 
        $status_option = isset($_POST['select_stock_status_option']) ? $_POST['select_stock_status_option'] : '';
        update_option('select_stock_status_option', sanitize_text_field( $status_option) ); 

        $is_apply_to_all_products = isset($_POST['stock_status_all_products']) ? 'yes' : 'no';
        update_option('stock_status_all_products',  sanitize_text_field($is_apply_to_all_products));           

        $apply_to_selected_products = isset($_POST['stock_status_selected_products']) ? $_POST['stock_status_selected_products'] : '';
        update_option('stock_status_selected_products', $apply_to_selected_products);  

        $apply_to_selected_categories = isset($_POST['stock_status_selected_categories']) ? $_POST['stock_status_selected_categories'] : '';
        update_option('stock_status_selected_categories', $apply_to_selected_categories);  
        
        // Schedule Date and Time
        $is_schedule = isset($_POST['is_schedule']) ? 'yes' : 'no';
        update_option('is_schedule',  sanitize_text_field($is_schedule)); 

        $date = sanitize_text_field(wp_unslash($_POST['stock_change_date'] ?? ''));
        $time = sanitize_text_field(wp_unslash($_POST['stock_change_time'] ?? ''));
     
        update_option('stock_change_date', $date);
        update_option('stock_change_time', $time);

    }
    
}
