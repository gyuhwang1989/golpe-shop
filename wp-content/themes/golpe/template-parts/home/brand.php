<?php
/**
 * 브랜드 소개 띠 배너.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="brand-band">
	<div class="container brand-band__inner">
		<p class="section-eyebrow">THE GOLPE STANDARD</p>
		<h2 class="brand-band__title"><?php echo esc_html( golpe_option( 'brand_title' ) ); ?></h2>
		<p class="brand-band__text"><?php echo esc_html( golpe_option( 'brand_text' ) ); ?></p>
	</div>
	<div class="brand-band__marquee" aria-hidden="true">
		<span>BLACK &amp; WHITE — GOLF BAGS — GOLF WEAR — ACCESSORIES — BLACK &amp; WHITE — GOLF BAGS — GOLF WEAR — ACCESSORIES —&nbsp;</span>
		<span>BLACK &amp; WHITE — GOLF BAGS — GOLF WEAR — ACCESSORIES — BLACK &amp; WHITE — GOLF BAGS — GOLF WEAR — ACCESSORIES —&nbsp;</span>
	</div>
</section>
