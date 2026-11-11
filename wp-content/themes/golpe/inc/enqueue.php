<?php
/**
 * CSS / JS 불러오기.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 프런트엔드 에셋 등록.
 */
function golpe_enqueue_assets() {
	wp_enqueue_style(
		'golpe-main',
		GOLPE_URI . '/assets/css/main.css',
		array(),
		GOLPE_VERSION
	);

	if ( golpe_is_woocommerce_active() ) {
		wp_enqueue_style(
			'golpe-woocommerce',
			GOLPE_URI . '/assets/css/woocommerce.css',
			array( 'golpe-main' ),
			GOLPE_VERSION
		);
	}

	wp_enqueue_script(
		'golpe-main',
		GOLPE_URI . '/assets/js/main.js',
		array(),
		GOLPE_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	// 메인 페이지 전용 (모바일 카테고리 가로 스크롤 등).
	if ( is_front_page() ) {
		wp_enqueue_script(
			'golpe-home',
			GOLPE_URI . '/assets/js/home.js',
			array(),
			GOLPE_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'golpe_enqueue_assets' );
