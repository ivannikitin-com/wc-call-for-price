<?php

/**
 * Plugin Name: Woocommerce Call for Price
 * Description: !!!Add a Call For Price button on single product page. A call form appears on click.
 * Version: 1.0.0
 * Author: Иван Никитин и партнеры
 * Author URI: https://ivannikitin.com/
 * Text Domain: wc-call-for-price
 * Domain Path: /languages
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/ivannikitin-com/wc-call-for-price
 * GitHub Branch:     master
 * Requires WP:       5.0
 * Requires PHP:      5.3
 * Tested up to: 5.5.1
 *
 * @license   GPL-2.0+
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'WCCFP_VERSION', '1.0.0' );

class WCCallForPrice {

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @access  public
	 * @action wc_add_video_to_gallery_init
	 */
	public function __construct() {
		$plugin = plugin_basename( __FILE__ );
		add_action( 'plugins_loaded', array( $this, 'check_woocommerce' ), 9 );
		if( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts_and_styles' ) );
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_wc_call_for_price', array( $this, 'settings_tab' ) );
			add_action( 'woocommerce_update_options_wc_call_for_price', array( $this, 'update_settings' ) );
			add_filter( "plugin_action_links_$plugin", array( $this, 'plugin_add_settings_link' ) );			
			add_action( 'init', array( $this, 'reactivate_action'), 9999 );
			add_action( 'init', array( $this, 'load_plugin_textdomain') );
			register_activation_hook( __FILE__, array( 'WCCallForPrice', 'install' ) );
			register_uninstall_hook( __FILE__, array( 'WCCallForPrice', 'uninstall' ) );
		}
		add_action( 'woocommerce_single_product_summary', array($this, 'call_for_price_output'), 30 );
		add_filter( 'woocommerce_empty_price_html', array($this, 'button_replace_price'), 1, 2 );
		add_action( 'wp_enqueue_scripts', array($this,'plugin_scripts_and_styles'),10  );
		add_action( 'wp_enqueue_scripts', array($this,'cfp_ajax_data'), 1999  );
		add_action( 'wp_ajax_get_product_price', array($this, 'get_product_price_callback' ) );
		add_action( 'wp_ajax_nopriv_get_product_price', array($this, 'get_product_price_callback' ) );		
	}

	public function check_woocommerce() {
		if ( $this->is_woocommerce_activated() === false ) {
			$error = sprintf( __( 'WC Call For Price requires %sWooCommerce%s to be installed & activated!' , 'wc-call-for-price' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
			$message = '<div class="error"><p>' . $error . '</p></div>';
			echo $message;
			return;
		}
	}

	public function is_woocommerce_activated() {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add a new settings tab to woocommerce/settings
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['wc_call_for_price'] = _x( 'Запрос цены', 'WooCommerce Settings Tab', 'wc-call-for-price' );
		return $settings_tabs;
	}

	/**
	 * @ince    1.0.0
	 * @access  public
	 */
	public  function settings_tab() {
		woocommerce_admin_fields( self::get_settings() ); 
	}


	/**
	 * Define the settings for this plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 * @filters wc_call_for_price_settings
	 */
	public function get_settings() {

		echo 'Укажите, какая форма должна отображаться при клике на кнопке запроса цены.';

		$selected_option = woocommerce_settings_get_option('call_for_price_form');
		//$selected_option = get_option('call_for_price_form');
		$args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1);
		$forms_array = array();
		if( $forms_objects = get_posts($args)){
		    foreach($forms_objects as $key){
		       $forms_array[$key->ID] = $key->post_title;
		    }
		}else{
		    $forms_array['0'] = esc_html__('No Contact Form found', 'wc-call-for-prices');
		}	

		$settings['section_title'] = array(
						'name'     => __( 'Call for Price Settings', 'wc-call-for-prices' ),
						'type'     => 'title',
						'desc'     => '',
						'id'       => 'do_section_title'
		);

		$settings['call_for_price_form'] = array(
			'id'		=> 'call_for_price_form',
			'type'    	=> 'select',
			'default' 	=> $selected_option,
		    'options' 	=> $forms_array
		);

		$settings['section_end'] = array(
			'type' => 'sectionend',
			'id' => 'do_section_end'
		);

		return apply_filters( 'wc_call_for_prices_settings', $settings );
	}

	/**
	 * @since    1.0.0
	 * @access  public
	 */
	function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}	

	/**
	 * Load the translation
	 *
	 * @since    1.0.0
	 * @access  public
	 * @filter plugin_locale
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wc-call-for-price' );

		load_textdomain( 'wc-call-for-price', trailingslashit( WP_LANG_DIR ) . 'wc-call-for-price/wc-call-for-price-' . $locale . '.mo' );
		load_plugin_textdomain( 'wc-call-for-price', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Reactivate the reorder link in order details
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	public function reactivate_action() {

	}

	/**
	 * Setup Database on activating the plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	static public function install() {

	}

	/**
	 * Cleanup Database on deleting the plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	static public function uninstall() {
		
	}


	/**
	 *
	 * @since    1.1.0
	 * @access  public
	 */
	public function plugin_scripts_and_styles() {
		wp_localize_script( 'wc-call-for-price-js', 'cfpajax', 
			array(
				'url' => admin_url('admin-ajax.php')
			)
		);		
		wp_enqueue_script( 'wc_call_for_price-js', plugins_url( '/js/wc-call-for-price.js' , __FILE__ ), array( 'jquery'), WCCFP_VERSION, true );
		wp_enqueue_style( 'wc-call-for-price_admin_styles', plugins_url('admin-styles.css', __FILE__) );
		wp_enqueue_style( 'wc-call-for-price_styles', plugins_url('styles.css', __FILE__) );
 

    }

    public function admin_scripts_and_styles() {
		wp_enqueue_style( 'wc-call-for-price_admin_styles', plugins_url('admin-styles.css', __FILE__) ); 
    }


	/**
	 * Add a link to plugin settings to the plugin list
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	public function plugin_add_settings_link( $links ) {
		$settings_link = '<a href="'. admin_url( 'admin.php?page=wc-settings&tab=wc_call_for_prices' ) .'">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	public function call_for_price_output(){
		global $product;
		$call_for_price_form_id = get_option('call_for_price_form');
		if ($product->get_price() == 0 && $call_for_price_form_id) {
			echo '<span><a href="#modalCallForPrice" class="button btn_brd_light-blue-green callforprice" data-toggle="modal">'.__('Запросить цену','wc-call-for-price').'</a></span>';
		}
	}

	public function button_replace_price($price, $_product) {
		$call_for_price_form_id = get_option('call_for_price_form');
		if ($_product->get_price() == 0 && $call_for_price_form_id) {
			return __('

<p class="price"><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">Цена по запросу</span></bdi></span></p>
<div id="modalCallForPrice" class="modal hide fade" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header"><div class="form_title">Запрос цены на товар</div><button class="close" type="button" data-dismiss="modal">×</button></div><!--/.modal-header-->
				<div class="modal-body">'
					.do_shortcode('[contact-form-7 id="'.$call_for_price_form_id.'" html_id="callforpriceform"  title="Запросить цену"]').
				'</div>
			</div>
		</div>
</div>'
			);
		}
		return $price;
	}	

	public function get_product_price_callback(){
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				echo '';
				wp_die();
			}

			$result['product_price'] = get_post_meta($_POST['product_id'],'_price',true).' '.get_woocommerce_currency_symbol();
			$result['product_quantity'] = (isset($_POST['quantity']))?$_POST['quantity']:'';
			$result['product_sku'] = get_post_meta($_POST['product_id'],'_sku',true);
			$product_post = get_post($_POST['product_id']);
			$result['product_title'] = $product_post->post_title;
			$result = json_encode($result);
			echo $result;
			wp_die();
	}	
}

$WCCallForPrice = new WCCallForPrice();
