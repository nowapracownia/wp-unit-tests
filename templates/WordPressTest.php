<?php

/**
 * Comprehensive WordPress PHPUnit Test Suite
 * 
 * This file demonstrates various testing patterns including:
 * - Factory usage for posts, users, comments
 * - Testing post meta, user capabilities, comments
 * - Mocking REST API endpoints
 * - WooCommerce product/order testing (if WooCommerce is active)
 * - Mocking external API responses
 */

class WordPressTest extends WP_UnitTestCase {

	// ============================================
	// 1. FACTORY TESTS - POSTS
	// ============================================

	/**
	 * Test creating multiple posts using factory
	 */
	public function test_create_posts_with_factory() {
		$post_ids = self::factory()->post->create_many( 3 );

		$this->assertCount( 3, $post_ids );
		$this->assertCount( 3, get_posts() );
	}

	/**
	 * Test creating post with custom meta data
	 */
	public function test_create_post_with_meta() {
		$post_id = self::factory()->post->create( [
			'post_title'   => 'Test Post',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		add_post_meta( $post_id, 'custom_field', 'custom_value' );
		add_post_meta( $post_id, 'price', 99.99 );

		$this->assertEquals( 'custom_value', get_post_meta( $post_id, 'custom_field', true ) );
		$this->assertEquals( 99.99, get_post_meta( $post_id, 'price', true ) );
	}

	/**
	 * Test post with specific post type
	 */
	public function test_create_custom_post_type() {
		register_post_type( 'product', [
			'public' => true,
			'label'  => 'Products'
		] );

		$product_id = self::factory()->post->create( [
			'post_type'   => 'product',
			'post_title'  => 'Test Product',
			'post_status' => 'publish',
		] );

		$post = get_post( $product_id );
		$this->assertEquals( 'product', $post->post_type );
		$this->assertEquals( 'Test Product', $post->post_title );
	}

	// ============================================
	// 2. FACTORY TESTS - USERS
	// ============================================

	/**
	 * Test creating users with factory
	 */
	public function test_create_users_with_factory() {
		$user_ids = self::factory()->user->create_many( 5, [
			'role' => 'subscriber'
		] );

		$this->assertCount( 5, $user_ids );
		
		foreach ( $user_ids as $user_id ) {
			$user = get_user_by( 'id', $user_id );
			$this->assertTrue( in_array( 'subscriber', $user->roles ) );
		}
	}

	/**
	 * Test user capabilities
	 */
	public function test_user_capabilities() {
		$editor_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		$subscriber_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );

		$editor = get_user_by( 'id', $editor_id );
		$subscriber = get_user_by( 'id', $subscriber_id );

		$this->assertTrue( $editor->has_cap( 'edit_posts' ) );
		$this->assertTrue( $editor->has_cap( 'publish_posts' ) );
		$this->assertFalse( $subscriber->has_cap( 'edit_posts' ) );
		$this->assertTrue( $subscriber->has_cap( 'read' ) );
	}

	/**
	 * Test user meta data
	 */
	public function test_user_meta() {
		$user_id = self::factory()->user->create();

		update_user_meta( $user_id, 'phone_number', '+1234567890' );
		update_user_meta( $user_id, 'subscription_level', 'premium' );

		$this->assertEquals( '+1234567890', get_user_meta( $user_id, 'phone_number', true ) );
		$this->assertEquals( 'premium', get_user_meta( $user_id, 'subscription_level', true ) );
	}

	// ============================================
	// 3. FACTORY TESTS - COMMENTS
	// ============================================

	/**
	 * Test creating comments with factory
	 */
	public function test_create_comments_with_factory() {
		$post_id = self::factory()->post->create();
		$user_id = self::factory()->user->create();

		$comment_ids = self::factory()->comment->create_many( 3, [
			'comment_post_ID' => $post_id,
			'user_id'         => $user_id,
			'comment_approved' => 1,
		] );

		$this->assertCount( 3, $comment_ids );
		
		$comments = get_comments( [ 'post_id' => $post_id ] );
		$this->assertCount( 3, $comments );
	}

	/**
	 * Test comment approval workflow
	 */
	public function test_comment_approval() {
		$post_id = self::factory()->post->create();
		
		$pending_comment_id = self::factory()->comment->create( [
			'comment_post_ID'  => $post_id,
			'comment_approved' => 0,
		] );

		$approved_comment_id = self::factory()->comment->create( [
			'comment_post_ID'  => $post_id,
			'comment_approved' => 1,
		] );

		$pending_comment = get_comment( $pending_comment_id );
		$approved_comment = get_comment( $approved_comment_id );

		$this->assertEquals( '0', $pending_comment->comment_approved );
		$this->assertEquals( '1', $approved_comment->comment_approved );

		// Approve the pending comment
		wp_set_comment_status( $pending_comment_id, 'approve' );
		$updated_comment = get_comment( $pending_comment_id );
		$this->assertEquals( '1', $updated_comment->comment_approved );
	}

	// ============================================
	// 4. REST API ENDPOINT TESTS
	// ============================================

	/**
	 * Test REST API endpoint registration
	 */
	public function test_custom_rest_api_endpoint() {
		// Register a custom endpoint using rest_api_init action
		add_action( 'rest_api_init', function() {
			register_rest_route( 'custom/v1', '/test', [
				'methods'  => 'GET',
				'callback' => function() {
					return new WP_REST_Response( [ 'message' => 'Hello World' ], 200 );
				},
				'permission_callback' => '__return_true',
			] );
		} );

		// Trigger the rest_api_init action
		do_action( 'rest_api_init' );

		$request = new WP_REST_Request( 'GET', '/custom/v1/test' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'Hello World', $response->get_data()['message'] );
	}

	/**
	 * Test REST API with authentication
	 */
	public function test_rest_api_with_authentication() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$post_id = self::factory()->post->create( [
			'post_title'  => 'Test Post',
			'post_status' => 'publish',
		] );

		$request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'Test Post', $response->get_data()['title']['rendered'] );
	}

	// ============================================
	// 5. WOOCOMMERCE TESTS (if WooCommerce active)
	// ============================================

	/**
	 * Test creating WooCommerce product
	 * Note: Requires WooCommerce to be active
	 */
	public function test_create_woocommerce_product() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->markTestSkipped( 'WooCommerce is not active' );
		}

		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( 29.99 );
		$product->set_sku( 'TEST-SKU-001' );
		$product->set_stock_quantity( 100 );
		$product->set_manage_stock( true );
		$product_id = $product->save();

		$saved_product = wc_get_product( $product_id );
		
		$this->assertEquals( 'Test Product', $saved_product->get_name() );
		$this->assertEquals( 29.99, $saved_product->get_regular_price() );
		$this->assertEquals( 'TEST-SKU-001', $saved_product->get_sku() );
		$this->assertEquals( 100, $saved_product->get_stock_quantity() );
	}

	/**
	 * Test creating WooCommerce order
	 * Note: Requires WooCommerce to be active
	 */
	public function test_create_woocommerce_order() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->markTestSkipped( 'WooCommerce is not active' );
		}

		// Create a product
		$product = new WC_Product_Simple();
		$product->set_name( 'Order Test Product' );
		$product->set_regular_price( 49.99 );
		$product_id = $product->save();

		// Create an order
		$order = wc_create_order();
		$order->add_product( wc_get_product( $product_id ), 2 );
		$order->set_address( [
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'email'      => 'john@example.com',
		], 'billing' );
		$order->calculate_totals();
		$order->save();

		$this->assertEquals( 99.98, $order->get_total() );
		$this->assertEquals( 'John', $order->get_billing_first_name() );
		$this->assertCount( 1, $order->get_items() );
	}

	// ============================================
	// 6. MOCKING EXTERNAL API RESPONSES
	// ============================================

	/**
	 * Test mocking wp_remote_get for external API
	 */
	public function test_mock_external_api_response() {
		// Mock wp_remote_get
		add_filter( 'pre_http_request', function( $preempt, $args, $url ) {
			if ( strpos( $url, 'api.example.com' ) !== false ) {
				return [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body' => json_encode( [
						'status'  => 'success',
						'data'    => [ 'id' => 123, 'name' => 'Test Item' ],
					] ),
				];
			}
			return $preempt;
		}, 10, 3 );

		$response = wp_remote_get( 'https://api.example.com/items/123' );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$this->assertEquals( 200, wp_remote_retrieve_response_code( $response ) );
		$this->assertEquals( 'success', $body['status'] );
		$this->assertEquals( 123, $body['data']['id'] );
	}

	/**
	 * Test mocking payment gateway response
	 */
	public function test_mock_payment_gateway_response() {
		// Simulate a payment gateway class
		$mock_gateway_response = [
			'success'        => true,
			'transaction_id' => 'txn_' . uniqid(),
			'amount'         => 99.99,
			'currency'       => 'USD',
			'status'         => 'completed',
		];

		// Mock the payment processing
		add_filter( 'woocommerce_payment_successful_result', function( $result ) use ( $mock_gateway_response ) {
			return array_merge( $result, $mock_gateway_response );
		} );

		// Simulate payment processing
		$result = apply_filters( 'woocommerce_payment_successful_result', [] );

		$this->assertTrue( $result['success'] );
		$this->assertEquals( 'completed', $result['status'] );
		$this->assertEquals( 99.99, $result['amount'] );
		$this->assertStringStartsWith( 'txn_', $result['transaction_id'] );
	}

	/**
	 * Test mocking failed payment gateway response
	 */
	public function test_mock_failed_payment_gateway() {
		$mock_failed_response = [
			'success' => false,
			'error'   => 'Insufficient funds',
			'code'    => 'INSUFFICIENT_FUNDS',
		];

		add_filter( 'pre_http_request', function( $preempt, $args, $url ) use ( $mock_failed_response ) {
			if ( strpos( $url, 'payment-gateway.com' ) !== false ) {
				return [
					'response' => [
						'code'    => 402,
						'message' => 'Payment Required',
					],
					'body' => json_encode( $mock_failed_response ),
				];
			}
			return $preempt;
		}, 10, 3 );

		$response = wp_remote_post( 'https://payment-gateway.com/charge', [
			'body' => [ 'amount' => 100 ]
		] );

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$this->assertEquals( 402, wp_remote_retrieve_response_code( $response ) );
		$this->assertFalse( $body['success'] );
		$this->assertEquals( 'Insufficient funds', $body['error'] );
	}

	// ============================================
	// 7. TEARDOWN
	// ============================================

	/**
	 * Clean up after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		// Remove all filters we added
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'woocommerce_payment_successful_result' );
	}
}