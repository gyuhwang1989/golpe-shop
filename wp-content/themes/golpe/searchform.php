<?php
/**
 * 검색 폼. WooCommerce 가 활성화되어 있으면 상품만 검색합니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_search_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $golpe_search_id ); ?>"><?php esc_html_e( '상품 검색', 'golpe' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $golpe_search_id ); ?>" class="search-field" placeholder="<?php esc_attr_e( '찾으시는 상품을 입력하세요', 'golpe' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<?php if ( golpe_is_woocommerce_active() ) : ?>
		<input type="hidden" name="post_type" value="product" />
	<?php endif; ?>
	<button type="submit" class="search-submit">
		<?php golpe_icon( 'search' ); ?>
		<span class="screen-reader-text"><?php esc_html_e( '검색', 'golpe' ); ?></span>
	</button>
</form>
