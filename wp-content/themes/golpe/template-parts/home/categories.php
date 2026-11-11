<?php
/**
 * 카테고리 (GOLF BAGS · GOLF WEAR · ACCESSORIES).
 *
 * 카테고리 썸네일 이미지가 있으면 사진을, 없으면 #F5F5F5 배경 + GOLPE 오리지널 선 아이콘을 보여줍니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_category_icons = array(
	'golf-bags'   => 'golfbag',
	'golf-wear'   => 'shirt',
	'accessories' => 'ball',
);
?>
<section class="section section--categories" id="categories">
	<div class="container">
		<?php golpe_section_heading( __( '카테고리', 'golpe' ), 'CATEGORY' ); ?>

		<div class="category-tiles">
			<?php
			foreach ( golpe_get_category_groups() as $golpe_group ) :
				$golpe_image = golpe_category_image_url( $golpe_group['slug'] );
				$golpe_icon  = isset( $golpe_category_icons[ $golpe_group['slug'] ] ) ? $golpe_category_icons[ $golpe_group['slug'] ] : 'bag';
				?>
				<article class="category-tile<?php echo $golpe_image ? ' has-image' : ''; ?>">
					<a class="category-tile__link" href="<?php echo esc_url( golpe_category_url( $golpe_group['slug'] ) ); ?>">
						<span class="category-tile__media">
							<?php if ( $golpe_image ) : ?>
								<img class="category-tile__image" src="<?php echo esc_url( $golpe_image ); ?>" alt="" loading="lazy" />
							<?php else : ?>
								<span class="category-tile__icon" aria-hidden="true"><?php golpe_icon( $golpe_icon ); ?></span>
							<?php endif; ?>
						</span>
						<span class="category-tile__body">
							<span class="category-tile__en"><?php echo esc_html( $golpe_group['en'] ); ?></span>
							<span class="category-tile__name"><?php echo esc_html( $golpe_group['name'] ); ?></span>
						</span>
					</a>
					<?php if ( ! empty( $golpe_group['children'] ) ) : ?>
						<ul class="category-tile__subs">
							<?php foreach ( $golpe_group['children'] as $golpe_child ) : ?>
								<li><a href="<?php echo esc_url( golpe_category_url( $golpe_child['slug'] ) ); ?>"><?php echo esc_html( $golpe_child['name'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
