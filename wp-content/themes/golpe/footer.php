<?php
/**
 * 사이트 푸터.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_business = golpe_business_info();
?>
</main>

<footer class="site-footer">
	<div class="container footer-grid">
		<div class="footer-brand">
			<?php golpe_site_logo(); ?>
			<p><?php esc_html_e( '프리미엄 골프백 · 골프웨어 · 액세서리 셀렉트숍', 'golpe' ); ?></p>
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

		<div class="footer-col">
			<h2 class="footer-title">CUSTOMER</h2>
			<ul>
				<li><a href="<?php echo esc_url( golpe_account_url() ); ?>"><?php esc_html_e( '마이페이지', 'golpe' ); ?></a></li>
				<li><a href="<?php echo esc_url( golpe_cart_url() ); ?>"><?php esc_html_e( '장바구니', 'golpe' ); ?></a></li>
				<?php if ( golpe_is_woocommerce_active() ) : ?>
					<li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( '주문조회', 'golpe' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="footer-col footer-info">
			<h2 class="footer-title">INFO</h2>
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
	</div>

	<div class="container footer-bottom">
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
