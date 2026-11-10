<?php
/**
 * 글(포스트) 상세 템플릿.
 * 상품 상세 페이지는 WooCommerce 가 별도로 처리합니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="container page-container page-container--narrow">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>
			<header class="page-header">
				<p class="section-eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
				<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="single-article__thumb"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
		</article>
		<?php
		the_post_navigation(
			array(
				'prev_text' => esc_html__( '이전 글', 'golpe' ),
				'next_text' => esc_html__( '다음 글', 'golpe' ),
			)
		);
	endwhile;
	?>
</div>
<?php
get_footer();
