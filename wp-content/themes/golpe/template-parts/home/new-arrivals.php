<?php
/**
 * 신상품 (WooCommerce 상품 자동 표시).
 *
 * - WooCommerce 가 있고 공개 상품이 있으면: 최신 상품 8개를 WooCommerce 기본 상품 카드로 표시
 * - 상품이 없거나 WooCommerce 가 없으면: "준비 중" 안내 (가짜 상품·가격은 표시하지 않음)
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_has_products = false;
if ( golpe_is_woocommerce_active() && function_exists( 'wc_get_products' ) ) {
	$golpe_has_products = ! empty(
		wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => 1,
				'return' => 'ids',
			)
		)
	);
}
?>
<section class="section section--products" id="new-arrivals">
	<div class="container">
		<?php golpe_section_heading( __( '신상품', 'golpe' ), 'NEW ARRIVALS', $golpe_has_products ? golpe_shop_url() : '' ); ?>

		<?php if ( $golpe_has_products ) : ?>
			<?php echo do_shortcode( '[products limit="8" columns="4" orderby="date" order="DESC" visibility="visible"]' ); ?>
		<?php else : ?>
			<div class="product-placeholder-grid" aria-hidden="true">
				<?php for ( $golpe_i = 0; $golpe_i < 4; $golpe_i++ ) : ?>
					<div class="product-placeholder">
						<span class="product-placeholder__image"></span>
						<span class="product-placeholder__line"></span>
						<span class="product-placeholder__line product-placeholder__line--short"></span>
					</div>
				<?php endfor; ?>
			</div>
			<p class="empty-note">
				<?php
				if ( current_user_can( 'edit_theme_options' ) ) {
					if ( golpe_is_woocommerce_active() ) {
						esc_html_e( '[관리자에게만 보임] 상품을 등록하고 "공개"하면 이곳에 최신 상품이 자동으로 표시됩니다.', 'golpe' );
					} else {
						esc_html_e( '[관리자에게만 보임] WooCommerce 를 설치·활성화하고 상품을 등록하면 이곳에 최신 상품이 자동으로 표시됩니다.', 'golpe' );
					}
				} else {
					esc_html_e( '새로운 컬렉션을 준비하고 있습니다. 곧 만나보세요.', 'golpe' );
				}
				?>
			</p>
		<?php endif; ?>
	</div>
</section>
