<?php
/**
 * Main plugin bootstrap.
 *
 * @package WooUseful_Order_Details_Copier
 */

namespace Vibe\WooUseful\OrderDetailsCopier;

use Vibe\WooUseful\OrderDetailsCopier\Admin\OrderList;
use Vibe\WooUseful\OrderDetailsCopier\Admin\OrderPanel;
use Vibe\WooUseful\OrderDetailsCopier\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class.
 */
final class Plugin {
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Settings instance.
     *
     * @var Settings|null
     */
    private $settings = null;

    /**
     * Order panel instance.
     *
     * @var OrderPanel|null
     */
    private $order_panel = null;

    /**
     * Order list instance.
     *
     * @var OrderList|null
     */
    private $order_list = null;

    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Init plugin.
     *
     * @return void
     */
    public function init(): void {
        load_plugin_textdomain( 'woouseful-order-details-copier', false, dirname( WUODC_BASENAME ) . '/languages' );

        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        $this->settings    = new Settings();
        $this->order_panel = new OrderPanel();
        $this->order_list  = new OrderList();

        $this->settings->init();
        $this->order_panel->init();
        $this->order_list->init();
    }

    /**
     * WooCommerce missing admin notice.
     *
     * @return void
     */
    public function woocommerce_missing_notice(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'WooUseful Order Details Copier requires WooCommerce to be installed and active.', 'woouseful-order-details-copier' );
        echo '</p></div>';
    }
}
