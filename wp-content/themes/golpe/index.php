<?php
/**
 * 기본 템플릿 (글 목록, 검색 결과, 아카이브 등).
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="container page-container">
	<header class="page-header">
		<?php if ( is_search() ) : ?>
			<p class="section-eyebrow">SEARCH</p>
			<h1 class="page-title">
				<?php
				/* translators: %s: 검색어 */
				printf( esc_html__( '"%s" 검색 결과', 'golpe' ), esc_html( get_search_query() ) );
				?>
			</h1>
		<?php elseif ( is_archive() ) : ?>
			<h1 class="page-title"><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
		<?php elseif ( is_home() && ! is_front_page() ) : ?>
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		<?php else : ?>
			<h1 class="page-title"><?php esc_html_e( '소식', 'golpe' ); ?></h1>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="post-list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content' );
			endwhile;
			?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'prev_text' => esc_html__( '이전', 'golpe' ),
				'next_text' => esc_html__( '다음', 'golpe' ),
			)
		);
		?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
