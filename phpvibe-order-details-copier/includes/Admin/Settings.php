<?php
/**
 * Settings page.
 *
 * @package PHPVibe_Order_Details_Copier
 */

namespace Vibe\PHPVibe\OrderDetailsCopier\Admin;

use Vibe\PHPVibe\OrderDetailsCopier\Fields;
use Vibe\PHPVibe\OrderDetailsCopier\OrderData;
use Vibe\PHPVibe\OrderDetailsCopier\Transform;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin settings.
 */
class Settings {
    public const OPTION_NAME = 'wuodc_settings';

    /**
     * Init hooks.
     *
     * @return void
     */
    public function init(): void {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'plugin_action_links_' . WUODC_BASENAME, array( $this, 'plugin_action_links' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Default settings.
     *
     * @return array<string,mixed>
     */
    public static function defaults(): array {
        return array(
            'enabled'          => 'yes',
            'panel_title'      => __( 'Easy copy', 'phpvibe-order-details-copier' ),
            'metabox_context'  => 'normal',
            'layout_density'   => 'comfortable',
            'hide_empty'       => 'yes',
            'enable_quick_bar' => 'yes',
            'enable_order_list' => 'yes',
            'enable_smart_badges' => 'yes',
            'enable_whatsapp' => 'yes',
            'clean_copied_text' => 'yes',
            'enabled_groups'   => Fields::default_enabled_groups(),
            'enabled_fields'   => Fields::default_enabled_fields(),
            'field_order'      => Fields::default_field_order(),
            'field_labels'     => array(),
            'field_cleaners'   => array(),
            'extractors'       => self::default_extractors(),
            'custom_templates' => self::default_custom_templates(),
            'custom_meta_fields' => self::default_custom_meta_fields(),
        );
    }

    /**
     * Default custom templates.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function default_custom_templates(): array {
        return array(
            array(
                'enabled' => 'no',
                'title'   => __( 'Courier with order number', 'phpvibe-order-details-copier' ),
                'content' => "Order #{order_number}\n{shipping_full_name}\n{shipping_phone}\n{shipping_full_address}",
            ),
            array(
                'enabled' => 'no',
                'title'   => __( 'Quick support note', 'phpvibe-order-details-copier' ),
                'content' => "{full_name} - {phone} - {email}\nOrder #{order_number}",
            ),
            array(
                'enabled' => 'yes',
                'title'   => __( 'Courier handoff', 'phpvibe-order-details-copier' ),
                'content' => "{shipping_full_name}
{shipping_phone}
{shipping_address_1}
{shipping_address_2}
{shipping_city}, {shipping_state}, {shipping_postcode}
{shipping_country}
Order #{order_number}",
            ),
            array(
                'enabled' => 'yes',
                'title'   => __( 'WhatsApp delivery check', 'phpvibe-order-details-copier' ),
                'content' => __( 'Hello {first_name}, regarding your order #{order_number}, we wanted to confirm your delivery details.', 'phpvibe-order-details-copier' ),
            ),
        );
    }

    /**
     * Default derived regex fields.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function default_extractors(): array {
        $rows = array();
        for ( $i = 0; $i < 8; $i++ ) {
            $rows[] = array(
                'enabled'           => 'no',
                'label'             => '',
                'source_field'      => 'shipping_address_1',
                'pattern'           => '',
                'capture_group'     => 1,
                'trim'              => 'both',
                'remove_prefix'     => '',
                'regex_pattern'     => '',
                'regex_replacement' => '',
            );
        }
        return $rows;
    }

    /**
     * Get all settings.
     *
     * @return array<string,mixed>
     */
    /**
     * Default custom order meta fields.
     *
     * @return array<int,array<string,string>>
     */
    public static function default_custom_meta_fields(): array {
        return array(
            array(
                'enabled'  => 'no',
                'label'    => __( 'VAT / Tax ID', 'phpvibe-order-details-copier' ),
                'meta_key' => '_billing_cui',
            ),
            array(
                'enabled'  => 'no',
                'label'    => __( 'Company registration number', 'phpvibe-order-details-copier' ),
                'meta_key' => '_billing_nr_reg_com',
            ),
            array(
                'enabled'  => 'no',
                'label'    => __( 'Invoice series', 'phpvibe-order-details-copier' ),
                'meta_key' => '',
            ),
            array(
                'enabled'  => 'no',
                'label'    => __( 'Internal reference', 'phpvibe-order-details-copier' ),
                'meta_key' => '',
            ),
            array(
                'enabled'  => 'no',
                'label'    => '',
                'meta_key' => '',
            ),
            array(
                'enabled'  => 'no',
                'label'    => '',
                'meta_key' => '',
            ),
        );
    }

    public static function get_all(): array {
        $saved    = get_option( self::OPTION_NAME, array() );
        $settings = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );

        $settings['enabled']         = 'no' === $settings['enabled'] ? 'no' : 'yes';
        $settings['hide_empty']      = 'no' === $settings['hide_empty'] ? 'no' : 'yes';
        $settings['enable_quick_bar'] = 'no' === ( $settings['enable_quick_bar'] ?? 'yes' ) ? 'no' : 'yes';
        $settings['enable_order_list'] = 'no' === ( $settings['enable_order_list'] ?? 'yes' ) ? 'no' : 'yes';
        $settings['enable_smart_badges'] = 'no' === ( $settings['enable_smart_badges'] ?? 'yes' ) ? 'no' : 'yes';
        $settings['enable_whatsapp'] = 'no' === ( $settings['enable_whatsapp'] ?? 'yes' ) ? 'no' : 'yes';
        $settings['clean_copied_text'] = 'no' === ( $settings['clean_copied_text'] ?? 'yes' ) ? 'no' : 'yes';
        $settings['panel_title']     = isset( $settings['panel_title'] ) && '' !== trim( (string) $settings['panel_title'] ) ? (string) $settings['panel_title'] : __( 'Easy copy', 'phpvibe-order-details-copier' );
        $settings['metabox_context'] = in_array( $settings['metabox_context'], array( 'normal', 'side', 'advanced' ), true ) ? $settings['metabox_context'] : 'normal';
        $settings['layout_density']  = in_array( $settings['layout_density'], array( 'comfortable', 'compact' ), true ) ? $settings['layout_density'] : 'comfortable';

        $valid_groups = array_keys( Fields::groups() );
        if ( ! is_array( $settings['enabled_groups'] ) ) {
            $settings['enabled_groups'] = self::defaults()['enabled_groups'];
        }
        $settings['enabled_groups'] = array_values( array_intersect( array_map( 'sanitize_key', $settings['enabled_groups'] ), $valid_groups ) );

        $valid_fields = array_keys( Fields::definitions() );
        if ( ! is_array( $settings['enabled_fields'] ) ) {
            $settings['enabled_fields'] = self::defaults()['enabled_fields'];
        }
        $settings['enabled_fields'] = array_values( array_intersect( array_map( 'sanitize_key', $settings['enabled_fields'] ), $valid_fields ) );

        if ( ! is_array( $settings['field_order'] ) ) {
            $settings['field_order'] = self::parse_order_string( (string) $settings['field_order'] );
        }
        $settings['field_order'] = self::normalize_field_order( $settings['field_order'], $valid_fields );

        if ( ! is_array( $settings['field_labels'] ) ) {
            $settings['field_labels'] = array();
        }
        $settings['field_labels'] = self::normalize_field_labels( $settings['field_labels'], $valid_fields );

        $settings['field_cleaners'] = self::normalize_field_cleaners( $settings['field_cleaners'] ?? array(), $valid_fields );
        $settings['extractors'] = self::normalize_extractors( $settings['extractors'] ?? array(), $valid_fields );

        $settings['custom_templates'] = self::normalize_custom_templates( $settings['custom_templates'] ?? array() );
        $settings['custom_meta_fields'] = self::normalize_custom_meta_fields( $settings['custom_meta_fields'] ?? array() );

        return $settings;
    }

    /**
     * Get one setting.
     *
     * @param string $key Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public static function get( string $key, $default = null ) {
        $settings = self::get_all();
        return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
    }

    /**
     * Register the setting.
     *
     * @return void
     */
    public function register_settings(): void {
        register_setting(
            'wuodc_settings_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default'           => self::defaults(),
            )
        );
    }

