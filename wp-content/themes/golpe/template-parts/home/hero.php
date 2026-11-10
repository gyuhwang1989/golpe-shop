<?php
/**
 * 메인 배너 (Hero).
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_hero_image_id = absint( golpe_option( 'hero_image' ) );
$golpe_button_url    = golpe_option( 'hero_button_url' );
$golpe_button_url    = $golpe_button_url ? $golpe_button_url : golpe_shop_url();
$golpe_groups        = golpe_get_category_groups();
?>
<section class="hero<?php echo $golpe_hero_image_id ? ' has-image' : ''; ?>">
	<?php if ( $golpe_hero_image_id ) : ?>
		<div class="hero__media">
			<?php
			echo wp_get_attachment_image(
				$golpe_hero_image_id,
				'full',
				false,
				array(
					'class'         => 'hero__image',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'alt'           => '',
				)
			);
			?>
		</div>
	<?php else : ?>
		<div class="hero__mark" aria-hidden="true">GOLPE</div>
	<?php endif; ?>

	<div class="container hero__inner">
		<p class="hero__eyebrow"><?php echo esc_html( golpe_option( 'hero_eyebrow' ) ); ?></p>
		<h1 class="hero__title"><?php echo esc_html( golpe_option( 'hero_title' ) ); ?></h1>
		<p class="hero__text"><?php echo esc_html( golpe_option( 'hero_text' ) ); ?></p>
		<div class="hero__actions">
			<a class="button button--light" href="<?php echo esc_url( $golpe_button_url ); ?>"><?php echo esc_html( golpe_option( 'hero_button_label' ) ); ?></a>
			<?php if ( ! empty( $golpe_groups[0] ) ) : ?>
				<a class="button button--ghost-light" href="<?php echo esc_url( golpe_category_url( $golpe_groups[0]['slug'] ) ); ?>"><?php echo esc_html( $golpe_groups[0]['name'] ); ?> <?php esc_html_e( '보러가기', 'golpe' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="hero__scroll" aria-hidden="true"><span>SCROLL</span></div>
</section>
