<?php
/**
 * Native WooCommerce coupon integration.
 *
 * @package Sticker
 */

namespace Sticker;

defined( 'ABSPATH' ) || exit;

final class Discount {
	const CODE      = 'sticker-auto-10';
	const TAG       = 'sticker';
	const MINIMUM   = 5;
	const PERCENT   = 10;

	/** Register once during plugins_loaded. */
	public static function init() {
		add_filter( 'woocommerce_get_shop_coupon_data', array( __CLASS__, 'coupon_data' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'synchronize' ), 100 );
		add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'valid_coupon' ), 10, 2 );
		add_filter( 'woocommerce_coupon_is_valid_for_product', array( __CLASS__, 'valid_product' ), 10, 3 );
		add_filter( 'woocommerce_cart_totals_coupon_label', array( __CLASS__, 'label' ), 10, 2 );
		add_filter( 'woocommerce_cart_totals_coupon_html', array( __CLASS__, 'coupon_html' ), 10, 3 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'order_meta' ) );
		add_action( 'woocommerce_store_api_checkout_update_order_meta', array( __CLASS__, 'order_meta' ) );
	}

	/** Variations inherit the parent's tag but use their own sale status. */
	public static function eligible( $product ) {
		return $product instanceof \WC_Product
			&& ! $product->is_on_sale()
			&& has_term( self::TAG, 'product_tag', $product->get_parent_id() ?: $product->get_id() );
	}

	/** Count units, rather than distinct products or cart rows. */
	public static function quantity( $cart ) {
		$quantity = 0;
		foreach ( $cart->get_cart() as $item ) {
			if ( self::eligible( $item['data'] ) ) {
				$quantity += max( 0, (float) $item['quantity'] );
			}
		}
		return $quantity;
	}

	/** Provide a reserved virtual coupon; no database coupon is created. */
	public static function coupon_data( $data, $code ) {
		if ( ! is_string( $code ) || ! wc_is_same_coupon( $code, self::CODE ) ) {
			return $data;
		}
		return array(
			'discount_type'      => 'percent',
			'amount'             => self::PERCENT,
			'exclude_sale_items' => true,
			'individual_use'     => false,
			'free_shipping'      => false,
		);
	}

	/** Rebuild only our coupon membership on every calculation; never change prices. */
	public static function synchronize( $cart ) {
		$codes = array_values(
			array_filter(
				$cart->get_applied_coupons(),
				static function ( $code ) {
					return ! wc_is_same_coupon( $code, self::CODE );
				}
			)
		);
		if ( self::quantity( $cart ) >= self::MINIMUM ) {
			// This is an automatic promotion, independent of the manual coupon UI setting.
			$codes[] = self::CODE;
		}
		$cart->set_applied_coupons( $codes );
	}

	/** Also reject attempts to manually apply the reserved code below threshold. */
	public static function valid_coupon( $valid, $coupon ) {
		if ( wc_is_same_coupon( $coupon->get_code(), self::CODE ) ) {
			return $valid && WC()->cart && self::quantity( WC()->cart ) >= self::MINIMUM;
		}
		return $valid;
	}

	/** Scope the native percentage calculation to eligible lines only. */
	public static function valid_product( $valid, $product, $coupon ) {
		return wc_is_same_coupon( $coupon->get_code(), self::CODE )
			? $valid && self::eligible( $product )
			: $valid;
	}

	/** Human-readable label in classic cart and checkout. Blocks show the coupon code. */
	public static function label( $label, $coupon ) {
		return wc_is_same_coupon( $coupon->get_code(), self::CODE )
			? __( 'Sticker discount (10%)', 'sticker' ) : $label;
	}

	/** An automatic promotion has no removable coupon link in classic templates. */
	public static function coupon_html( $html, $coupon, $discount_html ) {
		return wc_is_same_coupon( $coupon->get_code(), self::CODE ) ? $discount_html : $html;
	}

	/** Snapshot actual order coupon amounts using the HPOS-compatible CRUD API. */
	public static function order_meta( $order ) {
		$amount = 0.0;
		$tax    = 0.0;
		$found  = false;
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			if ( wc_is_same_coupon( $coupon->get_code(), self::CODE ) ) {
				$found  = true;
				$amount += (float) $coupon->get_discount();
				$tax    += (float) $coupon->get_discount_tax();
			}
		}
		$quantity = 0;
		$ids      = array();
		foreach ( $order->get_items( 'line_item' ) as $item ) {
			$product = $item->get_product();
			if ( self::eligible( $product ) ) {
				$quantity += (float) $item->get_quantity();
				$ids[]     = $product->get_id();
			}
		}
		// Overwrite zeros too: a checkout draft may previously have qualified.
		$order->update_meta_data( '_sticker_eligible_quantity', $quantity );
		$order->update_meta_data( '_sticker_discount_percent', $found ? self::PERCENT : 0 );
		$order->update_meta_data( '_sticker_discount_amount', wc_format_decimal( $amount, wc_get_price_decimals() ) );
		$order->update_meta_data( '_sticker_discount_tax', wc_format_decimal( $tax, wc_get_price_decimals() ) );
		$order->update_meta_data( '_sticker_eligible_product_ids', array_values( array_unique( $ids ) ) );
		$order->update_meta_data( '_sticker_discount_version', '1.0.0' );
		// Classic checkout saves new orders afterwards; Store API drafts already have an ID.
		if ( $order->get_id() ) {
			$order->save_meta_data();
		}
	}
}
