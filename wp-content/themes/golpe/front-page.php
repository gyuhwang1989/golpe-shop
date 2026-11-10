<?php
/**
 * 메인 홈페이지.
 *
 * 섹션 순서를 바꾸고 싶으면 아래 get_template_part 줄의 순서만 바꾸면 됩니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/categories' );
get_template_part( 'template-parts/home/new-arrivals' );
get_template_part( 'template-parts/home/brand' );
get_template_part( 'template-parts/home/category-index' );
get_template_part( 'template-parts/home/service' );

get_footer();
