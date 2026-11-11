<?php
/**
 * 메인 배너 (Hero) — 대형 이미지 + PLAY WITH PRESENCE. + SHOP NOW.
 *
 * 이미지: 사용자 정의하기 > GOLPE 테마 설정 > 메인 화면 > 메인 배너 이미지.
 * 이미지가 없으면 테마에 포함된 GOLPE 오리지널 흑백 일러스트(assets/images/hero-green.svg)를 보여줍니다.
 * ⚠️ 실제 사용 권한이 있는 사진만 올려 주세요. (타 브랜드 화보 · 로고 사용 금지)
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_hero_image_id = absint( golpe_option( 'hero_image' ) );
$golpe_button_url    = golpe_option( 'hero_button_url' );
$golpe_button_url    = $golpe_button_url ? $golpe_button_url : golpe_shop_url();
?>
<section class="hero<?php echo $golpe_hero_image_id ? ' has-image' : ' has-illustration'; ?>">
	<div class="hero__media">
		<?php
		if ( $golpe_hero_image_id ) {
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
		} else {
			printf(
				'<img class="hero__image" src="%s" alt="" width="2400" height="1400" fetchpriority="high" />',
				esc_url( GOLPE_URI . '/assets/images/hero-green.svg' )
			);
		}
		?>
	</div>

	<div class="container hero__inner">
		<p class="hero__eyebrow"><?php echo esc_html( golpe_option( 'hero_eyebrow' ) ); ?></p>
		<h1 class="hero__title"><?php echo esc_html( golpe_option( 'hero_title' ) ); ?></h1>
		<p class="hero__text"><?php echo esc_html( golpe_option( 'hero_text' ) ); ?></p>
		<a class="button button--light hero__cta" href="<?php echo esc_url( $golpe_button_url ); ?>"><?php echo esc_html( golpe_option( 'hero_button_label' ) ); ?></a>
	</div>
</section>
