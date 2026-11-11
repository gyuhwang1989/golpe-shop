<?php
/**
 * 쇼핑 안내 (배송 · 교환/반품 · 고객센터) — 해외구매대행 기준.
 *
 * 배송기간 · 운영시간 등은 사용자 정의하기 > 구매대행 안내 설정값을 사용합니다.
 * 'golpe_service_items' 필터로 바꿀 수 있습니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_cs_hours = trim( (string) golpe_agency_option( 'agency_cs_hours' ) );

$golpe_service_items = apply_filters(
	'golpe_service_items',
	array(
		array(
			'icon'  => 'truck',
			'title' => __( '배송 안내', 'golpe' ),
			'text'  => sprintf(
				/* translators: %s: 배송 기간 */
				__( '현지 구매 → 국제배송 → 통관 → 국내 배송 순서로 진행되며 %s 소요됩니다. 현지 사정 · 통관 상황에 따라 늦어질 수 있습니다.', 'golpe' ),
				golpe_agency_option( 'agency_shipping_period' )
			),
		),
		array(
			'icon'  => 'refresh',
			'title' => __( '교환 · 반품', 'golpe' ),
			'text'  => __( '해외구매대행 상품도 상품을 받은 날부터 7일 이내 반품을 신청할 수 있습니다. 상품 하자 · 오배송 시 반품 비용은 GOLPE가 부담합니다.', 'golpe' ),
		),
		array(
			'icon'  => 'chat',
			'title' => __( '고객센터', 'golpe' ),
			'text'  => '' !== $golpe_cs_hours
				/* translators: %s: 운영 시간 */
				? sprintf( __( '주문 · 배송 · 통관 관련 문의를 도와드립니다. 운영시간: %s', 'golpe' ), $golpe_cs_hours )
				: __( '주문 · 배송 · 통관 관련 문의는 고객센터로 연락해 주세요.', 'golpe' ),
		),
	)
);
?>
<section class="service-strip">
	<div class="container service-strip__grid">
		<?php foreach ( $golpe_service_items as $golpe_item ) : ?>
			<div class="service-item">
				<?php golpe_icon( $golpe_item['icon'] ); ?>
				<h3 class="service-item__title"><?php echo esc_html( $golpe_item['title'] ); ?></h3>
				<p class="service-item__text"><?php echo esc_html( $golpe_item['text'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>
