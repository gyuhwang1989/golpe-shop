<?php
/**
 * 404 (페이지 없음).
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="container page-container error-404">
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="page-title"><?php esc_html_e( '페이지를 찾을 수 없습니다.', 'golpe' ); ?></h1>
	<p><?php esc_html_e( '주소가 바뀌었거나 삭제된 페이지입니다. 상품을 검색해 보세요.', 'golpe' ); ?></p>
	<?php get_search_form(); ?>
	<p><a class="button" href="<?php echo esc_url( golpe_shop_url() ); ?>"><?php esc_html_e( '쇼핑 계속하기', 'golpe' ); ?></a></p>
</div>
<?php
get_footer();
