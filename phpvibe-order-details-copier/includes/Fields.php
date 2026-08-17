<?php
/**
 * Field definitions for the order details copier.
 *
 * @package PHPVibe_Order_Details_Copier
 */

namespace Vibe\PHPVibe\OrderDetailsCopier;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Central field registry.
 */
final class Fields {
    /**
     * Get field group labels.
     *
     * @return array<string,string>
     */
    public static function groups(): array {
        $groups = array(
            'customer'         => __( 'Customer', 'phpvibe-order-details-copier' ),
            'billing'          => __( 'Billing', 'phpvibe-order-details-copier' ),
            'shipping'         => __( 'Shipping', 'phpvibe-order-details-copier' ),
            'order'            => __( 'Order', 'phpvibe-order-details-copier' ),
            'templates'        => __( 'Copy blocks', 'phpvibe-order-details-copier' ),
            'custom_templates' => __( 'Custom copy blocks', 'phpvibe-order-details-copier' ),
            'custom_meta'      => __( 'Custom meta details', 'phpvibe-order-details-copier' ),
        );

        /**
         * Filter copyable field groups.
         *
         * @param array<string,string> $groups Field groups.
         */
        return apply_filters( 'wuodc_field_groups', $groups );
    }

    /**
     * Get field definitions.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function definitions(): array {
        $definitions = array(
            'billing_first_name' => array(
                'label' => __( 'First name', 'phpvibe-order-details-copier' ),
                'group' => 'customer',
            ),
            'billing_last_name'  => array(
                'label' => __( 'Last name', 'phpvibe-order-details-copier' ),
                'group' => 'customer',
            ),
            'billing_full_name'  => array(
                'label' => __( 'Full name', 'phpvibe-order-details-copier' ),
                'group' => 'customer',
            ),
            'billing_email'      => array(
                'label' => __( 'Email', 'phpvibe-order-details-copier' ),
                'group' => 'customer',
            ),
            'billing_phone'      => array(
                'label' => __( 'Phone', 'phpvibe-order-details-copier' ),
                'group' => 'customer',
            ),

            'billing_company'      => array(
                'label' => __( 'Billing company', 'phpvibe-order-details-copier' ),
                'group' => 'billing',
            ),
            'billing_address_1'    => array(
                'label' => __( 'Billing address 1', 'phpvibe-order-details-copier' ),
                'group' => 'billing',
            ),
            'billing_address_2'    => array(
                'label' => __( 'Billing address 2', 'phpvibe-order-details-copier' ),
                'group' => 'billing',
            ),
            'billing_city'         => array(
                'label' => __( 'Billing city', 'phpvibe-order-details-copier' ),
                'group' => 'billing',
            ),
            'billing_state'        => array(
                'label' => __( 'Billing county/state', 'phpvibe-order-details-copier' ),
                'group' => 'billing',
            ),
            'billing_postcode'     => array(
                'label' => __( 'Billing postcode', 'phpvibe-order-details-copier' ),
                'group' => 'billing',
            ),
            'billing_country'      => array(
                'label' => __( 'Billing country', 'phpvibe-order-details-copier' ),
                'group' => 'billing',
            ),
            'billing_full_address' => array(
                'label'     => __( 'Full billing address', 'phpvibe-order-details-copier' ),
                'group'     => 'billing',
                'multiline' => true,
            ),
            'billing_all_details'  => array(
                'label'     => __( 'All billing details', 'phpvibe-order-details-copier' ),
                'group'     => 'billing',
                'multiline' => true,
            ),

            'shipping_first_name'   => array(
                'label' => __( 'Shipping first name', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_last_name'    => array(
                'label' => __( 'Shipping last name', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_full_name'    => array(
                'label' => __( 'Shipping full name', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_company'      => array(
                'label' => __( 'Shipping company', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_phone'        => array(
                'label' => __( 'Shipping phone', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_address_1'    => array(
                'label' => __( 'Shipping address 1', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_address_2'    => array(
                'label' => __( 'Shipping address 2', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_city'         => array(
                'label' => __( 'Shipping city', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_state'        => array(
                'label' => __( 'Shipping county/state', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_postcode'     => array(
                'label' => __( 'Shipping postcode', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_country'      => array(
                'label' => __( 'Shipping country', 'phpvibe-order-details-copier' ),
                'group' => 'shipping',
            ),
            'shipping_full_address' => array(
                'label'     => __( 'Full shipping address', 'phpvibe-order-details-copier' ),
                'group'     => 'shipping',
                'multiline' => true,
            ),
            'shipping_all_details'  => array(
                'label'     => __( 'All shipping details', 'phpvibe-order-details-copier' ),
                'group'     => 'shipping',
                'multiline' => true,
            ),

            'order_id'         => array(
                'label' => __( 'Order ID', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),
            'order_number'     => array(
                'label' => __( 'Order number', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),
            'order_date'       => array(
                'label' => __( 'Order date', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),
            'order_status'     => array(
                'label' => __( 'Order status', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),
            'order_total'      => array(
                'label' => __( 'Order total', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),
            'payment_method'   => array(
                'label' => __( 'Payment method', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),
            'shipping_method'  => array(
                'label' => __( 'Shipping method', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),
            'order_items'      => array(
                'label'     => __( 'Order items', 'phpvibe-order-details-copier' ),
                'group'     => 'order',
                'multiline' => true,
            ),
            'customer_note'    => array(
                'label'     => __( 'Customer note', 'phpvibe-order-details-copier' ),
                'group'     => 'order',
                'multiline' => true,
            ),
            'order_admin_link' => array(
                'label' => __( 'Order admin link', 'phpvibe-order-details-copier' ),
                'group' => 'order',
            ),

            'customer_contact_block' => array(
                'label'     => __( 'Customer contact block', 'phpvibe-order-details-copier' ),
                'group'     => 'templates',
                'multiline' => true,
            ),
            'courier_block'          => array(
                'label'     => __( 'Courier block', 'phpvibe-order-details-copier' ),
                'group'     => 'templates',
                'multiline' => true,
            ),
            'invoice_block'          => array(
                'label'     => __( 'Invoice/client block', 'phpvibe-order-details-copier' ),
                'group'     => 'templates',
                'multiline' => true,
            ),
            'packing_block'          => array(
                'label'     => __( 'Packing/prep block', 'phpvibe-order-details-copier' ),
                'group'     => 'templates',
                'multiline' => true,
            ),
            'spreadsheet_row'        => array(
                'label'     => __( 'Spreadsheet row', 'phpvibe-order-details-copier' ),
                'group'     => 'templates',
                'multiline' => true,
            ),
            'whatsapp_block'         => array(
                'label'     => __( 'WhatsApp/contact block', 'phpvibe-order-details-copier' ),
                'group'     => 'templates',
                'multiline' => true,
            ),
        );

        /**
         * Filter copyable field definitions.
         *
         * @param array<string,array<string,mixed>> $definitions Field definitions.
         */
        return apply_filters( 'wuodc_field_definitions', $definitions );
    }

    /**
     * Fields enabled by default.
     *
     * @return array<int,string>
     */
    public static function default_enabled_fields(): array {
        return array_keys( self::definitions() );
    }

    /**
     * Groups enabled by default.
     *
     * @return array<int,string>
     */
    public static function default_enabled_groups(): array {
        return array_keys( self::groups() );
    }

    /**
     * Default field order.
     *
     * @return array<int,string>
     */
    public static function default_field_order(): array {
        return array_keys( self::definitions() );
    }
}
