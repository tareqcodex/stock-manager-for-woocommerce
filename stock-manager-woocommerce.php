<?php
/**
 * Plugin Name: Stock Manager for Woocommerce
 * Description: A WooCommerce plugin to manage product stock status (In Stock/Out of Stock) in bulk with in a single click.
 * Version:     1.1
 * Author:      Semicolon BD 
 * Text Domain: stock-manager
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

 class Stock_Manager {      

    public function __construct(){
        define('VERSION', '1.1');
        define('PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('PLUGIN_URL', plugin_dir_url(__FILE__));

        add_action('admin_init', [$this, 'stock_manager_plugin_activation_check']);      
        add_action( 'plugins_loaded', [$this,'stock_manager_plugin_init'] );
        add_filter('plugin_action_links', [$this, 'stock_manager_settings_action_links'], 10, 2);
        $this->stock_manager_plugin_settings();
        $this->define_admin_script_hooks();
    }
    
    /**	  
	 * @param [type] Preveneting plugin activation if woocommerce is not installed or activated	 
	 * @since    1.1
	 * @access   public
	 */

    public function stock_manager_plugin_activation_check(){
        if (!is_plugin_active('woocommerce/woocommerce.php')) {    
            add_action('admin_notices', function () {
                echo '<div class="error"><p><strong>Stock Manager </strong> plugin requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> to be installed and activated.</p></div>';
            });

            deactivate_plugins(plugin_basename(__FILE__));
            unset($_GET['activate']);
        }
    }

    /**
	 * Added Setting link after plugin activation
	 *
	 * @param [type] $admin.php?page=wc-settings&tab=stock_manager*
	 * @since    1.1
	 * @access   public
	 */
    
	public function stock_manager_settings_action_links($links, $file){		
        // Check if the plugin file matches the plugin you want to customize
        if (false !== strpos($file, 'stock-manager/stock-manager-woocommerce.php')) {
   
           $plugin_actions = array(
               'settings' => sprintf( '<a href="%s">%s</a>', esc_url( admin_url('admin.php?page=wc-settings&tab=stock_manager') ), __( 'Settings', 'stock-manager' ) ),
           );
           return array_merge( $plugin_actions, $links );
       }
       return $links;
   }

    /**	  
	 * @param [type] Loaded text domain	 
	 * @since    1.1
	 * @access   public
	 */
    public function stock_manager_plugin_init(){
        load_plugin_textdomain( 'stock-manager', false, dirname(__FILE__).'/languages');
    }

    /**	  
	 * @param [type] Included other files	 
	 * @since    1.1
	 * @access   private
	 */

    private function stock_manager_plugin_settings(){
        require_once PLUGIN_DIR.'includes/class-stock-manager-tab.php';
        $stock_manager_tab = new Stock_Manager_Tab();

        require_once PLUGIN_DIR.'includes/class-schedule-stock-status.php';
        $stock_management = new Schedule_Stock_Status();

        require_once PLUGIN_DIR.'includes/class-stock-status.php';
        $stock_management = new Stock_Status();

    }

    /**	  
	 * @param [type] Enqueue admin scripts hook	 
	 * @since    1.1
	 * @access   public
	 */

    private function define_admin_script_hooks() {
		add_action( 'admin_enqueue_scripts', [$this,'stock_manager_js_hook'] );
	}

    public function stock_manager_js_hook(){
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('stock-manager-js', PLUGIN_URL.'assets/js/stock-manager.js', array('jquery'), VERSION, true);
    }
 }

 new Stock_Manager();