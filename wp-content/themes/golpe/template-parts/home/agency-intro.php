<?php
/**
 * 해외구매대행 안내 (배송 · 통관 · 반품) — 메인 페이지.
 *
 * 안내 문구는 inc/purchase-agency.php 의 공통 함수 · 설정값을 그대로 사용합니다.
 * (이 파일은 화면 배치만 담당하며, 반품 · 환불 정책 문구를 새로 만들지 않습니다.)
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_country = golpe_agency_option( 'agency_country' );
$golpe_terms   = golpe_agency_checkout_terms();
$golpe_keys    = golpe_agency_key_terms();

$golpe_steps = apply_filters(
	'golpe_agency_steps',
	array(
		array(
			'en'    => 'ORDER',
			'title' => __( '주문 · 결제', 'golpe' ),
		),
		array(
			'en'    => 'PURCHASE',
			/* translators: %s: 구매 국가 */
			'title' => sprintf( __( '%s 현지 구매', 'golpe' ), $golpe_country ),
		),
		array(
			'en'    => 'CUSTOMS',
			'title' => __( '국제배송 · 통관', 'golpe' ),
		),
		array(
			'en'    => 'DELIVERY',
			'title' => __( '국내 배송', 'golpe' ),
		),
	)
);

$golpe_cards = array(
	array(
		'icon'  => 'truck',
		'en'    => 'SHIPPING',
		'title' => __( '배송', 'golpe' ),
		'items' => array( $golpe_keys['shipping'] ),
	),
	array(
		'icon'  => 'search',
		'en'    => 'CUSTOMS',
		'title' => __( '통관', 'golpe' ),
		'items' => array( $golpe_keys['customs'], $golpe_keys['duty'] ),
	),
	array(
		'icon'  => 'refresh',
		'en'    => 'RETURNS',
		'title' => __( '취소 · 반품', 'golpe' ),
		'items' => array( $golpe_terms['refund'], $golpe_terms['return'], $golpe_terms['defect'] ),
	),
);
?>
<section class="section agency-intro" id="purchase-agency">
	<div class="container">
		<div class="agency-intro__head">
			<div>
				<p class="section-eyebrow">OVERSEAS PURCHASE AGENCY</p>
				<h2 class="section-title"><?php esc_html_e( '해외구매대행 안내', 'golpe' ); ?></h2>
			</div>
			<p class="agency-intro__lead">
				<?php
				/* translators: %s: 구매 국가 */
				printf( esc_html__( 'GOLPE는 %s 현지에서 판매되는 골프용품을 고객님을 대신해 구매하고, 국제배송과 통관을 거쳐 국내로 배송해 드리는 해외구매대행 서비스입니다.', 'golpe' ), esc_html( $golpe_country ) );
				?>
			</p>
		</div>

		<ol class="agency-flow" aria-label="<?php esc_attr_e( '진행 절차', 'golpe' ); ?>">
			<?php foreach ( $golpe_steps as $golpe_i => $golpe_step ) : ?>
				<li class="agency-flow__step">
					<span class="agency-flow__num"><?php echo esc_html( sprintf( '%02d', $golpe_i + 1 ) ); ?></span>
					<span class="agency-flow__en"><?php echo esc_html( $golpe_step['en'] ); ?></span>
					<span class="agency-flow__title"><?php echo esc_html( $golpe_step['title'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>

		<div class="agency-cards">
			<?php foreach ( $golpe_cards as $golpe_card ) : ?>
				<div class="agency-card">
					<?php golpe_icon( $golpe_card['icon'] ); ?>
					<p class="agency-card__en"><?php echo esc_html( $golpe_card['en'] ); ?></p>
					<h3 class="agency-card__title"><?php echo esc_html( $golpe_card['title'] ); ?></h3>
					<ul class="agency-card__list">
						<?php foreach ( $golpe_card['items'] as $golpe_item ) : ?>
							<li><?php echo esc_html( $golpe_item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="agency-intro__more">
			<?php golpe_agency_guide_link(); ?>
		</div>
	</div>
</section>
