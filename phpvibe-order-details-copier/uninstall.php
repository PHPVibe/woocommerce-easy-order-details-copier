<?php
/**
 * Uninstall cleanup.
 *
 * @package PHPVibe_Order_Details_Copier
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'wuodc_settings' );
