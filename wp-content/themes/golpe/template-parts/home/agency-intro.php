<?php
/**
 * 해외구매대행 소개 섹션 (메인 배너 바로 아래).
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_country = golpe_agency_option( 'agency_country' );
$golpe_steps   = apply_filters(
	'golpe_agency_steps',
	array(
		array(
			'en'    => 'ORDER',
			'title' => __( '주문 · 결제', 'golpe' ),
			'text'  => __( 'GOLPE에서 상품을 고르고 개인통관고유부호와 함께 주문합니다.', 'golpe' ),
		),
		array(
			'en'    => 'PURCHASE',
			/* translators: %s: 구매 국가 */
			'title' => sprintf( __( '%s 현지 구매', 'golpe' ), $golpe_country ),
			'text'  => __( '고객님을 대신해 현지 판매처에서 상품을 구매합니다.', 'golpe' ),
		),
		array(
			'en'    => 'CUSTOMS',
			'title' => __( '국제배송 · 통관', 'golpe' ),
			'text'  => __( '국제배송 후 고객님 명의로 수입 통관이 진행됩니다.', 'golpe' ),
		),
		array(
			'en'    => 'DELIVERY',
			'title' => __( '국내 배송', 'golpe' ),
			'text'  => __( '통관이 끝나면 국내 택배로 받아보실 수 있습니다.', 'golpe' ),
		),
	)
);
?>
<section class="section agency-intro" id="purchase-agency">
	<div class="container">
		<div class="agency-intro__head">
			<div>
				<p class="section-eyebrow">OVERSEAS PURCHASE AGENCY</p>
				<h2 class="section-title">
					<?php
					/* translators: %s: 구매 국가 */
					printf( esc_html__( '%s 현지 골프용품, 해외구매대행 전문 쇼핑몰', 'golpe' ), esc_html( $golpe_country ) );
					?>
				</h2>
			</div>
			<p class="agency-intro__lead">
				<?php
				/* translators: %s: 구매 국가 */
				printf( esc_html__( 'GOLPE는 %s 현지에서 판매되는 골프용품을 고객님을 대신해 구매하고, 국제배송과 통관을 거쳐 국내로 배송해 드리는 해외구매대행 서비스입니다.', 'golpe' ), esc_html( $golpe_country ) );
				?>
				<?php golpe_agency_guide_link(); ?>
			</p>
		</div>

		<ol class="agency-steps">
			<?php foreach ( $golpe_steps as $golpe_i => $golpe_step ) : ?>
				<li class="agency-step">
					<span class="agency-step__num"><?php echo esc_html( sprintf( '%02d', $golpe_i + 1 ) ); ?></span>
					<span class="agency-step__en"><?php echo esc_html( $golpe_step['en'] ); ?></span>
					<h3 class="agency-step__title"><?php echo esc_html( $golpe_step['title'] ); ?></h3>
					<p class="agency-step__text"><?php echo esc_html( $golpe_step['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>

		<p class="agency-intro__note">
			<?php
			/* translators: 1: 배송 기간, 2: 면세 한도 */
			printf( esc_html__( '예상 배송기간 %1$s · 면세 한도(%2$s) 초과 시 관부가세 고객 부담 · 상품 수령 후 7일 이내 청약철회 가능', 'golpe' ), esc_html( golpe_agency_option( 'agency_shipping_period' ) ), esc_html( golpe_agency_duty_free_limit() ) );
			?>
		</p>
	</div>
</section>
