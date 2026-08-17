<?php
/**
 * Shared order data helpers.
 *
 * @package PHPVibe_Order_Details_Copier
 */

namespace Vibe\PHPVibe\OrderDetailsCopier;

use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Builds clean, copy-friendly order data.
 */
final class OrderData {
    /**
     * Get field value.
     *
     * @param string   $field_id Field ID.
     * @param WC_Order $order Order.
     * @return string
     */
    public static function get_field_value( string $field_id, WC_Order $order ): string {
        $variables = self::template_variables( $order );

        switch ( $field_id ) {
            case 'billing_first_name':
                $value = $order->get_billing_first_name();
                break;
            case 'billing_last_name':
                $value = $order->get_billing_last_name();
                break;
            case 'billing_full_name':
                $value = self::billing_full_name( $order );
                break;
            case 'billing_email':
                $value = $order->get_billing_email();
                break;
            case 'billing_phone':
                $value = $order->get_billing_phone();
                break;
            case 'billing_company':
                $value = $order->get_billing_company();
                break;
            case 'billing_address_1':
                $value = $order->get_billing_address_1();
                break;
            case 'billing_address_2':
                $value = $order->get_billing_address_2();
                break;
            case 'billing_city':
                $value = $order->get_billing_city();
                break;
            case 'billing_state':
                $value = self::state_name( $order->get_billing_country(), $order->get_billing_state() );
                break;
            case 'billing_postcode':
                $value = $order->get_billing_postcode();
                break;
            case 'billing_country':
                $value = self::country_name( $order->get_billing_country() );
                break;
            case 'billing_full_address':
                $value = self::full_address( $order, 'billing' );
                break;
            case 'billing_all_details':
                $value = self::template_block( 'billing', $order );
                break;
            case 'shipping_first_name':
                $value = $order->get_shipping_first_name();
                break;
            case 'shipping_last_name':
                $value = $order->get_shipping_last_name();
                break;
            case 'shipping_full_name':
                $value = self::shipping_full_name( $order, true );
                break;
            case 'shipping_company':
                $value = self::has_shipping_address( $order ) ? $order->get_shipping_company() : $order->get_billing_company();
                break;
            case 'shipping_phone':
                $value = self::shipping_phone( $order );
                break;
            case 'shipping_address_1':
                $value = self::shipping_or_billing_value( $order, 'address_1' );
                break;
            case 'shipping_address_2':
                $value = self::shipping_or_billing_value( $order, 'address_2' );
                break;
            case 'shipping_city':
                $value = self::shipping_or_billing_value( $order, 'city' );
                break;
            case 'shipping_state':
                $value = self::has_shipping_address( $order ) ? self::state_name( $order->get_shipping_country(), $order->get_shipping_state() ) : self::state_name( $order->get_billing_country(), $order->get_billing_state() );
                break;
            case 'shipping_postcode':
                $value = self::shipping_or_billing_value( $order, 'postcode' );
                break;
            case 'shipping_country':
                $value = self::has_shipping_address( $order ) ? self::country_name( $order->get_shipping_country() ) : self::country_name( $order->get_billing_country() );
                break;
            case 'shipping_full_address':
                $value = self::full_address( $order, 'shipping', true );
                break;
            case 'shipping_all_details':
                $value = self::template_block( 'shipping', $order );
                break;
            case 'order_id':
                $value = (string) $order->get_id();
                break;
            case 'order_number':
                $value = $order->get_order_number();
                break;
            case 'order_date':
                $date  = $order->get_date_created();
                $value = $date ? wc_format_datetime( $date ) : '';
                break;
            case 'order_status':
                $value = function_exists( 'wc_get_order_status_name' ) ? wc_get_order_status_name( $order->get_status() ) : $order->get_status();
                break;
            case 'order_total':
                $value = self::formatted_order_total( $order );
                break;
            case 'payment_method':
                $value = $order->get_payment_method_title();
                break;
            case 'shipping_method':
                $value = $order->get_shipping_method();
                break;
            case 'order_items':
                $value = self::order_items_summary( $order );
                break;
            case 'customer_note':
                $value = $order->get_customer_note();
                break;
            case 'order_admin_link':
                $value = self::order_admin_link( $order );
                break;
            case 'customer_contact_block':
                $value = self::template_block( 'contact', $order );
                break;
            case 'courier_block':
                $value = self::template_block( 'courier', $order );
                break;
            case 'invoice_block':
                $value = self::template_block( 'invoice', $order );
                break;
            case 'packing_block':
                $value = self::template_block( 'packing', $order );
                break;
            case 'spreadsheet_row':
                $value = self::template_block( 'spreadsheet', $order );
                break;
            case 'whatsapp_block':
                $value = self::template_block( 'whatsapp', $order );
                break;
            default:
                $value = isset( $variables[ '{' . $field_id . '}' ] ) ? $variables[ '{' . $field_id . '}' ] : '';
                break;
        }

        /**
         * Filter a copyable field value.
         *
         * @param string   $value Field value.
         * @param string   $field_id Field ID.
         * @param WC_Order $order Order object.
         */
        return (string) apply_filters( 'wuodc_field_value', self::clean_value( $value ), $field_id, $order );
    }

