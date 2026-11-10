<?php
/**
 * 테마 기본 설정.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce 활성화 여부.
 *
 * @return bool
 */
function golpe_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * 테마 기능 등록.
 */
function golpe_setup() {
	load_theme_textdomain( 'golpe', GOLPE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	register_nav_menus(
		array(
			'primary' => __( '메인 메뉴', 'golpe' ),
			'footer'  => __( '푸터 메뉴 (이용약관·개인정보처리방침 등)', 'golpe' ),
		)
	);

	// 카테고리 타일용 세로형 이미지 크기.
	add_image_size( 'golpe-category', 900, 1200, true );
}
add_action( 'after_setup_theme', 'golpe_setup' );

/**
 * 본문 최대 너비.
 */
function golpe_content_width() {
	$GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'golpe_content_width', 0 );

/**
 * body 클래스 추가.
 *
 * @param array $classes 기존 클래스.
 * @return array
 */
function golpe_body_classes( $classes ) {
	$classes[] = golpe_is_woocommerce_active() ? 'golpe-wc-active' : 'golpe-wc-inactive';
	return $classes;
}
add_filter( 'body_class', 'golpe_body_classes' );
