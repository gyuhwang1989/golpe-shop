<?php
/**
 * 쇼핑 안내 (배송 · 교환/반품 · 고객센터).
 *
 * 문구는 운영 정책 확정 후 수정이 필요합니다. 'golpe_service_items' 필터로 바꿀 수 있습니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_service_items = apply_filters(
	'golpe_service_items',
	array(
		array(
			'icon'  => 'truck',
			'title' => __( '배송 안내', 'golpe' ),
			'text'  => __( '주문 후 상품 준비 상태와 배송 현황을 마이페이지에서 확인할 수 있습니다.', 'golpe' ),
		),
		array(
			'icon'  => 'refresh',
			'title' => __( '교환 · 반품', 'golpe' ),
			'text'  => __( '상품 수령 후 교환·반품 기준은 상품 상세 페이지의 안내를 확인해 주세요.', 'golpe' ),
		),
		array(
			'icon'  => 'chat',
			'title' => __( '고객센터', 'golpe' ),
			'text'  => __( '상품 및 주문 관련 문의는 고객센터로 연락해 주세요.', 'golpe' ),
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
