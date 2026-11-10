<?php
/**
 * 결과 없음.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="no-results">
	<p>
		<?php
		if ( is_search() ) {
			esc_html_e( '검색 결과가 없습니다. 다른 검색어로 다시 찾아보세요.', 'golpe' );
		} else {
			esc_html_e( '아직 등록된 내용이 없습니다.', 'golpe' );
		}
		?>
	</p>
	<?php get_search_form(); ?>
</div>