    /**
     * Sanitize settings.
     *
     * @param mixed $input Input.
     * @return array<string,mixed>
     */
    public function sanitize_settings( $input ): array {
        $input = is_array( $input ) ? $input : array();

        $context = isset( $input['metabox_context'] ) ? sanitize_key( $input['metabox_context'] ) : 'normal';
        if ( ! in_array( $context, array( 'normal', 'side', 'advanced' ), true ) ) {
            $context = 'normal';
        }

        $density = isset( $input['layout_density'] ) ? sanitize_key( $input['layout_density'] ) : 'comfortable';
        if ( ! in_array( $density, array( 'comfortable', 'compact' ), true ) ) {
            $density = 'comfortable';
        }

        $valid_groups   = array_keys( Fields::groups() );
        $enabled_groups = isset( $input['enabled_groups'] ) && is_array( $input['enabled_groups'] ) ? $input['enabled_groups'] : array();
        $enabled_groups = array_values( array_intersect( array_map( 'sanitize_key', $enabled_groups ), $valid_groups ) );

        $valid_fields   = array_keys( Fields::definitions() );
        $enabled_fields = isset( $input['enabled_fields'] ) && is_array( $input['enabled_fields'] ) ? $input['enabled_fields'] : array();
        $enabled_fields = array_values( array_intersect( array_map( 'sanitize_key', $enabled_fields ), $valid_fields ) );

        $field_order = isset( $input['field_order'] ) ? $input['field_order'] : array();
        if ( is_string( $field_order ) ) {
            $field_order = self::parse_order_string( $field_order );
        }
        $field_order = is_array( $field_order ) ? $field_order : array();
        $field_order = self::normalize_field_order( $field_order, $valid_fields );

        $field_labels = isset( $input['field_labels'] ) && is_array( $input['field_labels'] ) ? $input['field_labels'] : array();
        $field_labels = self::normalize_field_labels( $field_labels, $valid_fields );
        $field_cleaners = self::normalize_field_cleaners( $input['field_cleaners'] ?? array(), $valid_fields );
        $extractors = self::normalize_extractors( $input['extractors'] ?? array(), $valid_fields );

        return array(
            'enabled'          => ! empty( $input['enabled'] ) ? 'yes' : 'no',
            'panel_title'      => isset( $input['panel_title'] ) && '' !== trim( (string) $input['panel_title'] ) ? sanitize_text_field( wp_unslash( (string) $input['panel_title'] ) ) : __( 'Easy copy', 'phpvibe-order-details-copier' ),
            'metabox_context'  => $context,
            'layout_density'   => $density,
            'hide_empty'       => ! empty( $input['hide_empty'] ) ? 'yes' : 'no',
            'enable_quick_bar' => ! empty( $input['enable_quick_bar'] ) ? 'yes' : 'no',
            'enable_order_list' => ! empty( $input['enable_order_list'] ) ? 'yes' : 'no',
            'enable_smart_badges' => ! empty( $input['enable_smart_badges'] ) ? 'yes' : 'no',
            'enable_whatsapp' => ! empty( $input['enable_whatsapp'] ) ? 'yes' : 'no',
            'clean_copied_text' => ! empty( $input['clean_copied_text'] ) ? 'yes' : 'no',
            'enabled_groups'   => $enabled_groups,
            'enabled_fields'   => $enabled_fields,
            'field_order'      => $field_order,
            'field_labels'     => $field_labels,
            'field_cleaners'   => $field_cleaners,
            'extractors'       => $extractors,
            'custom_templates' => $this->sanitize_custom_templates( $input['custom_templates'] ?? array() ),
            'custom_meta_fields' => $this->sanitize_custom_meta_fields( $input['custom_meta_fields'] ?? array() ),
        );
    }

