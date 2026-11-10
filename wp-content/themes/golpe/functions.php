<?php
/**
 * GOLPE 테마 기능 파일.
 *
 * 기능별 코드는 inc/ 폴더에 나누어 두었습니다.
 * - setup.php        : 테마 기본 기능(메뉴, 로고, 썸네일 등)
 * - enqueue.php      : CSS / JS 불러오기
 * - template-tags.php: 아이콘, 링크 등 템플릿 도우미 함수
 * - categories.php   : 골프백 / 골프웨어 / 액세서리 카테고리 정의
 * - customizer.php   : 관리자 > 외모 > 사용자 정의하기 설정 항목
 * - woocommerce.php  : WooCommerce 가 활성화된 경우에만 불러오는 연동 코드
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GOLPE_VERSION', '0.1.0' );
define( 'GOLPE_DIR', get_template_directory() );
define( 'GOLPE_URI', get_template_directory_uri() );

require GOLPE_DIR . '/inc/setup.php';
require GOLPE_DIR . '/inc/enqueue.php';
require GOLPE_DIR . '/inc/template-tags.php';
require GOLPE_DIR . '/inc/categories.php';
require GOLPE_DIR . '/inc/customizer.php';

// WooCommerce 플러그인이 설치·활성화된 경우에만 연동 코드를 불러옵니다.
// (플러그인은 테마보다 먼저 로드되므로 이 시점에 확인할 수 있습니다.)
if ( golpe_is_woocommerce_active() ) {
	require GOLPE_DIR . '/inc/woocommerce.php';
}
