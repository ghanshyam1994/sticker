<?php

namespace Sticker;

defined( 'ABSPATH' ) || exit;

class Discount {
	const CODE      = 'sticker-auto-10';
	const TAG       = 'sticker';
	const MINIMUM   = 5;
	const PERCENT   = 10;

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

	public static function eligible( $product ) {
		if ( ! $product instanceof \WC_Product || $product->is_on_sale() ) {
			return false;
		}

		$product_id = $product->get_parent_id() ?: $product->get_id();

		return has_term( self::TAG, 'product_tag', $product_id );
	}

	public static function quantity( $cart ) {
		$quantity = 0;
		foreach ( $cart->get_cart() as $item ) {
			if ( self::eligible( $item['data'] ) ) {
				$quantity += max( 0, (float) $item['quantity'] );
			}
		}
		return $quantity;
	}

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
	
	public static function synchronize( $cart ) {
		$codes = array();

		foreach ( $cart->get_applied_coupons() as $code ) {
			if ( ! wc_is_same_coupon( $code, self::CODE ) ) {
				$codes[] = $code;
			}
		}

		if ( self::quantity( $cart ) >= self::MINIMUM ) {
			$codes[] = self::CODE;
		}
		$cart->set_applied_coupons( $codes );
	}

	public static function valid_coupon( $valid, $coupon ) {
		if ( wc_is_same_coupon( $coupon->get_code(), self::CODE ) ) {
			return $valid && WC()->cart && self::quantity( WC()->cart ) >= self::MINIMUM;
		}
		return $valid;
	}

	public static function valid_product( $valid, $product, $coupon ) {
		if ( ! wc_is_same_coupon( $coupon->get_code(), self::CODE ) ) {
			return $valid;
		}

		return $valid && self::eligible( $product );
	}

	public static function label( $label, $coupon ) {
		if ( wc_is_same_coupon( $coupon->get_code(), self::CODE ) ) {
			return __( 'Sticker discount (10%)', 'sticker' );
		}

		return $label;
	}

	public static function coupon_html( $html, $coupon, $discount_html ) {
		return wc_is_same_coupon( $coupon->get_code(), self::CODE ) ? $discount_html : $html;
	}

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
		$order->update_meta_data( '_sticker_eligible_quantity', $quantity );
		$order->update_meta_data( '_sticker_discount_percent', $found ? self::PERCENT : 0 );
		$order->update_meta_data( '_sticker_discount_amount', wc_format_decimal( $amount, wc_get_price_decimals() ) );
		$order->update_meta_data( '_sticker_discount_tax', wc_format_decimal( $tax, wc_get_price_decimals() ) );
		$order->update_meta_data( '_sticker_eligible_product_ids', array_values( array_unique( $ids ) ) );
		$order->update_meta_data( '_sticker_discount_version', '1.0.0' );
		if ( $order->get_id() ) {
			$order->save_meta_data();
		}
	}
}
