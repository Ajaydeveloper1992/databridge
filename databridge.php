<?php
/**
 * Plugin Name: DataBridge – Data Export & Migration
 * Description: Export WooCommerce customers, orders, and products to portable JSON, JSONL, and CSV files.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Author: DataBridge Contributors
 * Text Domain: databridge
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * DataBridge is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DATABRIDGE_VERSION', '0.1.0' );
define( 'DATABRIDGE_FILE', __FILE__ );
define( 'DATABRIDGE_PATH', plugin_dir_path( __FILE__ ) );

require_once DATABRIDGE_PATH . 'includes/Contracts/SourceAdapterInterface.php';
require_once DATABRIDGE_PATH . 'includes/Sources/WooCommerceSource.php';
require_once DATABRIDGE_PATH . 'includes/Exporters/ExporterInterface.php';
require_once DATABRIDGE_PATH . 'includes/Exporters/JsonExporter.php';
require_once DATABRIDGE_PATH . 'includes/Exporters/JsonlExporter.php';
require_once DATABRIDGE_PATH . 'includes/Exporters/CsvExporter.php';
require_once DATABRIDGE_PATH . 'includes/Admin.php';

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain( 'databridge', false, dirname( plugin_basename( DATABRIDGE_FILE ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function (): void {
					if ( ! current_user_can( 'activate_plugins' ) ) {
						return;
					}
					printf(
						'<div class="notice notice-warning"><p>%s</p></div>',
						esc_html__( 'DataBridge requires WooCommerce to export WooCommerce data. Activate WooCommerce to continue.', 'databridge' )
					);
				}
			);
			return;
		}

		new DataBridge\Admin();
	}
);
