<?php
/**
 * 사이트 헤더.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_announcement = trim( (string) golpe_option( 'announcement' ) );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( '본문 바로가기', 'golpe' ); ?></a>

<?php if ( '' !== $golpe_announcement ) : ?>
	<div class="announcement-bar">
		<p class="container"><?php echo esc_html( $golpe_announcement ); ?></p>
	</div>
<?php endif; ?>

<header class="site-header" data-golpe-header>
	<div class="container header-inner">
		<button class="icon-button menu-toggle" type="button" aria-controls="mobile-drawer" aria-expanded="false" data-golpe-menu-open>
			<?php golpe_icon( 'menu' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( '메뉴 열기', 'golpe' ); ?></span>
		</button>

		<div class="site-branding">
			<?php golpe_site_logo(); ?>
		</div>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( '메인 메뉴', 'golpe' ); ?>">
			<?php golpe_primary_menu( 'desktop' ); ?>
		</nav>

		<div class="header-actions">
			<button class="icon-button" type="button" aria-controls="search-panel" aria-expanded="false" data-golpe-search-toggle>
				<?php golpe_icon( 'search' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '검색', 'golpe' ); ?></span>
			</button>
			<a class="icon-button header-account" href="<?php echo esc_url( golpe_account_url() ); ?>">
				<?php golpe_icon( 'user' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '마이페이지', 'golpe' ); ?></span>
			</a>
			<a class="icon-button header-cart" href="<?php echo esc_url( golpe_cart_url() ); ?>">
				<?php golpe_icon( 'bag' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '장바구니', 'golpe' ); ?></span>
				<?php echo golpe_get_cart_count_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 내부에서 이스케이프. ?>
			</a>
		</div>
	</div>

	<div class="search-panel" id="search-panel" hidden>
		<div class="container">
			<?php get_search_form(); ?>
		</div>
	</div>
</header>

<div class="mobile-drawer" id="mobile-drawer" hidden>
	<div class="mobile-drawer__overlay" data-golpe-menu-close></div>
	<div class="mobile-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( '모바일 메뉴', 'golpe' ); ?>">
		<div class="mobile-drawer__head">
			<?php golpe_site_logo(); ?>
			<button class="icon-button" type="button" data-golpe-menu-close>
				<?php golpe_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '메뉴 닫기', 'golpe' ); ?></span>
			</button>
		</div>
		<nav aria-label="<?php esc_attr_e( '모바일 메뉴', 'golpe' ); ?>">
			<?php golpe_primary_menu( 'mobile' ); ?>
		</nav>
		<div class="mobile-drawer__links">
			<a href="<?php echo esc_url( golpe_account_url() ); ?>"><?php esc_html_e( '마이페이지', 'golpe' ); ?></a>
			<a href="<?php echo esc_url( golpe_cart_url() ); ?>"><?php esc_html_e( '장바구니', 'golpe' ); ?></a>
		</div>
	</div>
</div>

<main id="main" class="site-main">