    /**
     * Get built-in quick copy templates.
     *
     * @return array<string,array<string,string>>
     */
    public static function quick_templates(): array {
        $templates = array(
            'courier'  => array(
                'label' => __( 'Courier', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-location-alt',
            ),
            'contact'  => array(
                'label' => __( 'Contact', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-phone',
            ),
            'shipping' => array(
                'label' => __( 'Shipping', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-archive',
            ),
            'billing'  => array(
                'label' => __( 'Billing', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-clipboard',
            ),
            'invoice'  => array(
                'label' => __( 'Invoice', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-media-document',
            ),
            'summary'  => array(
                'label' => __( 'Summary', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-list-view',
            ),
            'packing'  => array(
                'label' => __( 'Prep list', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-products',
            ),
            'spreadsheet'  => array(
                'label' => __( 'Spreadsheet', 'phpvibe-order-details-copier' ),
                'icon'  => 'dashicons-editor-table',
            ),
        );

        /**
         * Filter built-in quick copy templates.
         *
         * @param array<string,array<string,string>> $templates Quick templates.
         */
        return apply_filters( 'wuodc_quick_templates', $templates );
    }

    /**
     * Build quick action data.
     *
     * @param WC_Order $order Order.
     * @return array<string,array<string,string>>
     */
    public static function quick_actions( WC_Order $order ): array {
        $actions = array();

        foreach ( self::quick_templates() as $template_id => $template ) {
            $value = self::template_block( $template_id, $order );
            if ( '' === trim( $value ) ) {
                continue;
            }

            $actions[ $template_id ] = array(
                'label' => $template['label'],
                'icon'  => $template['icon'],
                'value' => $value,
            );
        }

        return $actions;
    }

    /**
     * Build one template block.
     *
     * @param string   $template_id Template id.
     * @param WC_Order $order Order.
     * @return string
     */
    public static function template_block( string $template_id, WC_Order $order ): string {
        switch ( $template_id ) {
            case 'courier':
                $value = self::join_lines(
                    array(
                        self::shipping_full_name( $order, true ),
                        self::shipping_phone( $order ),
                        self::shipping_or_billing_value( $order, 'address_1' ),
                        self::shipping_or_billing_value( $order, 'address_2' ),
                        self::join_inline(
                            array(
                                self::shipping_or_billing_value( $order, 'city' ),
                                self::has_shipping_address( $order ) ? self::state_name( $order->get_shipping_country(), $order->get_shipping_state() ) : self::state_name( $order->get_billing_country(), $order->get_billing_state() ),
                                self::shipping_or_billing_value( $order, 'postcode' ),
                            )
                        ),
                        self::has_shipping_address( $order ) ? self::country_name( $order->get_shipping_country() ) : self::country_name( $order->get_billing_country() ),
                        sprintf( /* translators: %s: order number */ __( 'Order #%s', 'phpvibe-order-details-copier' ), $order->get_order_number() ),
                    )
                );
                break;
            case 'contact':
                $value = self::join_lines(
                    array(
                        self::billing_full_name( $order ),
                        $order->get_billing_phone(),
                        $order->get_billing_email(),
                    )
                );
                break;
            case 'shipping':
                $value = self::join_lines(
                    array(
                        self::shipping_full_name( $order, true ),
                        self::has_shipping_address( $order ) ? $order->get_shipping_company() : $order->get_billing_company(),
                        self::full_address( $order, 'shipping', true ),
                        self::shipping_phone( $order ),
                        $order->get_billing_email(),
                    )
                );
                break;
            case 'billing':
                $value = self::join_lines(
                    array(
                        self::billing_full_name( $order ),
                        $order->get_billing_company(),
                        self::full_address( $order, 'billing' ),
                        $order->get_billing_email(),
                        $order->get_billing_phone(),
                    )
                );
                break;
            case 'invoice':
                $value = self::join_lines(
                    array(
                        self::billing_full_name( $order ),
                        $order->get_billing_company(),
                        self::full_address( $order, 'billing' ),
                        $order->get_billing_email(),
                        $order->get_billing_phone(),
                        sprintf( /* translators: %s: order number */ __( 'Order #%s', 'phpvibe-order-details-copier' ), $order->get_order_number() ),
                        self::formatted_order_total( $order ),
                    )
                );
                break;
            case 'summary':
                $value = self::join_lines(
                    array(
                        sprintf( /* translators: %s: order number */ __( 'Order #%s', 'phpvibe-order-details-copier' ), $order->get_order_number() ),
                        self::billing_full_name( $order ),
                        $order->get_billing_phone(),
                        $order->get_billing_email(),
                        self::formatted_order_total( $order ),
                        $order->get_payment_method_title(),
                        $order->get_shipping_method(),
                        self::order_items_summary( $order ),
                    )
                );
                break;
            case 'packing':
                $value = self::join_lines(
                    array(
                        sprintf( /* translators: %s: order number */ __( 'Order #%s', 'phpvibe-order-details-copier' ), $order->get_order_number() ),
                        self::billing_full_name( $order ),
                        self::order_items_summary( $order ),
                        self::shipping_method_line( $order ),
                        $order->get_customer_note(),
                    )
                );
                break;
            case 'spreadsheet':
                $value = self::join_tabbed(
                    array(
                        $order->get_order_number(),
                        self::billing_full_name( $order ),
                        self::shipping_phone( $order ),
                        $order->get_billing_email(),
                        self::shipping_or_billing_value( $order, 'city' ),
                        self::shipping_or_billing_value( $order, 'address_1' ),
                        self::formatted_order_total( $order ),
                        function_exists( 'wc_get_order_status_name' ) ? wc_get_order_status_name( $order->get_status() ) : $order->get_status(),
                    )
                );
                break;
            case 'whatsapp':
                $value = self::render_template(
                    __( 'Hello {first_name}, regarding your order #{order_number}, we wanted to contact you about your delivery details.', 'phpvibe-order-details-copier' ),
                    $order
                );
                break;
            default:
                $value = '';
                break;
        }

        /**
         * Filter a built-in copy block.
         *
         * @param string   $value Built block value.
         * @param string   $template_id Template ID.
         * @param WC_Order $order Order object.
         */
        return (string) apply_filters( 'wuodc_template_block', self::clean_value( $value ), $template_id, $order );
    }

    /**
     * Clean output value for copying.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public static function clean_value( $value ): string {
        $value = is_scalar( $value ) ? (string) $value : '';
        $value = wp_strip_all_tags( $value );
        $value = html_entity_decode( $value, ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' );
        $value = preg_replace( "/[ \t]+\n/", "\n", $value );
        $value = preg_replace( "/\n{3,}/", "\n\n", $value );

        return trim( (string) $value );
    }

    /**
     * Read a clean custom meta value from an order.
     *
     * @param WC_Order $order Order.
     * @param string   $meta_key Meta key.
     * @return string
     */
    public static function get_meta_value( WC_Order $order, string $meta_key ): string {
        $meta_key = sanitize_key( $meta_key );

        if ( '' === $meta_key ) {
            return '';
        }

        $value = $order->get_meta( $meta_key, true );

        if ( is_array( $value ) ) {
            $value = implode( ', ', array_filter( array_map( 'strval', $value ) ) );
        }

        if ( is_object( $value ) ) {
            $value = method_exists( $value, '__toString' ) ? (string) $value : '';
        }

        /**
         * Filter a custom meta value before it is copied.
         *
         * @param mixed    $value Raw meta value.
         * @param string   $meta_key Meta key.
         * @param WC_Order $order Order object.
         */
        return self::clean_value( apply_filters( 'wuodc_custom_meta_value', $value, $meta_key, $order ) );
    }

    /**
     * Render a copy template.
     *
     * @param string   $template Template content.
     * @param WC_Order $order Order.
     * @return string
     */
    public static function render_template( string $template, WC_Order $order ): string {
        $variables = self::template_variables( $order );
        $rendered  = strtr( $template, $variables );
        $rendered  = preg_replace_callback(
            '/\{meta:([A-Za-z0-9_\-.]+)\}/',
            static function ( array $matches ) use ( $order ): string {
                return self::get_meta_value( $order, $matches[1] );
            },
            $rendered
        );
        $rendered  = is_string( $rendered ) ? $rendered : '';

        /**
         * Filter a rendered copy template.
         *
         * @param string   $rendered Rendered text.
         * @param string   $template Raw template text.
         * @param WC_Order $order Order object.
         */
        return (string) apply_filters( 'wuodc_rendered_template', self::clean_value( $rendered ), $template, $order );
    }

    /**
     * Build template variable replacements.
     *
     * @param WC_Order $order Order.
     * @return array<string,string>
     */
    public static function template_variables( WC_Order $order ): array {
        $date = $order->get_date_created();

        return array(
            '{order_id}'               => (string) $order->get_id(),
            '{order_number}'           => (string) $order->get_order_number(),
            '{order_date}'             => $date ? wc_format_datetime( $date ) : '',
            '{order_status}'           => function_exists( 'wc_get_order_status_name' ) ? wc_get_order_status_name( $order->get_status() ) : $order->get_status(),
            '{order_total}'            => self::formatted_order_total( $order ),
            '{order_items}'            => self::order_items_summary( $order ),
            '{first_name}'             => (string) $order->get_billing_first_name(),
            '{last_name}'              => (string) $order->get_billing_last_name(),
            '{full_name}'              => self::billing_full_name( $order ),
            '{email}'                  => (string) $order->get_billing_email(),
            '{phone}'                  => (string) $order->get_billing_phone(),
            '{billing_first_name}'     => (string) $order->get_billing_first_name(),
            '{billing_last_name}'      => (string) $order->get_billing_last_name(),
            '{billing_full_name}'      => self::billing_full_name( $order ),
            '{billing_company}'        => (string) $order->get_billing_company(),
            '{billing_address_1}'      => (string) $order->get_billing_address_1(),
            '{billing_address_2}'      => (string) $order->get_billing_address_2(),
            '{billing_city}'           => (string) $order->get_billing_city(),
            '{billing_state}'          => self::state_name( $order->get_billing_country(), $order->get_billing_state() ),
            '{billing_postcode}'       => (string) $order->get_billing_postcode(),
            '{billing_country}'        => self::country_name( $order->get_billing_country() ),
            '{billing_full_address}'   => self::full_address( $order, 'billing' ),
            '{shipping_first_name}'    => (string) $order->get_shipping_first_name(),
            '{shipping_last_name}'     => (string) $order->get_shipping_last_name(),
            '{shipping_full_name}'     => self::shipping_full_name( $order, true ),
            '{shipping_company}'       => self::has_shipping_address( $order ) ? (string) $order->get_shipping_company() : (string) $order->get_billing_company(),
            '{shipping_phone}'         => self::shipping_phone( $order ),
            '{shipping_address_1}'     => self::shipping_or_billing_value( $order, 'address_1' ),
            '{shipping_address_2}'     => self::shipping_or_billing_value( $order, 'address_2' ),
            '{shipping_city}'          => self::shipping_or_billing_value( $order, 'city' ),
            '{shipping_state}'         => self::has_shipping_address( $order ) ? self::state_name( $order->get_shipping_country(), $order->get_shipping_state() ) : self::state_name( $order->get_billing_country(), $order->get_billing_state() ),
            '{shipping_postcode}'      => self::shipping_or_billing_value( $order, 'postcode' ),
            '{shipping_country}'       => self::has_shipping_address( $order ) ? self::country_name( $order->get_shipping_country() ) : self::country_name( $order->get_billing_country() ),
            '{shipping_full_address}'  => self::full_address( $order, 'shipping', true ),
            '{payment_method}'         => (string) $order->get_payment_method_title(),
            '{shipping_method}'        => (string) $order->get_shipping_method(),
            '{customer_note}'          => (string) $order->get_customer_note(),
            '{order_admin_link}'       => self::order_admin_link( $order ),
        );
    }

    /**
     * Settings live-preview sample variables.
     *
     * @return array<string,string>
     */
    public static function sample_variables(): array {
        return array(
            '{order_id}'               => '12345',
            '{order_number}'           => '12345',
            '{order_date}'             => '2026-06-14 12:30',
            '{order_status}'           => __( 'Processing', 'phpvibe-order-details-copier' ),
            '{order_total}'            => '250.00 EUR',
            '{order_items}'            => "World coin x 1\nSilver medal x 2",
            '{first_name}'             => 'John',
            '{last_name}'              => 'Smith',
            '{full_name}'              => 'John Smith',
            '{email}'                  => 'john@example.com',
            '{phone}'                  => '+40722123456',
            '{billing_first_name}'     => 'John',
            '{billing_last_name}'      => 'Smith',
            '{billing_full_name}'      => 'John Smith',
            '{billing_company}'        => 'Example SRL',
            '{billing_address_1}'      => 'Str. Exemplu 12',
            '{billing_address_2}'      => 'Apt. 4',
            '{billing_city}'           => 'Craiova',
            '{billing_state}'          => 'Dolj',
            '{billing_postcode}'       => '200000',
            '{billing_country}'        => 'Romania',
            '{billing_full_address}'   => "Example SRL\nJohn Smith\nStr. Exemplu 12\nApt. 4\nCraiova, Dolj, 200000\nRomania",
            '{shipping_first_name}'    => 'John',
            '{shipping_last_name}'     => 'Smith',
            '{shipping_full_name}'     => 'John Smith',
            '{shipping_company}'       => '',
            '{shipping_phone}'         => '+40722123456',
            '{shipping_address_1}'     => 'Str. Exemplu 12',
            '{shipping_address_2}'     => 'Apt. 4',
            '{shipping_city}'          => 'Craiova',
            '{shipping_state}'         => 'Dolj',
            '{shipping_postcode}'      => '200000',
            '{shipping_country}'       => 'Romania',
            '{shipping_full_address}'  => "John Smith\nStr. Exemplu 12\nApt. 4\nCraiova, Dolj, 200000\nRomania",
            '{payment_method}'         => 'Credit card',
            '{shipping_method}'        => 'Courier',
            '{customer_note}'          => 'Please call before delivery.',
            '{order_admin_link}'       => admin_url( 'post.php?post=12345&action=edit' ),
            '{meta:_billing_cui}'       => 'RO12345678',
        );
    }

    /**
     * Build smart status badges.
     *
     * @param WC_Order $order Order.
     * @return array<int,array<string,string>>
     */
    public static function smart_badges( WC_Order $order ): array {
        $badges = array();

        if ( '' === trim( self::shipping_phone( $order ) ) && '' === trim( (string) $order->get_billing_phone() ) ) {
            $badges[] = array(
                'label' => __( 'Phone missing', 'phpvibe-order-details-copier' ),
                'type'  => 'warning',
            );
        }

        if ( ! $order->get_customer_id() ) {
            $badges[] = array(
                'label' => __( 'Guest order', 'phpvibe-order-details-copier' ),
                'type'  => 'neutral',
            );
        } elseif ( function_exists( 'wc_get_customer_order_count' ) && wc_get_customer_order_count( $order->get_customer_id() ) <= 1 ) {
            $badges[] = array(
                'label' => __( 'First order', 'phpvibe-order-details-copier' ),
                'type'  => 'info',
            );
        }

        if ( self::shipping_differs_from_billing( $order ) ) {
            $badges[] = array(
                'label' => __( 'Billing ≠ shipping', 'phpvibe-order-details-copier' ),
                'type'  => 'info',
            );
        }

        if ( '' !== trim( (string) $order->get_billing_company() ) ) {
            $badges[] = array(
                'label' => __( 'Company order', 'phpvibe-order-details-copier' ),
                'type'  => 'neutral',
            );
        }

        if ( '' !== trim( (string) $order->get_customer_note() ) ) {
            $badges[] = array(
                'label' => __( 'Customer note', 'phpvibe-order-details-copier' ),
                'type'  => 'attention',
            );
        }

        /**
         * Filter smart badges.
         *
         * @param array<int,array<string,string>> $badges Badges.
         * @param WC_Order                       $order Order object.
         */
        return apply_filters( 'wuodc_smart_badges', $badges, $order );
    }

    /**
     * WhatsApp URL.
     *
     * @param WC_Order    $order Order.
     * @param string|null $message Optional message.
     * @return string
     */
    public static function whatsapp_url( WC_Order $order, ?string $message = null ): string {
        $phone = self::whatsapp_phone( self::shipping_phone( $order ) );

        if ( '' === $phone ) {
            return '';
        }

        if ( null === $message ) {
            $message = self::template_block( 'whatsapp', $order );
        }

        return 'https://wa.me/' . rawurlencode( $phone ) . '?text=' . rawurlencode( $message );
    }

    /**
     * Normalize phone for WhatsApp links.
     *
     * @param string $phone Raw phone.
     * @return string
     */
    private static function whatsapp_phone( string $phone ): string {
        $phone = trim( $phone );
        if ( '' === $phone ) {
            return '';
        }

        $phone = preg_replace( '/[^0-9+]/', '', $phone );
        $phone = is_string( $phone ) ? $phone : '';

        if ( 0 === strpos( $phone, '00' ) ) {
            $phone = substr( $phone, 2 );
        }

        return ltrim( $phone, '+' );
    }

    /**
     * Billing full name.
     *
     * @param WC_Order $order Order.
     * @return string
     */
    public static function billing_full_name( WC_Order $order ): string {
        return trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
    }

    /**
     * Shipping full name with optional billing fallback.
     *
     * @param WC_Order $order Order.
     * @param bool     $fallback_to_billing Fallback flag.
     * @return string
     */
    public static function shipping_full_name( WC_Order $order, bool $fallback_to_billing = false ): string {
        $name = trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() );

        if ( '' === $name && $fallback_to_billing ) {
            $name = self::billing_full_name( $order );
        }

        return $name;
    }

    /**
     * Shipping phone with billing fallback.
     *
     * @param WC_Order $order Order.
     * @return string
     */
    public static function shipping_phone( WC_Order $order ): string {
        $phone = '';

        if ( method_exists( $order, 'get_shipping_phone' ) ) {
            $phone = (string) $order->get_shipping_phone();
        }

        return '' !== trim( $phone ) ? $phone : (string) $order->get_billing_phone();
    }

    /**
     * Get a shipping field with billing fallback.
     *
     * @param WC_Order $order Order.
     * @param string   $field Field suffix.
     * @return string
     */
    public static function shipping_or_billing_value( WC_Order $order, string $field ): string {
        if ( self::has_shipping_address( $order ) ) {
            $shipping_getter = 'get_shipping_' . $field;
            if ( method_exists( $order, $shipping_getter ) ) {
                return (string) $order->{$shipping_getter}();
            }
        }

        $billing_getter = 'get_billing_' . $field;
        if ( method_exists( $order, $billing_getter ) ) {
            return (string) $order->{$billing_getter}();
        }

        return '';
    }

    /**
     * Build a plain full address.
     *
     * @param WC_Order $order Order.
     * @param string   $type billing or shipping.
     * @param bool     $fallback_to_billing Fallback flag.
     * @return string
     */
    public static function full_address( WC_Order $order, string $type = 'billing', bool $fallback_to_billing = false ): string {
        if ( 'shipping' === $type && $fallback_to_billing && ! self::has_shipping_address( $order ) ) {
            $type = 'billing';
        }

        if ( 'shipping' === $type ) {
            $country = $order->get_shipping_country();
            $state   = self::state_name( $country, $order->get_shipping_state() );
            $city    = $order->get_shipping_city();
            $lines   = array(
                $order->get_shipping_company(),
                self::shipping_full_name( $order, false ),
                $order->get_shipping_address_1(),
                $order->get_shipping_address_2(),
                self::join_inline( array( $city, $state, $order->get_shipping_postcode() ) ),
                self::country_name( $country ),
            );
        } else {
            $country = $order->get_billing_country();
            $state   = self::state_name( $country, $order->get_billing_state() );
            $city    = $order->get_billing_city();
            $lines   = array(
                $order->get_billing_company(),
                self::billing_full_name( $order ),
                $order->get_billing_address_1(),
                $order->get_billing_address_2(),
                self::join_inline( array( $city, $state, $order->get_billing_postcode() ) ),
                self::country_name( $country ),
            );
        }

        return self::join_lines( $lines );
    }

    /**
     * Check if order has separate shipping address data.
     *
     * @param WC_Order $order Order.
     * @return bool
     */
    public static function has_shipping_address( WC_Order $order ): bool {
        $values = array(
            $order->get_shipping_first_name(),
            $order->get_shipping_last_name(),
            $order->get_shipping_company(),
            $order->get_shipping_address_1(),
            $order->get_shipping_address_2(),
            $order->get_shipping_city(),
            $order->get_shipping_state(),
            $order->get_shipping_postcode(),
            $order->get_shipping_country(),
        );

        return '' !== trim( implode( '', array_map( 'strval', $values ) ) );
    }

    /**
     * Whether shipping and billing addresses differ.
     *
     * @param WC_Order $order Order.
     * @return bool
     */
    public static function shipping_differs_from_billing( WC_Order $order ): bool {
        if ( ! self::has_shipping_address( $order ) ) {
            return false;
        }

        $billing = self::clean_compare(
            array(
                $order->get_billing_first_name(),
                $order->get_billing_last_name(),
                $order->get_billing_company(),
                $order->get_billing_address_1(),
                $order->get_billing_address_2(),
                $order->get_billing_city(),
                $order->get_billing_state(),
                $order->get_billing_postcode(),
                $order->get_billing_country(),
            )
        );
        $shipping = self::clean_compare(
            array(
                $order->get_shipping_first_name(),
                $order->get_shipping_last_name(),
                $order->get_shipping_company(),
                $order->get_shipping_address_1(),
                $order->get_shipping_address_2(),
                $order->get_shipping_city(),
                $order->get_shipping_state(),
                $order->get_shipping_postcode(),
                $order->get_shipping_country(),
            )
        );

        return $billing !== $shipping;
    }

    /**
     * Convert country code to country name.
     *
     * @param string $country_code Country code.
     * @return string
     */
    public static function country_name( string $country_code ): string {
        $country_code = strtoupper( trim( $country_code ) );
        if ( '' === $country_code ) {
            return '';
        }

        if ( function_exists( 'WC' ) && WC() && WC()->countries ) {
            $countries = WC()->countries->get_countries();
            return isset( $countries[ $country_code ] ) ? $countries[ $country_code ] : $country_code;
        }

        return $country_code;
    }

    /**
     * Convert state code to state name where available.
     *
     * @param string $country_code Country code.
     * @param string $state_code State code.
     * @return string
     */
    public static function state_name( string $country_code, string $state_code ): string {
        $state_code = trim( $state_code );
        if ( '' === $state_code ) {
            return '';
        }

        if ( function_exists( 'WC' ) && WC() && WC()->countries ) {
            $states = WC()->countries->get_states( strtoupper( trim( $country_code ) ) );
            if ( is_array( $states ) && isset( $states[ $state_code ] ) ) {
                return $states[ $state_code ];
            }
        }

        return $state_code;
    }

    /**
     * Build order admin link.
     *
     * @param WC_Order $order Order.
     * @return string
     */
    public static function order_admin_link( WC_Order $order ): string {
        if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\OrderUtil' ) && method_exists( '\\Automattic\\WooCommerce\\Utilities\\OrderUtil', 'custom_orders_table_usage_is_enabled' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
            return admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order->get_id() );
        }

        return get_edit_post_link( $order->get_id(), 'raw' ) ?: '';
    }

    /**
     * Formatted order total as clean text.
     *
     * @param WC_Order $order Order.
     * @return string
     */
    private static function formatted_order_total( WC_Order $order ): string {
        return self::clean_value( $order->get_formatted_order_total() );
    }

    /**
     * Shipping method line for prep blocks.
     *
     * @param WC_Order $order Order.
     * @return string
     */
    private static function shipping_method_line( WC_Order $order ): string {
        $shipping_method = trim( (string) $order->get_shipping_method() );

        if ( '' === $shipping_method ) {
            return '';
        }

        return sprintf( /* translators: %s: shipping method */ __( 'Shipping: %s', 'phpvibe-order-details-copier' ), $shipping_method );
    }

    /**
     * Order items summary.
     *
     * @param WC_Order $order Order.
     * @return string
     */
    private static function order_items_summary( WC_Order $order ): string {
        $lines = array();

        foreach ( $order->get_items() as $item ) {
            if ( ! is_object( $item ) || ! method_exists( $item, 'get_name' ) ) {
                continue;
            }

            $qty     = method_exists( $item, 'get_quantity' ) ? (float) $item->get_quantity() : 0;
            $qty_str = (string) ( ( floor( $qty ) === $qty ) ? (int) $qty : $qty );
            $lines[] = sprintf( '%s x %s', $item->get_name(), $qty_str );
        }

        return self::join_lines( $lines );
    }

    /**
     * Join tab-separated parts for spreadsheet-friendly copying.
     *
     * @param array<int,mixed> $parts Parts.
     * @return string
     */
    private static function join_tabbed( array $parts ): string {
        $parts = array_map(
            static function ( $part ): string {
                return str_replace( array( "\r", "\n", "\t" ), ' ', trim( (string) $part ) );
            },
            $parts
        );

        return implode( "\t", $parts );
    }

    /**
     * Join inline parts.
     *
     * @param array<int,mixed> $parts Parts.
     * @return string
     */
    private static function join_inline( array $parts ): string {
        $parts = array_filter(
            array_map(
                static function ( $part ): string {
                    return trim( (string) $part );
                },
                $parts
            )
        );

        return implode( ', ', $parts );
    }

    /**
     * Join lines.
     *
     * @param array<int,mixed> $lines Lines.
     * @return string
     */
    private static function join_lines( array $lines ): string {
        $lines = array_filter(
            array_map(
                static function ( $line ): string {
                    return trim( (string) $line );
                },
                $lines
            )
        );

        return implode( "\n", $lines );
    }

    /**
     * Normalize values for comparisons.
     *
     * @param array<int,mixed> $values Values.
     * @return string
     */
    private static function clean_compare( array $values ): string {
        $values = array_map(
            static function ( $value ): string {
                return strtolower( preg_replace( '/\s+/', ' ', trim( (string) $value ) ) ?: '' );
            },
            $values
        );

        return implode( '|', $values );
    }
}
