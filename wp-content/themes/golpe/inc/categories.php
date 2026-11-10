<?php
/**
 * 쇼핑몰 카테고리 정의.
 *
 * 화면에 보여줄 카테고리 구조를 한 곳에서 관리합니다.
 * 실제 상품 카테고리는 관리자 > 상품 > 카테고리 에서 아래와 "같은 슬러그"로 만들면
 * 자동으로 연결됩니다. 이 테마는 카테고리를 자동으로 생성하거나 삭제하지 않습니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 카테고리 그룹 목록.
 *
 * 'golpe_category_groups' 필터로 다른 코드에서 수정할 수 있습니다.
 *
 * @return array
 */
function golpe_get_category_groups() {
	$groups = array(
		array(
			'slug'        => 'golf-bags',
			'name'        => __( '골프백', 'golpe' ),
			'en'          => 'GOLF BAGS',
			'mark'        => 'BAGS',
			'description' => __( '라운드의 시작을 함께하는 캐디백부터 보스턴백까지.', 'golpe' ),
			'tone'        => 'dark',
			'children'    => array(
				array(
					'slug' => 'caddie-bags',
					'name' => __( '캐디백', 'golpe' ),
					'en'   => 'CADDIE BAG',
				),
				array(
					'slug' => 'stand-bags',
					'name' => __( '스탠드백', 'golpe' ),
					'en'   => 'STAND BAG',
				),
				array(
					'slug' => 'boston-bags',
					'name' => __( '보스턴백', 'golpe' ),
					'en'   => 'BOSTON BAG',
				),
			),
		),
		array(
			'slug'        => 'golf-wear',
			'name'        => __( '골프웨어', 'golpe' ),
			'en'          => 'GOLF WEAR',
			'mark'        => 'WEAR',
			'description' => __( '필드와 일상을 넘나드는 미니멀 골프웨어.', 'golpe' ),
			'tone'        => 'light',
			'children'    => array(
				array(
					'slug' => 'apparel',
					'name' => __( '의류', 'golpe' ),
					'en'   => 'APPAREL',
				),
				array(
					'slug' => 'caps',
					'name' => __( '모자', 'golpe' ),
					'en'   => 'HEADWEAR',
				),
				array(
					'slug' => 'shoes',
					'name' => __( '신발', 'golpe' ),
					'en'   => 'SHOES',
				),
			),
		),
		array(
			'slug'        => 'accessories',
			'name'        => __( '액세서리', 'golpe' ),
			'en'          => 'ACCESSORIES',
			'mark'        => 'GEAR',
			'description' => __( '디테일을 완성하는 골프 액세서리와 기타 용품.', 'golpe' ),
			'tone'        => 'dark',
			'children'    => array(
				array(
					'slug' => 'pouches',
					'name' => __( '파우치', 'golpe' ),
					'en'   => 'POUCH',
				),
				array(
					'slug' => 'golf-accessories',
					'name' => __( '골프 액세서리', 'golpe' ),
					'en'   => 'ACCESSORY',
				),
				array(
					'slug' => 'etc',
					'name' => __( '기타 용품', 'golpe' ),
					'en'   => 'ETC',
				),
			),
		),
	);

	return apply_filters( 'golpe_category_groups', $groups );
}

/**
 * 슬러그로 WooCommerce 상품 카테고리를 찾습니다.
 *
 * @param string $slug 카테고리 슬러그.
 * @return WP_Term|null
 */
function golpe_get_product_category( $slug ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return null;
	}
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	return ( $term instanceof WP_Term ) ? $term : null;
}

/**
 * 카테고리 링크 주소.
 * - 같은 슬러그의 상품 카테고리가 있으면 그 카테고리 페이지
 * - 없으면 전체 상품(쇼핑) 페이지
 *
 * @param string $slug 카테고리 슬러그.
 * @return string
 */
function golpe_category_url( $slug ) {
	$term = golpe_get_product_category( $slug );
	if ( $term ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return golpe_shop_url();
}

/**
 * 카테고리 대표 이미지 주소.
 * WooCommerce 카테고리 편집 화면의 "썸네일" 이미지를 사용합니다.
 *
 * @param string $slug 카테고리 슬러그.
 * @param string $size 이미지 크기.
 * @return string 이미지가 없으면 빈 문자열.
 */
function golpe_category_image_url( $slug, $size = 'golpe-category' ) {
	$term = golpe_get_product_category( $slug );
	if ( ! $term ) {
		return '';
	}
	$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( ! $thumbnail_id ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $thumbnail_id, $size );
	return $url ? $url : '';
}

/**
 * 카테고리에 등록된 상품 수. (카테고리가 없으면 null)
 *
 * @param string $slug 카테고리 슬러그.
 * @return int|null
 */
function golpe_category_count( $slug ) {
	$term = golpe_get_product_category( $slug );
	return $term ? (int) $term->count : null;
}
