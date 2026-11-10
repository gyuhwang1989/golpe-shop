<?php
/**
 * 카테고리 타일 (골프백 · 골프웨어 · 액세서리).
 *
 * 카테고리 썸네일 이미지가 있으면 사진을, 없으면 블랙/화이트 타이포그래피 배경을 보여줍니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="section section--categories" id="categories">
	<div class="container">
		<?php golpe_section_heading( 'SHOP BY CATEGORY', __( '카테고리별 쇼핑', 'golpe' ) ); ?>

		<div class="category-tiles">
			<?php
			$golpe_index = 0;
			foreach ( golpe_get_category_groups() as $golpe_group ) :
				$golpe_index++;
				$golpe_image = golpe_category_image_url( $golpe_group['slug'] );
				$golpe_tone  = ( 'light' === $golpe_group['tone'] ) ? 'light' : 'dark';
				?>
				<article class="category-tile category-tile--<?php echo esc_attr( $golpe_tone ); ?><?php echo $golpe_image ? ' has-image' : ''; ?>">
					<a class="category-tile__link" href="<?php echo esc_url( golpe_category_url( $golpe_group['slug'] ) ); ?>">
						<?php if ( $golpe_image ) : ?>
							<img class="category-tile__image" src="<?php echo esc_url( $golpe_image ); ?>" alt="" loading="lazy" />
						<?php else : ?>
							<span class="category-tile__mark" aria-hidden="true"><?php echo esc_html( $golpe_group['mark'] ); ?></span>
						<?php endif; ?>
						<span class="category-tile__body">
							<span class="category-tile__num"><?php echo esc_html( sprintf( '%02d', $golpe_index ) ); ?></span>
							<span class="category-tile__en"><?php echo esc_html( $golpe_group['en'] ); ?></span>
							<span class="category-tile__name"><?php echo esc_html( $golpe_group['name'] ); ?></span>
							<span class="category-tile__desc"><?php echo esc_html( $golpe_group['description'] ); ?></span>
							<span class="category-tile__cta"><?php esc_html_e( '쇼핑하기', 'golpe' ); ?> <?php golpe_icon( 'arrow' ); ?></span>
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
