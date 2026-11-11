<?php
/**
 * 사이트 푸터.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_business  = golpe_business_info();
$golpe_cs_phone  = trim( (string) golpe_option( 'biz_phone' ) );
$golpe_cs_email  = trim( (string) golpe_option( 'biz_email' ) );
$golpe_cs_hours  = function_exists( 'golpe_agency_option' ) ? trim( (string) golpe_agency_option( 'agency_cs_hours' ) ) : '';
$golpe_terms     = golpe_terms_url();
$golpe_privacy   = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
$golpe_guide_url = function_exists( 'golpe_agency_guide_url' ) ? golpe_agency_guide_url() : '';
?>
</main>

<footer class="site-footer">
	<div class="container footer-grid">
		<div class="footer-brand">
			<?php golpe_site_logo(); ?>
			<p><?php esc_html_e( '중국 현지 골프백 · 골프웨어 · 액세서리 해외구매대행', 'golpe' ); ?></p>
		</div>

		<div class="footer-col">
			<h2 class="footer-title">SHOP</h2>
			<ul>
				<li><a href="<?php echo esc_url( golpe_shop_url() ); ?>"><?php esc_html_e( '전체상품', 'golpe' ); ?></a></li>
				<?php foreach ( golpe_get_category_groups() as $golpe_group ) : ?>
					<li><a href="<?php echo esc_url( golpe_category_url( $golpe_group['slug'] ) ); ?>"><?php echo esc_html( $golpe_group['name'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="footer-col footer-cs">
			<h2 class="footer-title">CUSTOMER CENTER</h2>
			<?php if ( '' !== $golpe_cs_phone ) : ?>
				<p class="footer-cs__phone"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $golpe_cs_phone ) ); ?>"><?php echo esc_html( $golpe_cs_phone ); ?></a></p>
			<?php endif; ?>
			<?php if ( '' !== $golpe_cs_hours ) : ?>
				<p class="footer-cs__meta"><?php echo esc_html( $golpe_cs_hours ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $golpe_cs_email ) : ?>
				<p class="footer-cs__meta"><a href="mailto:<?php echo esc_attr( $golpe_cs_email ); ?>"><?php echo esc_html( $golpe_cs_email ); ?></a></p>
			<?php endif; ?>
			<?php if ( '' === $golpe_cs_phone && '' === $golpe_cs_hours && current_user_can( 'edit_theme_options' ) ) : ?>
				<p class="admin-note"><?php esc_html_e( '[관리자에게만 보임] 사업자 정보의 고객센터 전화번호와 구매대행 안내의 운영시간을 입력해 주세요.', 'golpe' ); ?></p>
			<?php endif; ?>
			<ul>
				<li><a href="<?php echo esc_url( golpe_account_url() ); ?>"><?php esc_html_e( '마이페이지', 'golpe' ); ?></a></li>
				<li><a href="<?php echo esc_url( golpe_cart_url() ); ?>"><?php esc_html_e( '장바구니', 'golpe' ); ?></a></li>
				<?php if ( golpe_is_woocommerce_active() ) : ?>
					<li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( '주문조회', 'golpe' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

		<?php if ( ! empty( $golpe_business ) || current_user_can( 'edit_theme_options' ) ) : ?>
		<div class="footer-col footer-info">
			<h2 class="footer-title">COMPANY</h2>
			<?php if ( ! empty( $golpe_business ) ) : ?>
				<dl class="business-info">
					<?php foreach ( $golpe_business as $golpe_label => $golpe_value ) : ?>
						<div><dt><?php echo esc_html( $golpe_label ); ?></dt><dd><?php echo esc_html( $golpe_value ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
				<p class="admin-note"><?php esc_html_e( '[관리자에게만 보임] 외모 > 사용자 정의하기 > GOLPE 테마 설정 > 사업자 정보를 입력해 주세요.', 'golpe' ); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>

	<div class="container footer-bottom">
		<ul class="footer-legal">
			<?php if ( $golpe_terms ) : ?>
				<li><a href="<?php echo esc_url( $golpe_terms ); ?>"><?php esc_html_e( '이용약관', 'golpe' ); ?></a></li>
			<?php endif; ?>
			<?php if ( $golpe_privacy ) : ?>
				<li><a class="footer-legal__privacy" href="<?php echo esc_url( $golpe_privacy ); ?>"><?php esc_html_e( '개인정보처리방침', 'golpe' ); ?></a></li>
			<?php endif; ?>
			<?php if ( $golpe_guide_url ) : ?>
				<li><a href="<?php echo esc_url( $golpe_guide_url ); ?>"><?php esc_html_e( '구매대행 이용안내', 'golpe' ); ?></a></li>
			<?php endif; ?>
		</ul>
		<?php if ( ( ! $golpe_terms || ! $golpe_privacy ) && current_user_can( 'edit_theme_options' ) ) : ?>
			<p class="admin-note"><?php esc_html_e( '[관리자에게만 보임] 이용약관 · 개인정보처리방침 페이지를 공개하고 연결해 주세요. (사용자 정의하기 > 사업자 정보, 설정 > 개인정보)', 'golpe' ); ?></p>
		<?php endif; ?>
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'GOLPE' ); ?>. All rights reserved.</p>
		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer-menu',
					'depth'          => 1,
				)
			);
		}
		?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
