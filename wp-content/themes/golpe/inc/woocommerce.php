<?php
/**
 * WooCommerce 연동.
 *
 * 이 파일은 WooCommerce 가 활성화된 경우에만 functions.php 에서 불러옵니다.
 * 결제·주문·고객 데이터는 다루지 않고, 화면 구성(레이아웃)만 조정합니다.
 * WooCommerce 템플릿을 덮어쓰지 않고 "훅(hook)"으로만 수정하므로
 * WooCommerce 업데이트 시 충돌 가능성이 낮습니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce 테마 지원 선언.
 */
function golpe_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 1000,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'golpe_woocommerce_setup' );

/**
 * 기본 WooCommerce 콘텐츠 감싸기(wrapper)를 테마 레이아웃으로 교체.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * 감싸기 시작.
 */
function golpe_woocommerce_wrapper_before() {
	echo '<div class="container shop-container">';
}
add_action( 'woocommerce_before_main_content', 'golpe_woocommerce_wrapper_before', 10 );

/**
 * 감싸기 끝.
 */
function golpe_woocommerce_wrapper_after() {
	echo '</div>';
}
add_action( 'woocommerce_after_main_content', 'golpe_woocommerce_wrapper_after', 10 );

/**
 * 상품 목록 한 줄에 4개.
 *
 * @return int
 */
function golpe_loop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'golpe_loop_columns' );

/**
 * 관련 상품 4개, 4열.
 *
 * @param array $args 인자.
 * @return array
 */
function golpe_related_products_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'golpe_related_products_args' );

/**
 * 장바구니에 상품을 담으면 헤더의 숫자를 새로고침 없이 갱신.
 *
 * @param array $fragments 갱신할 HTML 조각.
 * @return array
 */
function golpe_cart_count_fragment( $fragments ) {
	$fragments['span[data-golpe-cart-count]'] = golpe_get_cart_count_markup();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'golpe_cart_count_fragment' );

/**
 * 상품 목록 상단 카테고리 바로가기 (쇼핑·카테고리 페이지).
 */
function golpe_shop_category_nav() {
	if ( ! ( is_shop() || is_product_category() ) ) {
		return;
	}

	$current = is_product_category() ? get_queried_object() : null;
	$current_slug = ( $current instanceof WP_Term ) ? $current->slug : '';

	echo '<nav class="shop-category-nav" aria-label="' . esc_attr__( '상품 카테고리', 'golpe' ) . '"><ul>';
	printf(
		'<li><a href="%1$s"%2$s>%3$s</a></li>',
		esc_url( golpe_shop_url() ),
		is_shop() ? ' class="is-active" aria-current="page"' : '',
		esc_html__( '전체', 'golpe' )
	);
	foreach ( golpe_get_category_groups() as $group ) {
		$slugs     = array_merge( array( $group['slug'] ), wp_list_pluck( $group['children'], 'slug' ) );
		$is_active = in_array( $current_slug, $slugs, true );
		printf(
			'<li><a href="%1$s"%2$s>%3$s</a></li>',
			esc_url( golpe_category_url( $group['slug'] ) ),
			$is_active ? ' class="is-active"' : '',
			esc_html( $group['name'] )
		);
	}
	echo '</ul></nav>';
}
add_action( 'woocommerce_before_shop_loop', 'golpe_shop_category_nav', 5 );
add_action( 'woocommerce_no_products_found', 'golpe_shop_category_nav', 5 );
