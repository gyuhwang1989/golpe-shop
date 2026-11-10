<?php
/**
 * 일반 페이지 템플릿.
 * WooCommerce 의 장바구니 · 결제 · 마이페이지도 이 템플릿 안에서 표시됩니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="container page-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>
			<header class="page-header">
				<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
			</header>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
		</article>
	<?php endwhile; ?>
</div>
<?php
get_footer();