    /**
     * Enqueue settings page assets.
     *
     * @param string $hook_suffix Current admin page hook suffix.
     * @return void
     */
    public function enqueue_assets( string $hook_suffix ): void {
        if ( 'woocommerce_page_wuodc-order-details-copier' !== $hook_suffix ) {
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
        wp_localize_script( 'wuodc-admin', 'WUODC', self::script_data() );
    }


    /**
     * Shared data for admin JavaScript.
     *
     * @return array<string,mixed>
     */
    public static function script_data(): array {
        $settings = self::get_all();

        return array(
            'copied'            => __( 'Copied', 'phpvibe-order-details-copier' ),
            'copy'              => __( 'Copy', 'phpvibe-order-details-copier' ),
            'failed'            => __( 'Could not copy', 'phpvibe-order-details-copier' ),
            'nothingSelected'   => __( 'Select one or more orders first.', 'phpvibe-order-details-copier' ),
            'nothingToCopy'     => __( 'Nothing available to copy.', 'phpvibe-order-details-copier' ),
            'bulkCopied'        => __( 'Selected orders copied', 'phpvibe-order-details-copier' ),
            'cleanCopiedText'   => 'yes' === $settings['clean_copied_text'],
            'sampleVariables'   => OrderData::sample_variables(),
        );
    }

    /**
     * Add submenu page.
     *
     * @return void
     */
    public function add_menu_page(): void {
        add_submenu_page(
            'woocommerce',
            __( 'PHPVibe Order Details Copier', 'phpvibe-order-details-copier' ),
            __( 'Order Details Copier', 'phpvibe-order-details-copier' ),
            'manage_woocommerce',
            'wuodc-order-details-copier',
            array( $this, 'render_page' )
        );
    }

    /**
     * Add settings action link.
     *
     * @param array<int,string> $links Plugin links.
     * @return array<int,string>
     */
    public function plugin_action_links( array $links ): array {
        $url = admin_url( 'admin.php?page=wuodc-order-details-copier' );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'phpvibe-order-details-copier' ) . '</a>' );

