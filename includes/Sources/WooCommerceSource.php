<?php
namespace DataBridge\Sources;

use DataBridge\Contracts\SourceAdapterInterface;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class WooCommerceSource implements SourceAdapterInterface {
	public function is_available(): bool {
		return class_exists( 'WooCommerce' );
	}

	public function fetch_page( string $entity, int $page, int $per_page ): array {
		$page     = max( 1, $page );
		$per_page = min( 100, max( 1, $per_page ) );

		switch ( $entity ) {
			case 'customers':
				$users = get_users(
					array(
						'role__in' => array( 'customer', 'subscriber' ),
						'number'  => $per_page,
						'paged'   => $page,
						'orderby' => 'ID',
						'order'   => 'ASC',
						'fields'  => 'all',
					)
				);
				return array_map( array( $this, 'normalize_customer' ), $users );

			case 'orders':
				$orders = wc_get_orders(
					array(
						'limit'   => $per_page,
						'page'    => $page,
						'orderby' => 'ID',
						'order'   => 'ASC',
						'paginate'=> false,
						'return'  => 'objects',
					)
				);
				return array_map( array( $this, 'normalize_order' ), $orders );

			case 'products':
				$products = wc_get_products(
					array(
						'limit'   => $per_page,
						'page'    => $page,
						'orderby' => 'ID',
						'order'   => 'ASC',
						'return'  => 'objects',
					)
				);
				return array_map( array( $this, 'normalize_product' ), $products );
		}

		return array();
	}

	private function safe_meta( $object ): array {
		$meta = array();
		foreach ( $object->get_meta_data() as $meta_item ) {
			$data = $meta_item->get_data();
			$key  = isset( $data['key'] ) ? (string) $data['key'] : '';
			if ( '' === $key || str_starts_with( $key, '_' ) && in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) {
				continue;
			}
			$meta[ $key ] = $data['value'] ?? null;
		}
		return $meta;
	}

	public function normalize_customer( $user ): array {
		$customer = new \WC_Customer( $user->ID );
		return array(
			'source_id'     => (string) $user->ID,
			'email'         => (string) $user->user_email,
			'username'      => (string) $user->user_login,
			'registered_at' => (string) $user->user_registered,
			'first_name'    => (string) $customer->get_first_name(),
			'last_name'     => (string) $customer->get_last_name(),
			'billing'       => array(
				'first_name' => $customer->get_billing_first_name(),
				'last_name'  => $customer->get_billing_last_name(),
				'company'    => $customer->get_billing_company(),
				'address_1'  => $customer->get_billing_address_1(),
				'address_2'  => $customer->get_billing_address_2(),
				'city'       => $customer->get_billing_city(),
				'state'      => $customer->get_billing_state(),
				'postcode'   => $customer->get_billing_postcode(),
				'country'    => $customer->get_billing_country(),
				'email'      => $customer->get_billing_email(),
				'phone'      => $customer->get_billing_phone(),
			),
			'shipping'      => array(
				'first_name' => $customer->get_shipping_first_name(),
				'last_name'  => $customer->get_shipping_last_name(),
				'company'    => $customer->get_shipping_company(),
				'address_1'  => $customer->get_shipping_address_1(),
				'address_2'  => $customer->get_shipping_address_2(),
				'city'       => $customer->get_shipping_city(),
				'state'      => $customer->get_shipping_state(),
				'postcode'   => $customer->get_shipping_postcode(),
				'country'    => $customer->get_shipping_country(),
			),
			'meta'          => $this->safe_meta( $customer ),
		);
	}

	public function normalize_order( $order ): array {
		$items = array();
		foreach ( $order->get_items() as $item ) {
			$items[] = array(
				'item_id'       => (string) $item->get_id(),
				'product_id'    => (string) $item->get_product_id(),
				'variation_id'  => (string) $item->get_variation_id(),
				'name'          => $item->get_name(),
				'quantity'      => $item->get_quantity(),
				'subtotal'      => $item->get_subtotal(),
				'total'         => $item->get_total(),
				'tax'           => $item->get_total_tax(),
				'sku'           => $item->get_product() ? $item->get_product()->get_sku() : '',
				'meta'          => $this->safe_meta( $item ),
			);
		}
		return array(
			'source_id'      => (string) $order->get_id(),
			'order_number'   => (string) $order->get_order_number(),
			'status'         => (string) $order->get_status(),
			'currency'       => (string) $order->get_currency(),
			'created_at'     => $order->get_date_created() ? $order->get_date_created()->date( 'c' ) : null,
			'updated_at'     => $order->get_date_modified() ? $order->get_date_modified()->date( 'c' ) : null,
			'customer_id'    => (string) $order->get_customer_id(),
			'customer_email' => (string) $order->get_billing_email(),
			'payment'        => array(
				'method'       => $order->get_payment_method(),
				'method_title' => $order->get_payment_method_title(),
				'transaction_id'=> $order->get_transaction_id(),
			),
			'amounts'        => array(
				'subtotal'  => $order->get_subtotal(),
				'discount'  => $order->get_discount_total(),
				'shipping'  => $order->get_shipping_total(),
				'tax'       => $order->get_total_tax(),
				'total'     => $order->get_total(),
			),
			'billing'        => $order->get_address( 'billing' ),
			'shipping'       => $order->get_address( 'shipping' ),
			'items'          => $items,
			'coupons'        => array_map( static fn( $coupon ) => $coupon->get_code(), $order->get_items( 'coupon' ) ),
			'customer_note'  => $order->get_customer_note(),
			'meta'           => $this->safe_meta( $order ),
		);
	}

	public function normalize_product( $product ): array {
		return array(
			'source_id'   => (string) $product->get_id(),
			'name'        => $product->get_name(),
			'type'        => $product->get_type(),
			'sku'         => $product->get_sku(),
			'parent_id'   => (string) $product->get_parent_id(),
			'status'      => $product->get_status(),
			'permalink'   => $product->get_permalink(),
			'description' => $product->get_description(),
			'short_description' => $product->get_short_description(),
			'price'       => $product->get_price(),
			'regular_price' => $product->get_regular_price(),
			'sale_price'  => $product->get_sale_price(),
			'stock_status'=> $product->get_stock_status(),
			'stock_quantity' => $product->get_stock_quantity(),
			'categories'  => wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'all' ) ),
			'images'      => array_map( static fn( $image_id ) => wp_get_attachment_url( $image_id ), $product->get_gallery_image_ids() ),
			'featured_image' => $product->get_image_id() ? wp_get_attachment_url( $product->get_image_id() ) : null,
			'meta'        => $this->safe_meta( $product ),
		);
	}
}
