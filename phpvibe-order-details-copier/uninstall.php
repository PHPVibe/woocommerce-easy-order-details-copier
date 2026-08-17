<?php
/**
 * Uninstall cleanup.
 *
 * @package WooUseful_Order_Details_Copier
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'wuodc_settings' );
