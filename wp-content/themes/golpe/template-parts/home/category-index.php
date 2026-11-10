<?php
/**
 * 세부 카테고리 인덱스 (캐디백, 스탠드백, 보스턴백, 의류, 모자, 신발 ...).
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="section section--index">
	<div class="container">
		<?php golpe_section_heading( 'CATEGORY INDEX', __( '세부 카테고리', 'golpe' ) ); ?>

		<div class="category-index">
			<?php foreach ( golpe_get_category_groups() as $golpe_group ) : ?>
				<div class="category-index__group">
					<h3 class="category-index__title">
						<a href="<?php echo esc_url( golpe_category_url( $golpe_group['slug'] ) ); ?>"><?php echo esc_html( $golpe_group['en'] ); ?></a>
					</h3>
					<ul class="category-index__list">
						<?php foreach ( $golpe_group['children'] as $golpe_child ) : ?>
							<?php $golpe_count = golpe_category_count( $golpe_child['slug'] ); ?>
							<li>
								<a href="<?php echo esc_url( golpe_category_url( $golpe_child['slug'] ) ); ?>">
									<span class="category-index__name"><?php echo esc_html( $golpe_child['name'] ); ?></span>
									<span class="category-index__en"><?php echo esc_html( $golpe_child['en'] ); ?></span>
									<?php if ( null !== $golpe_count ) : ?>
										<span class="category-index__count"><?php echo esc_html( (string) $golpe_count ); ?></span>
									<?php endif; ?>
									<?php golpe_icon( 'arrow' ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