        return $links;
    }

    /**
     * Render settings page.
     *
     * @return void
     */
    public function render_page(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'phpvibe-order-details-copier' ) );
        }

        $settings       = self::get_all();
        $definitions    = Fields::definitions();
        $groups         = Fields::groups();
        $enabled_fields = $settings['enabled_fields'];
        $enabled_groups = $settings['enabled_groups'];
        $field_order    = $settings['field_order'];
        $field_labels   = $settings['field_labels'];
        $field_cleaners = $settings['field_cleaners'];
        $custom_meta_fields = $settings['custom_meta_fields'];
        ?>
        <div class="wrap wuodc-settings-wrap">
            <h1><?php echo esc_html__( 'PHPVibe Order Details Copier', 'phpvibe-order-details-copier' ); ?></h1>
            <div class="wuodc-settings-hero">
                <div>
                    <p class="wuodc-kicker"><?php echo esc_html__( 'Free PHPVibe utility', 'phpvibe-order-details-copier' ); ?></p>
                    <h2><?php echo esc_html__( 'One-click order handoff for real store work.', 'phpvibe-order-details-copier' ); ?></h2>
                    <p><?php echo esc_html__( 'Copy clean customer, address, courier, invoice, support, and order details from the order screen or orders list without selecting messy WooCommerce address blocks.', 'phpvibe-order-details-copier' ); ?></p>
                </div>
                <div class="wuodc-hero-pills" aria-label="<?php echo esc_attr__( 'Included features', 'phpvibe-order-details-copier' ); ?>">
                    <span><?php echo esc_html__( 'HPOS ready', 'phpvibe-order-details-copier' ); ?></span>
                    <span><?php echo esc_html__( 'No tracking', 'phpvibe-order-details-copier' ); ?></span>
                    <span><?php echo esc_html__( 'No external calls', 'phpvibe-order-details-copier' ); ?></span>
                    <span><?php echo esc_html__( 'Translation ready', 'phpvibe-order-details-copier' ); ?></span>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'wuodc_settings_group' ); ?>

                <div class="wuodc-settings-card">
                    <h2><?php echo esc_html__( 'General', 'phpvibe-order-details-copier' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php echo esc_html__( 'Enable Easy Copy panel', 'phpvibe-order-details-copier' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]" value="1" <?php checked( $settings['enabled'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Show the panel on WooCommerce order edit screens.', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__( 'Panel title', 'phpvibe-order-details-copier' ); ?></th>
                                <td>
                                    <input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[panel_title]" value="<?php echo esc_attr( $settings['panel_title'] ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__( 'Panel location', 'phpvibe-order-details-copier' ); ?></th>
                                <td>
                                    <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[metabox_context]">
                                        <option value="normal" <?php selected( $settings['metabox_context'], 'normal' ); ?>><?php echo esc_html__( 'Main column', 'phpvibe-order-details-copier' ); ?></option>
                                        <option value="side" <?php selected( $settings['metabox_context'], 'side' ); ?>><?php echo esc_html__( 'Right sidebar', 'phpvibe-order-details-copier' ); ?></option>
                                        <option value="advanced" <?php selected( $settings['metabox_context'], 'advanced' ); ?>><?php echo esc_html__( 'Advanced section', 'phpvibe-order-details-copier' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__( 'Panel density', 'phpvibe-order-details-copier' ); ?></th>
                                <td>
                                    <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[layout_density]">
                                        <option value="comfortable" <?php selected( $settings['layout_density'], 'comfortable' ); ?>><?php echo esc_html__( 'Comfortable', 'phpvibe-order-details-copier' ); ?></option>
                                        <option value="compact" <?php selected( $settings['layout_density'], 'compact' ); ?>><?php echo esc_html__( 'Compact', 'phpvibe-order-details-copier' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__( 'Empty details', 'phpvibe-order-details-copier' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hide_empty]" value="1" <?php checked( $settings['hide_empty'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Hide fields that are empty for the current order.', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__( 'Wow features', 'phpvibe-order-details-copier' ); ?></th>
                                <td class="wuodc-checkbox-stack">
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_quick_bar]" value="1" <?php checked( $settings['enable_quick_bar'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Show the Quick Copy bar on the order screen.', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_order_list]" value="1" <?php checked( $settings['enable_order_list'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Add copy dropdowns and bulk copy tools to the orders list.', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_smart_badges]" value="1" <?php checked( $settings['enable_smart_badges'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Show smart badges such as guest order, missing phone, different shipping, and customer note.', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_whatsapp]" value="1" <?php checked( $settings['enable_whatsapp'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Show Open WhatsApp actions when a phone number is available.', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[clean_copied_text]" value="1" <?php checked( $settings['clean_copied_text'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Clean copied text in the browser before it reaches the clipboard.', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wuodc-settings-card">
                    <h2><?php echo esc_html__( 'Groups', 'phpvibe-order-details-copier' ); ?></h2>
                    <p><?php echo esc_html__( 'Disable a full group when you do not want it displayed in the order panel.', 'phpvibe-order-details-copier' ); ?></p>
                    <div class="wuodc-settings-groups">
                        <?php foreach ( $groups as $group_id => $group_label ) : ?>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled_groups][]" value="<?php echo esc_attr( $group_id ); ?>" <?php checked( in_array( $group_id, $enabled_groups, true ) ); ?>>
                                <?php echo esc_html( $group_label ); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="wuodc-settings-card">
                    <div class="wuodc-settings-card-heading">
                        <div>
                            <h2><?php echo esc_html__( 'Copyable details', 'phpvibe-order-details-copier' ); ?></h2>
                            <p><?php echo esc_html__( 'Enable, rename, and drag fields to change their order. Every enabled detail keeps its own copy-to-clipboard button.', 'phpvibe-order-details-copier' ); ?></p>
                        </div>
                        <div class="wuodc-field-toggle-actions" data-wuodc-field-toggle-actions>
                            <button type="button" class="button" data-wuodc-fields-enable><?php echo esc_html__( 'Enable all', 'phpvibe-order-details-copier' ); ?></button>
                            <button type="button" class="button" data-wuodc-fields-disable><?php echo esc_html__( 'Disable all', 'phpvibe-order-details-copier' ); ?></button>
                        </div>
                    </div>
                    <input type="hidden" class="wuodc-field-order-input" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[field_order]" value="<?php echo esc_attr( implode( ',', $field_order ) ); ?>">

                    <div class="wuodc-sort-list" data-wuodc-sort-list>
                        <?php foreach ( $field_order as $field_id ) : ?>
                            <?php
                            if ( ! isset( $definitions[ $field_id ] ) ) {
                                continue;
                            }
                            $field       = $definitions[ $field_id ];
                            $group_id    = (string) ( $field['group'] ?? 'customer' );
                            $group_label = $groups[ $group_id ] ?? $group_id;
                            $label       = (string) ( $field['label'] ?? $field_id );
                            $custom      = $field_labels[ $field_id ] ?? '';
                            ?>
                            <div class="wuodc-sort-item" draggable="true" data-field-id="<?php echo esc_attr( $field_id ); ?>">
                                <span class="dashicons dashicons-menu wuodc-sort-handle" aria-hidden="true"></span>
                                <label class="wuodc-sort-enabled">
                                    <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled_fields][]" value="<?php echo esc_attr( $field_id ); ?>" <?php checked( in_array( $field_id, $enabled_fields, true ) ); ?>>
                                    <?php echo esc_html__( 'Enabled', 'phpvibe-order-details-copier' ); ?>
                                </label>
                                <div class="wuodc-sort-main">
                                    <strong><?php echo esc_html( $label ); ?></strong>
                                    <span><?php echo esc_html( $group_label ); ?> · <code><?php echo esc_html( $field_id ); ?></code></span>
                                </div>
                                <input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[field_labels][<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $custom ); ?>" placeholder="<?php echo esc_attr( $label ); ?>">
                                <?php $cleaner = $field_cleaners[ $field_id ] ?? array(); ?>
                                <details class="wuodc-cleaner-details">
                                    <summary><?php echo esc_html__( 'Text cleaner', 'phpvibe-order-details-copier' ); ?></summary>
                                    <div class="wuodc-cleaner-grid">
                                        <label><?php echo esc_html__( 'Trim', 'phpvibe-order-details-copier' ); ?>
                                            <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[field_cleaners][<?php echo esc_attr( $field_id ); ?>][trim]">
                                                <option value="none" <?php selected( $cleaner['trim'] ?? 'none', 'none' ); ?>><?php echo esc_html__( 'None', 'phpvibe-order-details-copier' ); ?></option>
                                                <option value="left" <?php selected( $cleaner['trim'] ?? '', 'left' ); ?>><?php echo esc_html__( 'Left trim', 'phpvibe-order-details-copier' ); ?></option>
                                                <option value="right" <?php selected( $cleaner['trim'] ?? '', 'right' ); ?>><?php echo esc_html__( 'Right trim', 'phpvibe-order-details-copier' ); ?></option>
                                                <option value="both" <?php selected( $cleaner['trim'] ?? '', 'both' ); ?>><?php echo esc_html__( 'Trim both', 'phpvibe-order-details-copier' ); ?></option>
                                            </select>
                                        </label>
                                        <label><?php echo esc_html__( 'Remove leading prefix', 'phpvibe-order-details-copier' ); ?>
                                            <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[field_cleaners][<?php echo esc_attr( $field_id ); ?>][remove_prefix]" value="<?php echo esc_attr( $cleaner['remove_prefix'] ?? '' ); ?>" placeholder="+4">
                                        </label>
                                        <label><?php echo esc_html__( 'Regex find', 'phpvibe-order-details-copier' ); ?>
                                            <input type="text" class="code" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[field_cleaners][<?php echo esc_attr( $field_id ); ?>][regex_pattern]" value="<?php echo esc_attr( $cleaner['regex_pattern'] ?? '' ); ?>" placeholder="/[^0-9]/">
                                        </label>
                                        <label><?php echo esc_html__( 'Regex replace', 'phpvibe-order-details-copier' ); ?>
                                            <input type="text" class="code" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[field_cleaners][<?php echo esc_attr( $field_id ); ?>][regex_replacement]" value="<?php echo esc_attr( $cleaner['regex_replacement'] ?? '' ); ?>">
                                        </label>
                                    </div>
                                </details>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="wuodc-settings-card wuodc-derived-fields-card">
                    <div class="wuodc-derived-fields-intro">
                        <div>
                            <span class="wuodc-settings-kicker"><?php echo esc_html__( 'Field builder', 'phpvibe-order-details-copier' ); ?></span>
                            <h2><?php echo esc_html__( 'Derived copy fields', 'phpvibe-order-details-copier' ); ?></h2>
                            <p><?php echo esc_html__( 'Build additional copy boxes by extracting part of an existing order field. This is where you can split an address into Street and Street Number.', 'phpvibe-order-details-copier' ); ?></p>
                        </div>
                        <button type="button" class="button button-secondary" data-wuodc-street-example><?php echo esc_html__( 'Load street + number example', 'phpvibe-order-details-copier' ); ?></button>
                    </div>
                    <div class="wuodc-extractor-list" data-wuodc-extractor-list>
                        <?php foreach ( $settings['extractors'] as $index => $extractor ) : ?>
                            <div class="wuodc-extractor-row" data-wuodc-extractor-row="<?php echo esc_attr( (string) $index ); ?>">
                                <div class="wuodc-extractor-row-head">
                                    <strong><?php echo esc_html( sprintf( __( 'Derived field %d', 'phpvibe-order-details-copier' ), $index + 1 ) ); ?></strong>
                                    <label class="wuodc-extractor-enabled"><input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[extractors][<?php echo esc_attr( (string) $index ); ?>][enabled]" value="1" <?php checked( $extractor['enabled'], 'yes' ); ?>> <?php echo esc_html__( 'Enable this field', 'phpvibe-order-details-copier' ); ?></label>
                                </div>
                                <div class="wuodc-extractor-grid">
                                    <label><?php echo esc_html__( 'Copy box label', 'phpvibe-order-details-copier' ); ?><input type="text" data-wuodc-extractor-label name="<?php echo esc_attr( self::OPTION_NAME ); ?>[extractors][<?php echo esc_attr( (string) $index ); ?>][label]" value="<?php echo esc_attr( $extractor['label'] ); ?>" placeholder="Street"></label>
                                    <label><?php echo esc_html__( 'Read value from', 'phpvibe-order-details-copier' ); ?><select data-wuodc-extractor-source name="<?php echo esc_attr( self::OPTION_NAME ); ?>[extractors][<?php echo esc_attr( (string) $index ); ?>][source_field]">
                                        <?php foreach ( $definitions as $source_id => $source_definition ) : ?><option value="<?php echo esc_attr( $source_id ); ?>" <?php selected( $extractor['source_field'], $source_id ); ?>><?php echo esc_html( $source_definition['label'] ?? $source_id ); ?></option><?php endforeach; ?>
                                    </select></label>
                                    <label class="wuodc-extractor-pattern"><?php echo esc_html__( 'Matching rule (regular expression)', 'phpvibe-order-details-copier' ); ?><input type="text" data-wuodc-extractor-pattern class="code" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[extractors][<?php echo esc_attr( (string) $index ); ?>][pattern]" value="<?php echo esc_attr( $extractor['pattern'] ); ?>" placeholder="/^(.*?)\s+(?:no\.?|nr\.?|#)\s*(\d+)$/iu"></label>
                                    <label><?php echo esc_html__( 'Use captured part', 'phpvibe-order-details-copier' ); ?><input type="number" data-wuodc-extractor-group min="0" max="99" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[extractors][<?php echo esc_attr( (string) $index ); ?>][capture_group]" value="<?php echo esc_attr( (string) $extractor['capture_group'] ); ?>"><span class="description"><?php echo esc_html__( 'Usually 1, 2, 3…', 'phpvibe-order-details-copier' ); ?></span></label>
                                    <label><?php echo esc_html__( 'Trim extracted value', 'phpvibe-order-details-copier' ); ?><select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[extractors][<?php echo esc_attr( (string) $index ); ?>][trim]"><option value="none" <?php selected( $extractor['trim'], 'none' ); ?>><?php echo esc_html__( 'None', 'phpvibe-order-details-copier' ); ?></option><option value="left" <?php selected( $extractor['trim'], 'left' ); ?>><?php echo esc_html__( 'Left', 'phpvibe-order-details-copier' ); ?></option><option value="right" <?php selected( $extractor['trim'], 'right' ); ?>><?php echo esc_html__( 'Right', 'phpvibe-order-details-copier' ); ?></option><option value="both" <?php selected( $extractor['trim'], 'both' ); ?>><?php echo esc_html__( 'Both sides', 'phpvibe-order-details-copier' ); ?></option></select></label>
                                    <label><?php echo esc_html__( 'Remove leading prefix', 'phpvibe-order-details-copier' ); ?><input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[extractors][<?php echo esc_attr( (string) $index ); ?>][remove_prefix]" value="<?php echo esc_attr( $extractor['remove_prefix'] ); ?>" placeholder="+4"></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="wuodc-derived-help"><strong><?php echo esc_html__( 'How the street example works:', 'phpvibe-order-details-copier' ); ?></strong> <?php echo esc_html__( 'The first generated field uses capture 1 for the street text. The second uses capture 2 for the house number. Both read Shipping address line 1.', 'phpvibe-order-details-copier' ); ?></div>
                </div>

                <div class="wuodc-settings-card">
                    <h2><?php echo esc_html__( 'Custom copy templates', 'phpvibe-order-details-copier' ); ?></h2>
                    <p><?php echo esc_html__( 'Create admin-defined blocks for courier labels, WhatsApp messages, support notes, spreadsheets, or invoice workflows.', 'phpvibe-order-details-copier' ); ?></p>

                    <div class="wuodc-template-grid">
                        <?php foreach ( $settings['custom_templates'] as $index => $template ) : ?>
                            <div class="postbox wuodc-template-box">
                                <div class="inside">
                                    <label class="wuodc-template-enabled">
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_templates][<?php echo esc_attr( (string) $index ); ?>][enabled]" value="1" <?php checked( $template['enabled'], 'yes' ); ?>>
                                        <?php echo esc_html__( 'Enable this template', 'phpvibe-order-details-copier' ); ?>
                                    </label>
                                    <p>
                                        <label>
                                            <?php echo esc_html__( 'Template title', 'phpvibe-order-details-copier' ); ?><br>
                                            <input type="text" class="widefat" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_templates][<?php echo esc_attr( (string) $index ); ?>][title]" value="<?php echo esc_attr( $template['title'] ); ?>">
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            <?php echo esc_html__( 'Template content', 'phpvibe-order-details-copier' ); ?><br>
                                            <textarea class="widefat code wuodc-template-content" data-wuodc-template-content rows="5" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_templates][<?php echo esc_attr( (string) $index ); ?>][content]"><?php echo esc_textarea( $template['content'] ); ?></textarea>
                                        </label>
                                    </p>
                                    <div class="wuodc-template-preview">
                                        <strong><?php echo esc_html__( 'Live preview', 'phpvibe-order-details-copier' ); ?></strong>
                                        <pre class="wuodc-template-preview-code" aria-live="polite"></pre>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <details class="wuodc-variables" open>
                        <summary><?php echo esc_html__( 'Available variables', 'phpvibe-order-details-copier' ); ?></summary>
                        <p><?php echo esc_html__( 'Use these placeholders inside custom template content:', 'phpvibe-order-details-copier' ); ?></p>
                        <div class="wuodc-variable-list">
                            <?php foreach ( self::variable_reference() as $variable ) : ?>
                                <button type="button" class="wuodc-variable-chip" data-wuodc-variable="<?php echo esc_attr( $variable ); ?>"><code><?php echo esc_html( $variable ); ?></code></button>
                            <?php endforeach; ?>
                        </div>
                    </details>
                </div>

                <div class="wuodc-settings-card">
                    <h2><?php echo esc_html__( 'Custom order meta fields', 'phpvibe-order-details-copier' ); ?></h2>
                    <p><?php echo esc_html__( 'Expose extra order meta from invoice, tax, ERP, auction, courier, or checkout-field plugins as copyable rows. Leave unused rows disabled.', 'phpvibe-order-details-copier' ); ?></p>
                    <p class="description">
                        <?php echo esc_html__( 'Template tip: custom templates can also use dynamic meta variables such as {meta:_billing_cui}.', 'phpvibe-order-details-copier' ); ?>
                    </p>

                    <div class="wuodc-meta-list">
                        <?php foreach ( $custom_meta_fields as $index => $meta_field ) : ?>
                            <div class="wuodc-meta-row">
                                <label class="wuodc-meta-enabled">
                                    <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_meta_fields][<?php echo esc_attr( (string) $index ); ?>][enabled]" value="1" <?php checked( $meta_field['enabled'], 'yes' ); ?>>
                                    <?php echo esc_html__( 'Enable', 'phpvibe-order-details-copier' ); ?>
                                </label>
                                <label>
                                    <span><?php echo esc_html__( 'Label', 'phpvibe-order-details-copier' ); ?></span>
                                    <input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_meta_fields][<?php echo esc_attr( (string) $index ); ?>][label]" value="<?php echo esc_attr( $meta_field['label'] ); ?>" placeholder="<?php echo esc_attr__( 'VAT / Tax ID', 'phpvibe-order-details-copier' ); ?>">
                                </label>
                                <label>
                                    <span><?php echo esc_html__( 'Order meta key', 'phpvibe-order-details-copier' ); ?></span>
                                    <input type="text" class="regular-text code" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_meta_fields][<?php echo esc_attr( (string) $index ); ?>][meta_key]" value="<?php echo esc_attr( $meta_field['meta_key'] ); ?>" placeholder="<?php echo esc_attr__( '_billing_cui', 'phpvibe-order-details-copier' ); ?>">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="wuodc-settings-card wuodc-privacy-card">
                    <h2><?php echo esc_html__( 'Privacy and safety', 'phpvibe-order-details-copier' ); ?></h2>
                    <p><?php echo esc_html__( 'This plugin only reads order data inside wp-admin and copies it to the current admin user clipboard. It does not send customer data to PHPVibe, does not call external APIs, and does not create logs.', 'phpvibe-order-details-copier' ); ?></p>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Parse a comma-separated order string.
     *
     * @param string $order_string Order string.
     * @return array<int,string>
     */
    private static function parse_order_string( string $order_string ): array {
        $parts = preg_split( '/[\s,]+/', $order_string );
        return is_array( $parts ) ? array_values( array_filter( array_map( 'sanitize_key', $parts ) ) ) : array();
    }

    /**
     * Normalize field order.
     *
     * @param array<int,mixed> $field_order Field order.
     * @param array<int,string> $valid_fields Valid fields.
     * @return array<int,string>
     */
    private static function normalize_field_order( array $field_order, array $valid_fields ): array {
        $field_order = array_values( array_unique( array_intersect( array_map( 'sanitize_key', $field_order ), $valid_fields ) ) );

        foreach ( $valid_fields as $field_id ) {
            if ( ! in_array( $field_id, $field_order, true ) ) {
                $field_order[] = $field_id;
            }
        }

        return $field_order;
    }

    /**
     * Normalize custom labels.
     *
     * @param array<mixed,mixed> $labels Labels.
     * @param array<int,string> $valid_fields Valid fields.
     * @return array<string,string>
     */
    private static function normalize_field_labels( array $labels, array $valid_fields ): array {
        $normalized = array();

        foreach ( $valid_fields as $field_id ) {
            if ( ! isset( $labels[ $field_id ] ) ) {
                continue;
            }

            $label = sanitize_text_field( wp_unslash( (string) $labels[ $field_id ] ) );
            if ( '' !== $label ) {
                $normalized[ $field_id ] = $label;
            }
        }

        return $normalized;
    }

    /**
     * Sanitize custom templates.
     *
     * @param mixed $templates Templates.
     * @return array<int,array<string,string>>
     */
    private static function normalize_field_cleaners( $rules, array $valid_fields ): array {
        $rules = is_array( $rules ) ? $rules : array();
        $output = array();
        foreach ( $valid_fields as $field_id ) {
            $rule = isset( $rules[ $field_id ] ) && is_array( $rules[ $field_id ] ) ? $rules[ $field_id ] : array();
            $trim = isset( $rule['trim'] ) ? sanitize_key( $rule['trim'] ) : 'none';
            if ( ! in_array( $trim, array( 'none', 'left', 'right', 'both' ), true ) ) { $trim = 'none'; }
            $pattern = isset( $rule['regex_pattern'] ) ? trim( wp_unslash( (string) $rule['regex_pattern'] ) ) : '';
            if ( '' !== $pattern && ! Transform::is_valid_pattern( $pattern ) ) { $pattern = ''; }
            $clean = array(
                'trim' => $trim,
                'remove_prefix' => isset( $rule['remove_prefix'] ) ? sanitize_text_field( wp_unslash( (string) $rule['remove_prefix'] ) ) : '',
                'regex_pattern' => $pattern,
                'regex_replacement' => isset( $rule['regex_replacement'] ) ? sanitize_text_field( wp_unslash( (string) $rule['regex_replacement'] ) ) : '',
            );
            if ( 'none' !== $clean['trim'] || '' !== $clean['remove_prefix'] || '' !== $clean['regex_pattern'] || '' !== $clean['regex_replacement'] ) { $output[ $field_id ] = $clean; }
        }
        return $output;
    }

    private static function normalize_extractors( $rules, array $valid_fields ): array {
        $rules = is_array( $rules ) ? $rules : array();
        $defaults = self::default_extractors();
        $output = array();
        for ( $i = 0; $i < 8; $i++ ) {
            $rule = isset( $rules[ $i ] ) && is_array( $rules[ $i ] ) ? $rules[ $i ] : ( $defaults[ $i ] ?? array() );
            $source = isset( $rule['source_field'] ) ? sanitize_key( $rule['source_field'] ) : 'shipping_address_1';
            if ( ! in_array( $source, $valid_fields, true ) ) { $source = 'shipping_address_1'; }
            $pattern = isset( $rule['pattern'] ) ? trim( wp_unslash( (string) $rule['pattern'] ) ) : '';
            if ( '' !== $pattern && ! Transform::is_valid_pattern( $pattern ) ) { $pattern = ''; }
            $trim = isset( $rule['trim'] ) ? sanitize_key( $rule['trim'] ) : 'both';
            if ( ! in_array( $trim, array( 'none', 'left', 'right', 'both' ), true ) ) { $trim = 'both'; }
            $output[] = array(
                'enabled' => ! empty( $rule['enabled'] ) && 'no' !== $rule['enabled'] ? 'yes' : 'no',
                'label' => isset( $rule['label'] ) ? sanitize_text_field( wp_unslash( (string) $rule['label'] ) ) : '',
                'source_field' => $source,
                'pattern' => $pattern,
                'capture_group' => isset( $rule['capture_group'] ) ? min( 99, max( 0, absint( $rule['capture_group'] ) ) ) : 1,
                'trim' => $trim,
                'remove_prefix' => isset( $rule['remove_prefix'] ) ? sanitize_text_field( wp_unslash( (string) $rule['remove_prefix'] ) ) : '',
                'regex_pattern' => '',
                'regex_replacement' => '',
            );
        }
        return $output;
    }

    private function sanitize_custom_templates( $templates ): array {
        return self::normalize_custom_templates( $templates );
    }

    /**
     * Normalize custom templates.
     *
     * @param mixed $templates Templates.
     * @return array<int,array<string,string>>
     */
    private static function normalize_custom_templates( $templates ): array {
        $defaults = self::default_custom_templates();
        $input    = is_array( $templates ) ? $templates : array();
        $output   = array();

        for ( $i = 0; $i < 4; $i++ ) {
            $template = isset( $input[ $i ] ) && is_array( $input[ $i ] ) ? $input[ $i ] : ( $defaults[ $i ] ?? array() );
            $title    = isset( $template['title'] ) ? sanitize_text_field( wp_unslash( (string) $template['title'] ) ) : '';
            $content  = isset( $template['content'] ) ? sanitize_textarea_field( wp_unslash( (string) $template['content'] ) ) : '';

            $output[] = array(
                'enabled' => ! empty( $template['enabled'] ) && 'no' !== $template['enabled'] ? 'yes' : 'no',
                'title'   => $title,
                'content' => $content,
            );
        }

        return $output;
    }

    /**
     * Sanitize custom meta rows.
     *
     * @param mixed $fields Custom meta rows.
     * @return array<int,array<string,string>>
     */
    private function sanitize_custom_meta_fields( $fields ): array {
        return self::normalize_custom_meta_fields( $fields );
    }

    /**
     * Normalize custom meta rows.
     *
     * @param mixed $fields Custom meta rows.
     * @return array<int,array<string,string>>
     */
    private static function normalize_custom_meta_fields( $fields ): array {
        $defaults = self::default_custom_meta_fields();
        $input    = is_array( $fields ) ? $fields : array();
        $output   = array();

        for ( $i = 0; $i < 6; $i++ ) {
            $field = isset( $input[ $i ] ) && is_array( $input[ $i ] ) ? $input[ $i ] : ( $defaults[ $i ] ?? array() );
            $label = isset( $field['label'] ) ? sanitize_text_field( wp_unslash( (string) $field['label'] ) ) : '';
            $key   = isset( $field['meta_key'] ) ? sanitize_key( wp_unslash( (string) $field['meta_key'] ) ) : '';

            $output[] = array(
                'enabled'  => ! empty( $field['enabled'] ) && 'no' !== $field['enabled'] ? 'yes' : 'no',
                'label'    => $label,
                'meta_key' => $key,
            );
        }

        return $output;
    }

    /**
     * Variables shown in settings.
     *
     * @return array<int,string>
     */
    private static function variable_reference(): array {
        return array(
            '{order_id}',
            '{order_number}',
            '{order_date}',
            '{order_status}',
            '{order_total}',
            '{order_items}',
            '{first_name}',
            '{last_name}',
            '{full_name}',
            '{email}',
            '{phone}',
            '{billing_company}',
            '{billing_address_1}',
            '{billing_address_2}',
            '{billing_city}',
            '{billing_state}',
            '{billing_postcode}',
            '{billing_country}',
            '{billing_full_address}',
            '{shipping_full_name}',
            '{shipping_company}',
            '{shipping_phone}',
            '{shipping_address_1}',
            '{shipping_address_2}',
            '{shipping_city}',
            '{shipping_state}',
            '{shipping_postcode}',
            '{shipping_country}',
            '{shipping_full_address}',
            '{payment_method}',
            '{shipping_method}',
            '{customer_note}',
            '{order_admin_link}',
            '{meta:_billing_cui}',
        );
    }
}
