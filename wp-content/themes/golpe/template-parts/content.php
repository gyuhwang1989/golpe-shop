<?php
/**
 * 목록용 글 카드.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
	<a class="post-card__link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<span class="post-card__thumb"><?php the_post_thumbnail( 'medium_large' ); ?></span>
		<?php endif; ?>
		<span class="post-card__body">
			<span class="post-card__meta"><?php echo esc_html( get_the_date() ); ?></span>
			<?php the_title( '<span class="post-card__title">', '</span>' ); ?>
			<span class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></span>
		</span>
	</a>
</article>
