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
		'announcement'      => __( 'GOLPE 공식 온라인 스토어 — 신규 회원가입 혜택을 확인하세요', 'golpe' ),
		'hero_image'        => 0,
		'hero_eyebrow'      => 'PREMIUM GOLF SELECT SHOP',
		'hero_title'        => 'PLAY WITH PRESENCE',
		'hero_text'         => __( '필드 위의 존재감을 완성하는 골프백, 골프웨어, 액세서리.', 'golpe' ),
		'hero_button_label' => __( '컬렉션 보기', 'golpe' ),
		'hero_button_url'   => '',
		'brand_title'       => __( '단순함이 가장 강한 스타일입니다.', 'golpe' ),
		'brand_text'        => __( 'GOLPE는 블랙과 화이트, 두 가지 색에서 시작합니다. 군더더기 없는 디자인과 기능을 갖춘 골프용품만을 골라 소개합니다.', 'golpe' ),
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
