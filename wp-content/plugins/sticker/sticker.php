<?php
/**
 * Plugin Name: sticker
 * Description: Automatically discounts non-sale products tagged sticker by 10% when five eligible units are in the cart.
 * Version: 1.0.0
 * Author: ghanshyam saini
 * Requires at least: 7.0
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 * WC requires at least: 11.2
 * WC tested up to: 11.2.1
 * License: GPL-2.0-or-later
 * Text Domain: sticker
 *
 * @package Sticker
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		if ( class_exists( 'WooCommerce' ) ) {
			require_once __DIR__ . '/includes/class-sticker-discount.php';
			\Sticker\Discount::init();
		}
	}
);
