<?php
/**
 * Orders list copy helpers.
 *
 * @package WooUseful_Order_Details_Copier
 */

namespace Vibe\WooUseful\OrderDetailsCopier\Admin;

use Vibe\WooUseful\OrderDetailsCopier\OrderData;
use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Adds copy tools to WooCommerce orders lists.
 */
class OrderList {
    /**
     * Init hooks.
     *
     * @return void
     */
    public function init(): void {
        add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_column' ), 70 );
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_legacy_column' ), 20, 2 );

        add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_column' ), 70 );
        add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_hpos_column' ), 20, 2 );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_footer', array( $this, 'render_bulk_toolbar' ) );
    }

    /**
     * Add the list column.
     *
     * @param array<string,string> $columns Columns.
     * @return array<string,string>
     */
    public function add_column( array $columns ): array {
        if ( ! $this->feature_enabled() ) {
            return $columns;
        }

        $new_columns = array();
        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;
            if ( in_array( $key, array( 'order_status', 'status' ), true ) ) {
                $new_columns['wuodc_copy'] = __( 'Copy', 'woouseful-order-details-copier' );
            }
        }

        if ( ! isset( $new_columns['wuodc_copy'] ) ) {
            $new_columns['wuodc_copy'] = __( 'Copy', 'woouseful-order-details-copier' );
        }

        return $new_columns;
    }

    /**
     * Render column for legacy orders.
     *
     * @param string $column Column.
     * @param int    $post_id Post ID.
     * @return void
     */
    public function render_legacy_column( string $column, int $post_id ): void {
        if ( 'wuodc_copy' !== $column || ! $this->feature_enabled() ) {
            return;
        }

        $order = wc_get_order( $post_id );
        if ( $order instanceof WC_Order ) {
            $this->render_column_content( $order );
        }
    }

    /**
     * Render column for HPOS orders.
     *
     * @param string $column Column.
     * @param mixed  $order Order object.
     * @return void
     */
    public function render_hpos_column( string $column, $order ): void {
        if ( 'wuodc_copy' !== $column || ! $this->feature_enabled() ) {
            return;
        }

        if ( $order instanceof WC_Order ) {
            $this->render_column_content( $order );
        } elseif ( is_numeric( $order ) ) {
            $resolved = wc_get_order( absint( $order ) );
            if ( $resolved instanceof WC_Order ) {
                $this->render_column_content( $resolved );
            }
        }
    }

    /**
     * Enqueue assets on orders list.
     *
     * @return void
     */
    public function enqueue_assets(): void {
        if ( ! $this->feature_enabled() || ! $this->is_order_list_screen() ) {
            return;
        }

        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'wuodc-admin', WUODC_URL . 'assets/admin.css', array(), WUODC_VERSION );
        wp_enqueue_script( 'wuodc-admin', WUODC_URL . 'assets/admin.js', array(), WUODC_VERSION, true );
        wp_localize_script( 'wuodc-admin', 'WUODC', Settings::script_data() );
    }

    /**
     * Render the bulk toolbar template.
     *
     * @return void
     */
    public function render_bulk_toolbar(): void {
        if ( ! $this->feature_enabled() || ! $this->is_order_list_screen() ) {
            return;
        }

        $templates = OrderData::quick_templates();
        ?>
        <div class="wuodc-list-bulk" data-wuodc-list-bulk hidden>
            <strong><?php echo esc_html__( 'Copy selected orders:', 'woouseful-order-details-copier' ); ?></strong>
            <?php foreach ( $templates as $template_id => $template ) : ?>
                <button type="button" class="button wuodc-bulk-copy-button" data-wuodc-bulk-template="<?php echo esc_attr( $template_id ); ?>">
                    <?php echo esc_html( $template['label'] ); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render one column menu.
     *
     * @param WC_Order $order Order.
     * @return void
     */
    private function render_column_content( WC_Order $order ): void {
        $actions = OrderData::quick_actions( $order );

        if ( empty( $actions ) ) {
            echo '&mdash;';
            return;
        }

        echo '<div class="wuodc-list-copy" data-wuodc-list-order="' . esc_attr( (string) $order->get_id() ) . '">';
        echo '<button type="button" class="button button-small wuodc-list-menu-toggle" aria-expanded="false">';
        echo '<span class="dashicons dashicons-admin-page" aria-hidden="true"></span> ';
        echo esc_html__( 'Copy', 'woouseful-order-details-copier' );
        echo '</button>';
        echo '<div class="wuodc-list-menu" role="menu">';

        foreach ( $actions as $action_id => $action ) {
            echo '<button type="button" class="wuodc-list-copy-button" data-wuodc-list-template="' . esc_attr( $action_id ) . '" role="menuitem">';
            echo esc_html( (string) $action['label'] );
            echo '</button>';
            echo '<textarea class="wuodc-list-copy-source" data-wuodc-template="' . esc_attr( $action_id ) . '" hidden readonly>' . esc_textarea( (string) $action['value'] ) . '</textarea>';
        }

        if ( 'yes' === Settings::get( 'enable_whatsapp', 'yes' ) ) {
            $whatsapp_url = OrderData::whatsapp_url( $order );
            if ( '' !== $whatsapp_url ) {
                echo '<a href="' . esc_url( $whatsapp_url ) . '" target="_blank" rel="noopener noreferrer" role="menuitem">' . esc_html__( 'Open WhatsApp', 'woouseful-order-details-copier' ) . '</a>';
            }
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Check feature enabled.
     *
     * @return bool
     */
    private function feature_enabled(): bool {
        return 'yes' === Settings::get( 'enabled', 'yes' ) && 'yes' === Settings::get( 'enable_order_list', 'yes' );
    }

    /**
     * Detect WooCommerce orders list screens.
     *
     * The HPOS wc-orders screen ID is also used by the single order editor on
     * many WooCommerce versions. Avoid loading list-only controls on
     * admin.php?page=wc-orders&action=edit&id=123.
     *
     * @return bool
     */
    private function is_order_list_screen(): bool {
        if ( ! function_exists( 'get_current_screen' ) ) {
            return false;
        }

        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        $screen_id = (string) $screen->id;

        if ( 'edit-shop_order' === $screen_id ) {
            return true;
        }

        if ( 'woocommerce_page_wc-orders' !== $screen_id ) {
            return false;
        }

        $id     = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        return ! ( $id > 0 || 'edit' === $action );
    }
}
