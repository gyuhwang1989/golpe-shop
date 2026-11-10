<?php
/**
 * 해외구매대행 공통 설정 · 안내 문구.
 *
 * 메인 화면, 이용 안내 페이지, 상품 상세, 장바구니, 결제 화면에서
 * 같은 안내 문구를 쓰도록 이 파일 한 곳에서 관리합니다.
 *
 * ⚠️ 원칙: 전자상거래법상 청약철회(반품)·환불 권리를 제한하는 문구를 쓰지 않습니다.
 *    - "반품 불가", "해외상품 환불 불가", "주문 후 취소 불가" 등 금지
 *    - 단순변심 반품 시 소비자는 "실제 반품 비용"만 부담 (사전 고지 금액)
 *    - 상품 하자 · 오배송 등은 판매자가 반품 비용 부담
 *
 * 관리자 > 외모 > 사용자 정의하기 > GOLPE 테마 설정 > 구매대행 안내 에서 값을 바꿀 수 있습니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 구매대행 설정 기본값.
 *
 * @return array
 */
function golpe_agency_defaults() {
	return array(
		'agency_country'         => __( '중국', 'golpe' ),
		'agency_shipping_period' => __( '결제 완료 후 약 7~14 영업일', 'golpe' ),
		'agency_return_cost'     => '',
		'agency_cs_hours'        => '',
		'agency_guide_page'      => 0,
		// 주문 완료 · 취소 · 환불 후 개인통관고유부호를 보관하는 기간(일). 지나면 자동 삭제.
		'agency_customs_retention_days' => 90,
	);
}

/**
 * 구매대행 설정값.
 *
 * @param string $key 설정 이름.
 * @return mixed
 */
function golpe_agency_option( $key ) {
	$defaults = golpe_agency_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( 'golpe_' . $key, $default );
}

/**
 * 면세 한도 안내 (중국 등 미국 외 국가 발송 기준).
 * 관세청 기준이 바뀌면 이 값만 수정하면 됩니다.
 *
 * @return string
 */
function golpe_agency_duty_free_limit() {
	return apply_filters( 'golpe_agency_duty_free_limit', 'USD 150' );
}

/**
 * '구매대행 이용 안내' 페이지 주소. (설정하지 않았으면 빈 문자열)
 *
 * @return string
 */
function golpe_agency_guide_url() {
	$page_id = absint( golpe_agency_option( 'agency_guide_page' ) );
	if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
		return '';
	}
	$url = get_permalink( $page_id );
	return $url ? $url : '';
}

/**
 * 단순변심 반품 비용 안내 문장.
 *
 * @return string
 */
function golpe_agency_return_cost_text() {
	$cost = trim( (string) golpe_agency_option( 'agency_return_cost' ) );
	if ( '' !== $cost ) {
		/* translators: %s: 반품 비용 */
		return sprintf( __( '단순변심 반품 시 실제 반품 비용(%s)만 고객님이 부담합니다.', 'golpe' ), $cost );
	}
	return __( '단순변심 반품 시 실제 발생하는 반품 배송비만 고객님이 부담하며, 금액은 주문 전 안내해 드립니다.', 'golpe' );
}

/**
 * 핵심 거래 조건 목록 (상품 상세 · 장바구니 · 결제 화면 공통).
 *
 * @return array 항목 키 => 문장.
 */
function golpe_agency_key_terms() {
	$terms = array(
		'agency'   => sprintf(
			/* translators: %s: 구매 국가 */
			__( '이 상품은 %s 현지에서 고객님을 대신해 구매하는 해외구매대행 상품입니다. 주문 후 현지에서 구매가 진행됩니다.', 'golpe' ),
			golpe_agency_option( 'agency_country' )
		),
		'shipping' => sprintf(
			/* translators: %s: 배송 기간 */
			__( '예상 배송기간은 %s이며, 현지 사정 · 국제배송 · 통관 상황에 따라 늦어질 수 있습니다.', 'golpe' ),
			golpe_agency_option( 'agency_shipping_period' )
		),
		'price'    => __( '판매가에는 현지 상품가, 현지 배송비, 국제배송비, 구매대행 수수료가 포함되어 있습니다.', 'golpe' ),
		'duty'     => sprintf(
			/* translators: %s: 면세 한도 */
			__( '관부가세: 물품가격이 면세 한도(%s, 미국 외 발송 기준)를 넘거나 같은 날 여러 건이 함께 통관되는 경우 관세 · 부가세가 부과될 수 있으며, 이 세금은 고객님 부담입니다.', 'golpe' ),
			golpe_agency_duty_free_limit()
		),
		'customs'  => __( '통관을 위해 수취인 본인 명의의 개인통관고유부호가 필요합니다.', 'golpe' ),
		'refund'   => __( '상품을 받기 전에는 언제든 주문 취소를, 받은 후에는 받은 날부터 7일 이내에 반품(청약철회)을 신청할 수 있습니다. ', 'golpe' ) . golpe_agency_return_cost_text(),
		'defect'   => __( '상품 하자 · 오배송 등 표시 · 광고 또는 계약 내용과 다른 경우 반품 비용은 GOLPE가 부담합니다.', 'golpe' ),
	);

	return apply_filters( 'golpe_agency_key_terms', $terms );
}

/**
 * 결제 · 장바구니용 짧은 핵심 조건 (4줄).
 * 자세한 내용은 golpe_agency_key_terms() 와 이용 안내 페이지에 있습니다.
 *
 * @return array 항목 키 => 문장.
 */
