<?php
/**
 * Plugin Name: Order Details Copier
 * Plugin URI:  https://PHPVibe.com/
 * Description: Copy clean WooCommerce customer, address, courier, invoice, support, and order details from the order screen or orders list in one click.
 * Version:     1.1.1
 * Author:      WooUseful for PHPVibe
 * Author URI:  https://PHPVibe.com/
 * Text Domain: PHPVibe-order-details-copier
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 7.1
 * WC tested up to: 10.8
 *
 * @package WooUseful_Order_Details_Copier
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WUODC_VERSION', '1.1.1' );
define( 'WUODC_FILE', __FILE__ );
define( 'WUODC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WUODC_URL', plugin_dir_url( __FILE__ ) );
define( 'WUODC_BASENAME', plugin_basename( __FILE__ ) );

add_action(
    'before_woocommerce_init',
    static function () {
        if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WUODC_FILE, true );
        }
    }
);

require_once WUODC_DIR . 'includes/Fields.php';
require_once WUODC_DIR . 'includes/OrderData.php';
require_once WUODC_DIR . 'includes/Transform.php';
require_once WUODC_DIR . 'includes/Plugin.php';
require_once WUODC_DIR . 'includes/Admin/Settings.php';
require_once WUODC_DIR . 'includes/Admin/OrderPanel.php';
require_once WUODC_DIR . 'includes/Admin/OrderList.php';

add_action(
    'plugins_loaded',
    static function () {
        \Vibe\WooUseful\OrderDetailsCopier\Plugin::instance()->init();
    }
);
