<?php
/**
 * 메인 홈페이지.
 *
 * 섹션 순서를 바꾸고 싶으면 아래 get_template_part 줄의 순서만 바꾸면 됩니다.
 * (brand · category-index · service 섹션 파일은 남아 있으며, 필요하면 아래에 다시 추가할 수 있습니다.)
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/home/hero' );         // 2. 메인 배너.
get_template_part( 'template-parts/home/categories' );   // 3. 카테고리.
get_template_part( 'template-parts/home/new-arrivals' ); // 4. NEW ARRIVALS.
get_template_part( 'template-parts/home/golpe-edit' );   // 5. GOLPE EDIT.
get_template_part( 'template-parts/home/agency-intro' ); // 6. 해외구매대행 안내.

get_footer();
