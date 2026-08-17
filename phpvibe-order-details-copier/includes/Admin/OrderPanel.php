<?php
/**
 * Order edit screen panel.
 *
 * @package WooUseful_Order_Details_Copier
 */

namespace Vibe\WooUseful\OrderDetailsCopier\Admin;

use Vibe\WooUseful\OrderDetailsCopier\Fields;
use Vibe\WooUseful\OrderDetailsCopier\OrderData;
use Vibe\WooUseful\OrderDetailsCopier\Transform;
use WC_Order;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renders the Easy Copy order panel.
 */
class OrderPanel {
    /**
     * Init hooks.
     *
     * @return void
     */
    public function init(): void {
        add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Register the order screen metabox.
     *
     * @return void
     */
    public function register_metabox(): void {
        if ( 'yes' !== Settings::get( 'enabled', 'yes' ) ) {
            return;
        }

        $screens = array( 'shop_order' );
        if ( function_exists( 'wc_get_page_screen_id' ) ) {
            $screens[] = wc_get_page_screen_id( 'shop-order' );
        }

        $screens = array_unique( array_filter( $screens ) );
        $context = Settings::get( 'metabox_context', 'normal' );
        $title   = Settings::get( 'panel_title', __( 'Easy copy', 'woouseful-order-details-copier' ) );

        foreach ( $screens as $screen ) {
            add_meta_box(
                'wuodc_order_details_copier',
                esc_html( (string) $title ),
                array( $this, 'render_metabox' ),
                $screen,
                $context,
                'default'
            );
        }
    }

    /**
     * Enqueue admin assets.
     *
     * @return void
     */
    public function enqueue_assets(): void {
        if ( ! $this->is_order_screen() ) {
            return;
        }

        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style(
            'wuodc-admin',
            WUODC_URL . 'assets/admin.css',
            array(),
            WUODC_VERSION
        );
        wp_enqueue_script(
            'wuodc-admin',
            WUODC_URL . 'assets/admin.js',
            array(),
            WUODC_VERSION,
            true
        );
        wp_localize_script( 'wuodc-admin', 'WUODC', Settings::script_data() );
    }

    /**
     * Check if the current admin screen is a WooCommerce single order screen.
     *
     * WooCommerce HPOS uses the wc-orders page for both the list table and the
     * single-order editor. Some installs report slightly different screen IDs,
     * so this method also checks the request shape. This keeps the panel styled
     * on HPOS order pages instead of leaving the Quick Copy buttons unstyled at
     * the bottom of the screen.
     *
     * @return bool
     */
    private function is_order_screen(): bool {
        if ( ! function_exists( 'get_current_screen' ) ) {
            return false;
        }

        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        $screen_id = (string) $screen->id;
        $screen_base = isset( $screen->base ) ? (string) $screen->base : '';

        $order_screens = array( 'shop_order' );
        if ( function_exists( 'wc_get_page_screen_id' ) ) {
            $order_screens[] = wc_get_page_screen_id( 'shop-order' );
        }

        if ( in_array( $screen_id, array_unique( array_filter( $order_screens ) ), true ) ) {
            return true;
        }

        // Legacy WooCommerce order editor: post.php?post=123&action=edit.
        if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $post_id = absint( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( $post_id > 0 && 'shop_order' === get_post_type( $post_id ) ) {
                return true;
            }
        }

        // HPOS single order editor: admin.php?page=wc-orders&action=edit&id=123.
        $page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $id     = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ( 'wc-orders' === $page && $id > 0 ) {
            return true;
        }

        if ( 'woocommerce_page_wc-orders' === $screen_id && 'edit' === $action && $id > 0 ) {
            return true;
        }

        if ( false !== strpos( $screen_id, 'wc-orders' ) && $id > 0 ) {
            return true;
        }

        return false !== strpos( $screen_base, 'wc-orders' ) && $id > 0;
    }

    /**
     * Render the order metabox.
     *
     * @param mixed $post_or_order_object Post or order object.
     * @return void
     */
    public function render_metabox( $post_or_order_object ): void {
        $order = $this->resolve_order( $post_or_order_object );

        if ( ! $order ) {
            echo '<p>' . esc_html__( 'Order data could not be loaded.', 'woouseful-order-details-copier' ) . '</p>';
            return;
        }

        $settings       = Settings::get_all();
        $definitions    = Fields::definitions();
        $groups         = Fields::groups();
        $field_order    = $settings['field_order'];
        $enabled_groups = $settings['enabled_groups'];
        $enabled_fields = $settings['enabled_fields'];
        $field_labels   = $settings['field_labels'];
        $field_cleaners = $settings['field_cleaners'] ?? array();
        $hide_empty     = 'yes' === $settings['hide_empty'];
        $density        = 'compact' === $settings['layout_density'] ? 'compact' : 'comfortable';
        $rows_by_group  = array();

        foreach ( $groups as $group_id => $group_label ) {
            $rows_by_group[ $group_id ] = array();
        }

        foreach ( $field_order as $field_id ) {
            if ( ! isset( $definitions[ $field_id ] ) ) {
                continue;
            }

            if ( ! in_array( $field_id, $enabled_fields, true ) ) {
                continue;
            }

            $field = $definitions[ $field_id ];
            $group = (string) ( $field['group'] ?? 'customer' );

            if ( ! in_array( $group, $enabled_groups, true ) ) {
                continue;
            }

            $value = OrderData::get_field_value( $field_id, $order );
            if ( isset( $field_cleaners[ $field_id ] ) && is_array( $field_cleaners[ $field_id ] ) ) {
                $value = Transform::clean( $value, $field_cleaners[ $field_id ] );
            }

            if ( $hide_empty && '' === trim( $value ) ) {
                continue;
            }

            if ( ! isset( $rows_by_group[ $group ] ) ) {
                $rows_by_group[ $group ] = array();
            }

            $default_label = (string) ( $field['label'] ?? $field_id );
            $custom_label  = isset( $field_labels[ $field_id ] ) ? (string) $field_labels[ $field_id ] : '';

            $rows_by_group[ $group ][ $field_id ] = array(
                'label'     => '' !== trim( $custom_label ) ? $custom_label : $default_label,
                'value'     => $value,
                'multiline' => ! empty( $field['multiline'] ),
            );
        }

        foreach ( $settings['extractors'] ?? array() as $index => $extractor ) {
            if ( 'yes' !== ( $extractor['enabled'] ?? 'no' ) || '' === trim( (string) ( $extractor['label'] ?? '' ) ) ) {
                continue;
            }

            $source_field = (string) ( $extractor['source_field'] ?? '' );
            if ( '' === $source_field || ! isset( $definitions[ $source_field ] ) ) {
                continue;
            }

            $source_value = OrderData::get_field_value( $source_field, $order );
            if ( isset( $field_cleaners[ $source_field ] ) && is_array( $field_cleaners[ $source_field ] ) ) {
                $source_value = Transform::clean( $source_value, $field_cleaners[ $source_field ] );
            }
            $value = Transform::extract( $source_value, $extractor );
            if ( $hide_empty && '' === trim( $value ) ) {
                continue;
            }

            $group = (string) ( $definitions[ $source_field ]['group'] ?? 'customer' );
            if ( ! in_array( $group, $enabled_groups, true ) ) {
                continue;
            }
            $rows_by_group[ $group ][ 'extractor_' . $index ] = array(
                'label' => (string) $extractor['label'],
                'value' => $value,
                'multiline' => false,
            );
        }

        if ( in_array( 'custom_templates', $enabled_groups, true ) ) {
            foreach ( $settings['custom_templates'] as $index => $template ) {
                if ( 'yes' !== ( $template['enabled'] ?? 'no' ) ) {
                    continue;
                }

                $title   = isset( $template['title'] ) ? trim( (string) $template['title'] ) : '';
                $content = isset( $template['content'] ) ? (string) $template['content'] : '';

                if ( '' === $title || '' === trim( $content ) ) {
                    continue;
                }

                $value = OrderData::render_template( $content, $order );

                if ( $hide_empty && '' === trim( $value ) ) {
                    continue;
                }

                $rows_by_group['custom_templates'][ 'custom_template_' . $index ] = array(
                    'label'     => $title,
                    'value'     => $value,
                    'multiline' => true,
                );
            }
        }

        $custom_meta_rows = $this->custom_meta_rows( $settings['custom_meta_fields'] ?? array(), $order, $hide_empty );

        if ( empty( array_filter( $rows_by_group ) ) && empty( $custom_meta_rows ) ) {
            echo '<div class="wuodc-panel"><p>' . esc_html__( 'No copyable details are available for this order.', 'woouseful-order-details-copier' ) . '</p></div>';
            return;
        }

        echo '<div class="wuodc-panel wuodc-density-' . esc_attr( $density ) . '" data-wuodc-order-id="' . esc_attr( (string) $order->get_id() ) . '">';
        echo '<div class="wuodc-toast" aria-live="polite" aria-atomic="true"></div>';

        if ( 'yes' === $settings['enable_smart_badges'] ) {
            $this->render_badges( $order );
        }

        if ( 'yes' === $settings['enable_quick_bar'] ) {
            $this->render_quick_bar( $order, 'yes' === $settings['enable_whatsapp'] );
        }

        foreach ( $groups as $group_id => $group_label ) {
            if ( 'custom_meta' === $group_id || empty( $rows_by_group[ $group_id ] ) ) {
                continue;
            }

            echo '<section class="wuodc-section wuodc-section-' . esc_attr( $group_id ) . '">';
            echo '<h4>' . esc_html( $group_label ) . '</h4>';

            foreach ( $rows_by_group[ $group_id ] as $field_id => $row ) {
                $this->render_copy_row( $field_id, $row );
            }

            echo '</section>';
        }

        if ( ! empty( $custom_meta_rows ) && in_array( 'custom_meta', $enabled_groups, true ) ) {
            echo '<section class="wuodc-section wuodc-section-custom-meta">';
            echo '<h4>' . esc_html__( 'Custom meta details', 'woouseful-order-details-copier' ) . '</h4>';

            foreach ( $custom_meta_rows as $field_id => $row ) {
                $this->render_copy_row( $field_id, $row );
            }

            echo '</section>';
        }

        echo '</div>';
    }

    /**
     * Build configured custom meta rows.
     *
     * @param mixed    $fields Custom meta field settings.
     * @param WC_Order $order Order.
     * @param bool     $hide_empty Hide empty values.
     * @return array<string,array<string,mixed>>
     */
    private function custom_meta_rows( $fields, WC_Order $order, bool $hide_empty ): array {
        $rows = array();

        if ( ! is_array( $fields ) ) {
            return $rows;
        }

        foreach ( $fields as $index => $field ) {
            if ( ! is_array( $field ) || 'yes' !== ( $field['enabled'] ?? 'no' ) ) {
                continue;
            }

            $meta_key = isset( $field['meta_key'] ) ? sanitize_key( (string) $field['meta_key'] ) : '';
            $label    = isset( $field['label'] ) ? trim( (string) $field['label'] ) : '';

            if ( '' === $meta_key ) {
                continue;
            }

            $value = OrderData::get_meta_value( $order, $meta_key );

            if ( $hide_empty && '' === trim( $value ) ) {
                continue;
            }

            $rows[ 'custom_meta_' . absint( $index ) ] = array(
                'label'     => '' !== $label ? $label : $meta_key,
                'value'     => $value,
                'multiline' => false !== strpos( $value, "\n" ),
            );
        }

        return $rows;
    }

    /**
     * Render smart badges.
     *
     * @param WC_Order $order Order.
     * @return void
     */
    private function render_badges( WC_Order $order ): void {
        $badges = OrderData::smart_badges( $order );

        if ( empty( $badges ) ) {
            return;
        }

        echo '<div class="wuodc-badges" aria-label="' . esc_attr__( 'Order notes', 'woouseful-order-details-copier' ) . '">';
        foreach ( $badges as $badge ) {
            $label = (string) ( $badge['label'] ?? '' );
            $type  = sanitize_html_class( (string) ( $badge['type'] ?? 'neutral' ) );
            if ( '' === $label ) {
                continue;
            }
            echo '<span class="wuodc-badge wuodc-badge-' . esc_attr( $type ) . '">' . esc_html( $label ) . '</span>';
        }
        echo '</div>';
    }

    /**
     * Render quick copy bar.
     *
     * @param WC_Order $order Order.
     * @param bool     $enable_whatsapp Whether WhatsApp action is enabled.
     * @return void
     */
    private function render_quick_bar( WC_Order $order, bool $enable_whatsapp ): void {
        $actions = OrderData::quick_actions( $order );

        if ( empty( $actions ) && ! $enable_whatsapp ) {
            return;
        }

        echo '<div class="wuodc-quick-bar">';
        echo '<span class="wuodc-quick-label">' . esc_html__( 'Quick copy:', 'woouseful-order-details-copier' ) . '</span>';

        foreach ( $actions as $action_id => $action ) {
            $source_id = 'wuodc-quick-source-' . $order->get_id() . '-' . sanitize_key( $action_id );
            $icon      = sanitize_html_class( (string) ( $action['icon'] ?? 'dashicons-admin-page' ) );
            echo '<button type="button" class="button wuodc-quick-copy-button" data-wuodc-source="' . esc_attr( $source_id ) . '">';
            echo '<span class="dashicons ' . esc_attr( $icon ) . '" aria-hidden="true"></span>';
            echo esc_html( (string) $action['label'] );
            echo '</button>';
            echo '<textarea id="' . esc_attr( $source_id ) . '" class="wuodc-copy-source" hidden readonly>' . esc_textarea( (string) $action['value'] ) . '</textarea>';
        }

        if ( $enable_whatsapp ) {
            $whatsapp_url = OrderData::whatsapp_url( $order );
            if ( '' !== $whatsapp_url ) {
                echo '<a class="button wuodc-whatsapp-button" href="' . esc_url( $whatsapp_url ) . '" target="_blank" rel="noopener noreferrer">';
                echo '<span class="dashicons dashicons-format-chat" aria-hidden="true"></span>';
                echo esc_html__( 'Open WhatsApp', 'woouseful-order-details-copier' );
                echo '</a>';
            }
        }

        echo '</div>';
    }

    /**
     * Render one copyable row.
     *
     * @param string              $field_id Field ID.
     * @param array<string,mixed> $row Row data.
     * @return void
     */
    private function render_copy_row( string $field_id, array $row ): void {
        $label     = (string) ( $row['label'] ?? $field_id );
        $value     = (string) ( $row['value'] ?? '' );
        $multiline = ! empty( $row['multiline'] );
        $row_class = $multiline ? ' wuodc-field-multiline' : '';

        echo '<div class="wuodc-field' . esc_attr( $row_class ) . '" data-wuodc-field-id="' . esc_attr( $field_id ) . '">';
        echo '<label>' . esc_html( $label ) . '</label>';
        echo '<div class="wuodc-copy-control">';

        if ( $multiline ) {
            echo '<textarea class="wuodc-copy-value" rows="3" readonly>' . esc_textarea( $value ) . '</textarea>';
        } else {
            echo '<input class="wuodc-copy-value" type="text" readonly value="' . esc_attr( $value ) . '">';
        }

        echo '<button type="button" class="button-link wuodc-copy-button" aria-label="' . esc_attr( sprintf( __( 'Copy %s', 'woouseful-order-details-copier' ), $label ) ) . '" title="' . esc_attr__( 'Copy to clipboard', 'woouseful-order-details-copier' ) . '">';
        echo '<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>';
        echo '<span class="screen-reader-text">' . esc_html( sprintf( __( 'Copy %s', 'woouseful-order-details-copier' ), $label ) ) . '</span>';
        echo '</button>';

        echo '</div>';
        echo '</div>';
    }

    /**
     * Resolve order object from current screen callback payload.
     *
     * @param mixed $post_or_order_object Post or order object.
     * @return WC_Order|null
     */
    private function resolve_order( $post_or_order_object ): ?WC_Order {
        if ( $post_or_order_object instanceof WC_Order ) {
            return $post_or_order_object;
        }

        if ( $post_or_order_object instanceof WP_Post ) {
            $order = wc_get_order( $post_or_order_object->ID );
            return $order instanceof WC_Order ? $order : null;
        }

        $order_id = 0;
        if ( isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $order_id = absint( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        } elseif ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $order_id = absint( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }

        if ( $order_id > 0 ) {
            $order = wc_get_order( $order_id );
            return $order instanceof WC_Order ? $order : null;
        }

        return null;
    }
}
