/**
 * GOLPE 테마 스크립트
 * - 모바일 메뉴 열기/닫기
 * - 검색창 열기/닫기
 * - 스크롤 시 헤더 그림자
 * 외부 라이브러리(jQuery 등) 없이 동작합니다.
 */
( function () {
	'use strict';

	var body = document.body;

	/* 모바일 메뉴 ------------------------------------------------------- */
	var drawer = document.getElementById( 'mobile-drawer' );
	var openButtons = document.querySelectorAll( '[data-golpe-menu-open]' );
	var closeButtons = document.querySelectorAll( '[data-golpe-menu-close]' );
	var lastFocus = null;

	function setExpanded( buttons, value ) {
		buttons.forEach( function ( button ) {
			button.setAttribute( 'aria-expanded', value ? 'true' : 'false' );
		} );
	}

	function openDrawer() {
		if ( ! drawer ) {
			return;
		}
		lastFocus = document.activeElement;
		drawer.hidden = false;
		// 다음 프레임에 클래스를 붙여야 슬라이드 애니메이션이 동작합니다.
		window.requestAnimationFrame( function () {
			drawer.classList.add( 'is-open' );
		} );
		body.classList.add( 'is-locked' );
		setExpanded( openButtons, true );
		var firstLink = drawer.querySelector( 'a, button' );
		if ( firstLink ) {
			firstLink.focus();
		}
	}

	function closeDrawer() {
		if ( ! drawer || drawer.hidden ) {
			return;
		}
		drawer.classList.remove( 'is-open' );
		body.classList.remove( 'is-locked' );
		setExpanded( openButtons, false );
		window.setTimeout( function () {
			drawer.hidden = true;
		}, 350 );
		if ( lastFocus ) {
			lastFocus.focus();
		}
	}

	openButtons.forEach( function ( button ) {
		button.addEventListener( 'click', openDrawer );
	} );
	closeButtons.forEach( function ( button ) {
		button.addEventListener( 'click', closeDrawer );
	} );

	// 화면이 넓어지면 모바일 메뉴 자동 닫기.
	window.addEventListener( 'resize', function () {
		if ( window.innerWidth > 1024 ) {
			closeDrawer();
		}
	} );

	/* 검색창 ------------------------------------------------------------ */
	var searchPanel = document.getElementById( 'search-panel' );
	var searchToggles = document.querySelectorAll( '[data-golpe-search-toggle]' );

	function toggleSearch( force ) {
		if ( ! searchPanel ) {
			return;
		}
		var open = typeof force === 'boolean' ? force : searchPanel.hidden;
		searchPanel.hidden = ! open;
		setExpanded( searchToggles, open );
		if ( open ) {
			var field = searchPanel.querySelector( 'input[type="search"]' );
			if ( field ) {
				field.focus();
			}
		}
	}

	searchToggles.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			toggleSearch();
		} );
	} );

	/* ESC 키로 닫기 ----------------------------------------------------- */
	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' ) {
			return;
		}
		closeDrawer();
		if ( searchPanel && ! searchPanel.hidden ) {
			toggleSearch( false );
		}
	} );

	/* 스크롤 시 헤더 그림자 --------------------------------------------- */
	var header = document.querySelector( '[data-golpe-header]' );
	if ( header ) {
		var onScroll = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}
} )();
