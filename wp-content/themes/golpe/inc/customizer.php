<?php
/**
 * 관리자 > 외모 > 사용자 정의하기 설정.
 *
 * 메인 배너 문구·이미지, 상단 공지, 사업자 정보를 코드 수정 없이 바꿀 수 있습니다.
 * 사업자 정보는 기본값이 비어 있으며, 입력한 항목만 푸터에 표시됩니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 설정 기본값.
 *
 * @return array
 */
function golpe_option_defaults() {
	return array(
		'announcement'      => __( 'GOLPE는 중국 현지 골프용품 해외구매대행 쇼핑몰입니다 — 주문 전 구매대행 이용 안내를 확인해 주세요', 'golpe' ),
		'hero_image'        => 0,
		'hero_eyebrow'      => 'CHINA GOLF · OVERSEAS PURCHASE AGENCY',
		'hero_title'        => 'PLAY WITH PRESENCE.',
		'hero_text'         => __( '필드 위의 존재감을 완성하는 골프백, 골프웨어, 액세서리.', 'golpe' ),
		'hero_button_label' => 'SHOP NOW',
		'hero_button_url'   => '',
		'brand_title'       => __( '단순함이 가장 강한 스타일입니다.', 'golpe' ),
		'brand_text'        => __( 'GOLPE는 블랙과 화이트, 두 가지 색에서 시작합니다. 군더더기 없는 디자인과 기능을 갖춘 골프용품만을 골라 소개합니다.', 'golpe' ),
		'edit_eyebrow'      => __( '기획전', 'golpe' ),
		'edit_title'        => __( '블랙 & 화이트 셀렉션', 'golpe' ),
		'edit_text'         => __( 'GOLPE가 고른 이번 시즌의 골프백과 액세서리. 흑백의 절제된 디자인으로 필드 위 존재감을 완성하세요.', 'golpe' ),
		'edit_button_label' => 'VIEW EDIT',
		'edit_url'          => '',
		'edit_image'        => 0,
		'terms_page'        => 0,
		'biz_company'       => '',
		'biz_ceo'           => '',
		'biz_number'        => '',
		'biz_mail_order'    => '',
		'biz_address'       => '',
		'biz_phone'         => '',
		'biz_email'         => '',
	);
}

/**
 * 설정값 가져오기.
 *
 * @param string $key 설정 이름.
 * @return mixed
 */
function golpe_option( $key ) {
	$defaults = golpe_option_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( 'golpe_' . $key, $default );
}

/**
 * 사용자 정의하기 항목 등록.
 *
 * @param WP_Customize_Manager $wp_customize 커스터마이저 객체.
 */
