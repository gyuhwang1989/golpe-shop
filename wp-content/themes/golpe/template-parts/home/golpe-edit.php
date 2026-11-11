<?php
/**
 * GOLPE EDIT — 기획전 배너 + 추천 상품.
 *
 * - 기획전: 사용자 정의하기 > GOLPE 테마 설정 > GOLPE EDIT 에서 제목 · 문구 · 링크 · 이미지 입력
 * - 추천 상품: WooCommerce 상품 목록에서 별(★)로 "추천" 지정한 상품 최대 4개
 *   (없으면 "준비 중" 자리 표시 — 가짜 상품 · 가격은 표시하지 않음)
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_edit_image = absint( golpe_option( 'edit_image' ) );
$golpe_edit_url   = golpe_option( 'edit_url' );
$golpe_edit_url   = $golpe_edit_url ? $golpe_edit_url : golpe_shop_url();

$golpe_has_featured = false;
if ( golpe_is_woocommerce_active() && function_exists( 'wc_get_featured_product_ids' ) ) {
	$golpe_has_featured = ! empty( wc_get_featured_product_ids() );
}
?>
<section class="section golpe-edit" id="golpe-edit">
	<div class="container">
		<?php golpe_section_heading( __( '추천 상품 · 기획전', 'golpe' ), 'GOLPE EDIT' ); ?>

		<div class="golpe-edit__grid">
			<a class="golpe-edit__feature<?php echo $golpe_edit_image ? ' has-image' : ''; ?>" href="<?php echo esc_url( $golpe_edit_url ); ?>">
				<?php
				if ( $golpe_edit_image ) {
					echo wp_get_attachment_image(
						$golpe_edit_image,
						'large',
						false,
						array(
							'class'   => 'golpe-edit__image',
							'loading' => 'lazy',
							'alt'     => '',
						)
					);
				}
				?>
				<span class="golpe-edit__body">
					<span class="golpe-edit__eyebrow"><?php echo esc_html( golpe_option( 'edit_eyebrow' ) ); ?></span>
					<span class="golpe-edit__title"><?php echo esc_html( golpe_option( 'edit_title' ) ); ?></span>
					<span class="golpe-edit__text"><?php echo esc_html( golpe_option( 'edit_text' ) ); ?></span>
					<span class="golpe-edit__cta"><?php echo esc_html( golpe_option( 'edit_button_label' ) ); ?> <?php golpe_icon( 'arrow' ); ?></span>
				</span>
			</a>

			<div class="golpe-edit__products">
				<?php if ( $golpe_has_featured ) : ?>
					<?php echo do_shortcode( '[products limit="4" columns="2" visibility="featured" orderby="date" order="DESC"]' ); ?>
				<?php else : ?>
					<div class="product-placeholder-grid product-placeholder-grid--2" aria-hidden="true">
						<?php for ( $golpe_i = 0; $golpe_i < 4; $golpe_i++ ) : ?>
							<div class="product-placeholder">
								<span class="product-placeholder__image"></span>
								<span class="product-placeholder__line"></span>
								<span class="product-placeholder__line product-placeholder__line--short"></span>
							</div>
						<?php endfor; ?>
					</div>
					<?php if ( current_user_can( 'edit_products' ) ) : ?>
						<p class="empty-note"><?php esc_html_e( '[관리자에게만 보임] 상품 > 모든 상품에서 별(★)을 눌러 "추천 상품"으로 지정하면 이곳에 최대 4개가 표시됩니다.', 'golpe' ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