function golpe_agency_checkout_terms() {
	$terms = array(
		'shipping' => sprintf(
			/* translators: %s: 배송 기간 */
			__( '해외구매대행 상품 · 예상 배송 %s', 'golpe' ),
			golpe_agency_option( 'agency_shipping_period' )
		),
		'duty'     => sprintf(
			/* translators: %s: 면세 한도 */
			__( '관부가세: %s 초과 시 고객 부담', 'golpe' ),
			golpe_agency_duty_free_limit()
		),
		'refund'   => __( '취소 · 반품: 받기 전 언제든 취소, 받은 후 7일 이내 반품 가능 (단순변심은 반품 실비만 부담)', 'golpe' ),
		'defect'   => __( '하자 · 오배송은 반품 비용 전액 GOLPE 부담', 'golpe' ),
	);

	return apply_filters( 'golpe_agency_checkout_terms', $terms );
}

/**
 * 결제 · 장바구니용 짧은 핵심 조건 목록 출력.
 */
function golpe_agency_checkout_terms_list() {
	echo '<ul class="agency-terms agency-terms--short">';
	foreach ( golpe_agency_checkout_terms() as $key => $text ) {
		echo '<li class="' . esc_attr( 'agency-terms__item agency-terms__item--' . $key ) . '">' . esc_html( $text ) . '</li>';
	}
	echo '</ul>';
}

/**
 * 구매대행 설정 항목을 사용자 정의하기에 추가.
 *
 * @param WP_Customize_Manager $wp_customize 커스터마이저 객체.
 */
function golpe_agency_customize_register( $wp_customize ) {
	$defaults = golpe_agency_defaults();

	$wp_customize->add_section(
		'golpe_agency',
		array(
			'title'       => __( '구매대행 안내', 'golpe' ),
			'description' => __( '메인 화면 · 이용 안내 · 상품 상세 · 결제 화면에 공통으로 표시되는 구매대행 조건입니다. 실제 운영 기준으로 입력해 주세요.', 'golpe' ),
			'panel'       => 'golpe_panel',
		)
	);

	$text_fields = array(
		'agency_country'         => __( '구매 국가', 'golpe' ),
		'agency_shipping_period' => __( '예상 배송기간 (예: 결제 완료 후 약 7~14 영업일)', 'golpe' ),
		'agency_return_cost'     => __( '단순변심 반품 비용 (예: 국내 회수비 + 해외 반송비 실비, 약 OO원) — 실제 비용 범위 내로만 입력', 'golpe' ),
		'agency_cs_hours'        => __( '고객센터 운영시간 (예: 평일 10:00~17:00, 주말·공휴일 휴무)', 'golpe' ),
	);

	foreach ( $text_fields as $key => $label ) {
		$wp_customize->add_setting(
			'golpe_' . $key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'golpe_' . $key,
			array(
				'label'   => $label,
				'section' => 'golpe_agency',
				'type'    => 'text',
			)
		);
	}

	$wp_customize->add_setting(
		'golpe_agency_customs_retention_days',
		array(
			'default'           => $defaults['agency_customs_retention_days'],
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'golpe_agency_customs_retention_days',
		array(
			'label'       => __( '개인통관고유부호 보관 기간 (일)', 'golpe' ),
			'description' => __( '주문이 완료 · 취소 · 환불된 뒤 이 기간이 지나면 번호를 자동 삭제합니다. 법적 보관 의무가 있는지 확인 후 정하세요. (기본 90일)', 'golpe' ),
			'section'     => 'golpe_agency',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 1,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'golpe_agency_guide_page',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'golpe_agency_guide_page',
		array(
			'label'       => __( "'구매대행 이용 안내' 페이지", 'golpe' ),
			'description' => __( "페이지 > 새 페이지 추가에서 템플릿을 '구매대행 이용 안내'로 선택해 만든 뒤 여기서 지정하세요.", 'golpe' ),
			'section'     => 'golpe_agency',
			'type'        => 'dropdown-pages',
		)
	);
}
add_action( 'customize_register', 'golpe_agency_customize_register', 20 );

/**
 * 핵심 조건 목록 출력.
 *
 * @param array  $keys  출력할 항목 키 (비우면 전체).
 * @param string $class ul 클래스.
 */
function golpe_agency_terms_list( $keys = array(), $class = 'agency-terms' ) {
	$terms = golpe_agency_key_terms();
	if ( ! empty( $keys ) ) {
		$terms = array_intersect_key( $terms, array_flip( $keys ) );
	}
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $terms as $key => $text ) {
		echo '<li class="' . esc_attr( $class . '__item ' . $class . '__item--' . $key ) . '">' . esc_html( $text ) . '</li>';
	}
	echo '</ul>';
}

/**
 * '이용 안내 자세히 보기' 링크 (페이지가 지정된 경우만).
 *
 * @param string $label 링크 문구.
 */
function golpe_agency_guide_link( $label = '' ) {
	$url = golpe_agency_guide_url();
	if ( ! $url ) {
		return;
	}
	$label = $label ? $label : __( '구매대행 이용 안내 자세히 보기', 'golpe' );
	echo '<a class="link-arrow agency-guide-link" href="' . esc_url( $url ) . '">' . esc_html( $label ) . golpe_get_icon( 'arrow' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