function golpe_customize_register( $wp_customize ) {
	$defaults = golpe_option_defaults();

	$wp_customize->add_panel(
		'golpe_panel',
		array(
			'title'    => __( 'GOLPE 테마 설정', 'golpe' ),
			'priority' => 30,
		)
	);

	// 1) 메인 화면.
	$wp_customize->add_section(
		'golpe_home',
		array(
			'title' => __( '메인 화면', 'golpe' ),
			'panel' => 'golpe_panel',
		)
	);

	$text_fields = array(
		'announcement'      => array( __( '상단 공지 문구 (비우면 숨김)', 'golpe' ), 'golpe_home', 'text' ),
		'hero_eyebrow'      => array( __( '메인 배너 소제목', 'golpe' ), 'golpe_home', 'text' ),
		'hero_title'        => array( __( '메인 배너 제목', 'golpe' ), 'golpe_home', 'text' ),
		'hero_text'         => array( __( '메인 배너 설명', 'golpe' ), 'golpe_home', 'textarea' ),
		'hero_button_label' => array( __( '메인 배너 버튼 문구', 'golpe' ), 'golpe_home', 'text' ),
		'brand_title'       => array( __( '브랜드 소개 제목', 'golpe' ), 'golpe_home', 'text' ),
		'brand_text'        => array( __( '브랜드 소개 문구', 'golpe' ), 'golpe_home', 'textarea' ),
	);

	foreach ( $text_fields as $key => $field ) {
		$wp_customize->add_setting(
			'golpe_' . $key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'textarea' === $field[2] ? 'sanitize_textarea_field' : 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'golpe_' . $key,
			array(
				'label'   => $field[0],
				'section' => $field[1],
				'type'    => $field[2],
			)
		);
	}

	$wp_customize->add_setting(
		'golpe_hero_button_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'golpe_hero_button_url',
		array(
			'label'       => __( '메인 배너 버튼 링크 (비우면 전체상품)', 'golpe' ),
			'section'     => 'golpe_home',
			'type'        => 'url',
		)
	);

	$wp_customize->add_setting(
		'golpe_hero_image',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'golpe_hero_image',
			array(
				'label'     => __( '메인 배너 이미지 (권장 2400×1400, 비우면 블랙 배경)', 'golpe' ),
				'section'   => 'golpe_home',
				'mime_type' => 'image',
			)
		)
	);

	// 1-2) GOLPE EDIT (추천 상품 · 기획전).
	$wp_customize->add_section(
		'golpe_edit',
		array(
			'title'       => __( 'GOLPE EDIT (추천 상품 · 기획전)', 'golpe' ),
			'description' => __( '추천 상품은 상품 목록에서 별(★) 표시한 "추천 상품"이 최대 4개 표시됩니다. 기획전 이미지는 사용 권한이 있는 사진만 올려 주세요.', 'golpe' ),
			'panel'       => 'golpe_panel',
		)
	);
	$edit_fields = array(
		'edit_eyebrow'      => array( __( '기획전 소제목', 'golpe' ), 'text' ),
		'edit_title'        => array( __( '기획전 제목', 'golpe' ), 'text' ),
		'edit_text'         => array( __( '기획전 설명', 'golpe' ), 'textarea' ),
		'edit_button_label' => array( __( '기획전 버튼 문구', 'golpe' ), 'text' ),
	);
	foreach ( $edit_fields as $key => $field ) {
		$wp_customize->add_setting(
			'golpe_' . $key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'textarea' === $field[1] ? 'sanitize_textarea_field' : 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'golpe_' . $key,
			array(
				'label'   => $field[0],
				'section' => 'golpe_edit',
				'type'    => $field[1],
			)
		);
	}
	$wp_customize->add_setting(
		'golpe_edit_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'golpe_edit_url',
		array(
			'label'   => __( '기획전 링크 (비우면 전체상품)', 'golpe' ),
			'section' => 'golpe_edit',
			'type'    => 'url',
		)
	);
	$wp_customize->add_setting(
		'golpe_edit_image',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'golpe_edit_image',
			array(
				'label'     => __( '기획전 이미지 (세로형 4:5 권장, 비우면 블랙 배경)', 'golpe' ),
				'section'   => 'golpe_edit',
				'mime_type' => 'image',
			)
		)
	);

	// 2) 사업자 정보 (전자상거래법 표시 사항).
	$wp_customize->add_section(
		'golpe_business',
		array(
			'title'       => __( '사업자 정보 (푸터)', 'golpe' ),
			'description' => __( '입력한 항목만 푸터에 표시됩니다. 실제 사업자 정보로 입력해 주세요.', 'golpe' ),
			'panel'       => 'golpe_panel',
		)
	);

	$biz_fields = array(
		'biz_company'    => __( '상호', 'golpe' ),
		'biz_ceo'        => __( '대표자', 'golpe' ),
		'biz_number'     => __( '사업자등록번호', 'golpe' ),
		'biz_mail_order' => __( '통신판매업 신고번호', 'golpe' ),
		'biz_address'    => __( '사업장 주소', 'golpe' ),
		'biz_phone'      => __( '고객센터 전화번호', 'golpe' ),
		'biz_email'      => __( '이메일', 'golpe' ),
	);

	$wp_customize->add_setting(
		'golpe_terms_page',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'golpe_terms_page',
		array(
			'label'       => __( '이용약관 페이지', 'golpe' ),
			'description' => __( '비우면 WooCommerce 설정의 "이용약관" 페이지를 사용합니다.', 'golpe' ),
			'section'     => 'golpe_business',
			'type'        => 'dropdown-pages',
		)
	);

	foreach ( $biz_fields as $key => $label ) {
		$wp_customize->add_setting(
			'golpe_' . $key,
			array(
				'default'           => '',
				'sanitize_callback' => 'biz_email' === $key ? 'sanitize_email' : 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'golpe_' . $key,
			array(
				'label'   => $label,
				'section' => 'golpe_business',
				'type'    => 'biz_email' === $key ? 'email' : 'text',
			)
		);
	}
}
add_action( 'customize_register', 'golpe_customize_register' );

/**
 * 푸터에 표시할 사업자 정보 (입력된 항목만).
 *
 * @return array 라벨 => 값.
 */
function golpe_business_info() {
	$labels = array(
		'biz_company'    => __( '상호', 'golpe' ),
		'biz_ceo'        => __( '대표', 'golpe' ),
		'biz_number'     => __( '사업자등록번호', 'golpe' ),
		'biz_mail_order' => __( '통신판매업신고', 'golpe' ),
		'biz_address'    => __( '주소', 'golpe' ),
		'biz_phone'      => __( '고객센터', 'golpe' ),
		'biz_email'      => __( '이메일', 'golpe' ),
	);
	$info   = array();
	foreach ( $labels as $key => $label ) {
		$value = trim( (string) golpe_option( $key ) );
		if ( '' !== $value ) {
			$info[ $label ] = $value;
		}
	}
	return $info;
}

/**
 * 이용약관 페이지 주소. (사용자 정의하기 → WooCommerce 이용약관 순, 공개 페이지만)
 *
 * @return string
 */
function golpe_terms_url() {
	$page_id = absint( golpe_option( 'terms_page' ) );
	if ( ! $page_id && function_exists( 'wc_terms_and_conditions_page_id' ) ) {
		$page_id = absint( wc_terms_and_conditions_page_id() );
	}
	if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
		return '';
	}
	$url = get_permalink( $page_id );
	return $url ? $url : '';
}
