<?php
/**
 * 템플릿 도우미 함수.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 인라인 SVG 아이콘을 반환합니다. (외부 아이콘 폰트 없이 사용)
 *
 * @param string $name 아이콘 이름.
 * @return string
 */
function golpe_get_icon( $name ) {
	$icons = array(
		'search'  => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
		'bag'     => '<path d="M5 8h14l-1 13H6L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
		'menu'    => '<path d="M3 7h18M3 12h18M3 17h18"/>',
		'close'   => '<path d="M5 5l14 14M19 5L5 19"/>',
		'arrow'   => '<path d="M4 12h16M14 6l6 6-6 6"/>',
		'truck'   => '<path d="M2 6h12v10H2z"/><path d="M14 10h4l4 4v2h-8z"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
		'refresh' => '<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4v7h-7"/>',
		'chat'    => '<path d="M4 5h16v11H9l-5 4V5z"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return '<svg class="golpe-icon golpe-icon--' . esc_attr( $name ) . '" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/**
 * 아이콘 출력.
 *
 * @param string $name 아이콘 이름.
 */
function golpe_icon( $name ) {
	echo golpe_get_icon( $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 고정된 SVG 마크업.
}

/**
 * 사이트 로고 출력. 로고 이미지가 없으면 텍스트 로고를 사용합니다.
 */
function golpe_site_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="site-logo-text" href="%1$s" rel="home">%2$s</a>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'GOLPE' )
	);
}

/**
 * 쇼핑(전체 상품) 페이지 주소.
 *
 * @return string
 */
function golpe_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}
	return home_url( '/' );
}

/**
 * 마이페이지(계정) 주소. WooCommerce 가 없으면 워드프레스 로그인 주소.
 *
 * @return string
 */
function golpe_account_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'myaccount' );
	}
	return is_user_logged_in() ? admin_url( 'profile.php' ) : wp_login_url();
}

/**
 * 장바구니 주소.
 *
 * @return string
 */
function golpe_cart_url() {
	if ( function_exists( 'wc_get_cart_url' ) ) {
		return wc_get_cart_url();
	}
	return golpe_shop_url();
}

/**
 * 장바구니 상품 수.
 *
 * @return int
 */
function golpe_cart_count() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}
	return 0;
}

/**
 * 헤더 장바구니 숫자 배지 마크업.
 * WooCommerce 장바구니 추가 시 자동 갱신(fragments)에도 같은 마크업을 사용합니다.
 *
 * @return string
 */
function golpe_get_cart_count_markup() {
	$count = golpe_cart_count();
	return sprintf(
		'<span class="cart-count%1$s" data-golpe-cart-count>%2$s</span>',
		$count > 0 ? '' : ' is-empty',
		esc_html( (string) $count )
	);
}

/**
 * 메인 메뉴 출력. 관리자가 메뉴를 지정하지 않았으면 기본 카테고리 메뉴를 보여줍니다.
 *
 * @param string $context 'desktop' 또는 'mobile'.
 */
function golpe_primary_menu( $context = 'desktop' ) {
	$menu_class = 'desktop' === $context ? 'primary-menu' : 'mobile-menu';

	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => $menu_class,
				'menu_id'        => $menu_class,
				'depth'          => 2,
			)
		);
		return;
	}

	golpe_fallback_menu( $menu_class );
}

/**
 * 기본 메뉴 (카테고리 정의 기반).
 *
 * @param string $menu_class ul 클래스.
 */
function golpe_fallback_menu( $menu_class ) {
	echo '<ul id="' . esc_attr( $menu_class ) . '" class="' . esc_attr( $menu_class ) . '">';
	echo '<li class="menu-item"><a href="' . esc_url( golpe_shop_url() ) . '">' . esc_html__( '전체상품', 'golpe' ) . '</a></li>';

	foreach ( golpe_get_category_groups() as $group ) {
		echo '<li class="menu-item menu-item-has-children">';
		echo '<a href="' . esc_url( golpe_category_url( $group['slug'] ) ) . '">' . esc_html( $group['name'] ) . '</a>';
		if ( ! empty( $group['children'] ) ) {
			echo '<ul class="sub-menu">';
			foreach ( $group['children'] as $child ) {
				echo '<li class="menu-item"><a href="' . esc_url( golpe_category_url( $child['slug'] ) ) . '">' . esc_html( $child['name'] ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}

	echo '</ul>';
}

/**
 * 섹션 제목 출력.
 *
 * @param string $eyebrow 영문 소제목.
 * @param string $title   제목.
 * @param string $link    (선택) 전체보기 링크.
 */
function golpe_section_heading( $eyebrow, $title, $link = '' ) {
	echo '<div class="section-heading">';
	echo '<div>';
	echo '<p class="section-eyebrow">' . esc_html( $eyebrow ) . '</p>';
	echo '<h2 class="section-title">' . esc_html( $title ) . '</h2>';
	echo '</div>';
	if ( $link ) {
		echo '<a class="link-arrow" href="' . esc_url( $link ) . '">' . esc_html__( '전체보기', 'golpe' ) . golpe_get_icon( 'arrow' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
}
