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
	var drawerPanel = drawer ? drawer.querySelector( '.mobile-drawer__panel' ) : null;
	var openButtons = document.querySelectorAll( '[data-golpe-menu-open]' );
	var closeButtons = document.querySelectorAll( '[data-golpe-menu-close]' );
	var lastFocus = null;
	var hideTimer = null;
	var inertTargets = [];
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	function setExpanded( buttons, value ) {
		buttons.forEach( function ( button ) {
			button.setAttribute( 'aria-expanded', value ? 'true' : 'false' );
		} );
	}

	// 메뉴가 열려 있는 동안 뒤쪽 화면(헤더·본문·푸터)을 조작·낭독할 수 없게 합니다.
	function setBackgroundInert( value ) {
		if ( value ) {
			inertTargets = Array.prototype.filter.call( body.children, function ( el ) {
				return el !== drawer && ! el.hasAttribute( 'inert' ) && el.tagName !== 'SCRIPT';
			} );
			inertTargets.forEach( function ( el ) {
				el.setAttribute( 'inert', '' );
			} );
		} else {
			inertTargets.forEach( function ( el ) {
				el.removeAttribute( 'inert' );
			} );
			inertTargets = [];
		}
	}

	function getDrawerFocusables() {
		if ( ! drawerPanel ) {
			return [];
		}
		return Array.prototype.filter.call( drawerPanel.querySelectorAll( FOCUSABLE ), function ( el ) {
			return el.offsetParent !== null;
		} );
	}

	function openDrawer() {
		if ( ! drawer ) {
			return;
		}
		// 닫히는 중에 다시 열면, 예약된 숨김 처리를 취소합니다.
		if ( hideTimer ) {
			window.clearTimeout( hideTimer );
			hideTimer = null;
		}
		lastFocus = document.activeElement;
		drawer.hidden = false;
		// 다음 프레임에 클래스를 붙여야 슬라이드 애니메이션이 동작합니다.
		window.requestAnimationFrame( function () {
			drawer.classList.add( 'is-open' );
		} );
		body.classList.add( 'is-locked' );
		setExpanded( openButtons, true );
		setBackgroundInert( true );

		// 첫 포커스는 '메뉴 닫기' 버튼에 둡니다.
		var closeButton = drawer.querySelector( '.mobile-drawer__head [data-golpe-menu-close]' );
		var target = closeButton || getDrawerFocusables()[ 0 ];
		if ( target ) {
			target.focus();
		}
	}

	function closeDrawer( restoreFocus ) {
		if ( ! drawer || drawer.hidden || ! drawer.classList.contains( 'is-open' ) ) {
			return;
		}
		drawer.classList.remove( 'is-open' );
		body.classList.remove( 'is-locked' );
		setExpanded( openButtons, false );
		setBackgroundInert( false );
		hideTimer = window.setTimeout( function () {
			drawer.hidden = true;
			hideTimer = null;
		}, 350 );
		if ( restoreFocus !== false && lastFocus && document.contains( lastFocus ) ) {
			lastFocus.focus();
		}
	}

	openButtons.forEach( function ( button ) {
		button.addEventListener( 'click', openDrawer );
	} );
	closeButtons.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			closeDrawer();
		} );
	} );

	if ( drawer ) {
		// Tab / Shift+Tab 이 메뉴 안에서만 돌도록 합니다. (포커스 가두기)
		drawer.addEventListener( 'keydown', function ( event ) {
			if ( event.key !== 'Tab' ) {
				return;
			}
			var items = getDrawerFocusables();
			if ( ! items.length ) {
				return;
			}
			var first = items[ 0 ];
			var last = items[ items.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		} );

		// 메뉴 안 링크를 누르면 메뉴를 닫습니다. (같은 페이지 내 이동 대비)
		drawer.addEventListener( 'click', function ( event ) {
			if ( event.target.closest && event.target.closest( '.mobile-drawer__panel a[href]' ) ) {
				closeDrawer( false );
			}
		} );
	}

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
			// 검색창을 닫으면 포커스를 검색 버튼으로 되돌립니다.
			if ( searchToggles.length ) {
				searchToggles[ 0 ].focus();
			}
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

/**
 * 구매대행 이용 안내 페이지: 현재 보고 있는 섹션을 목차에 표시합니다.
 */
( function () {
	'use strict';

	var toc = document.querySelector( '[data-golpe-guide-toc]' );
	if ( ! toc || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	var links = toc.querySelectorAll( 'a[href^="#"]' );
	var map = {};
	links.forEach( function ( link ) {
		var section = document.getElementById( link.getAttribute( 'href' ).slice( 1 ) );
		if ( section ) {
			map[ section.id ] = link;
		}
	} );

	function setCurrent( id ) {
		links.forEach( function ( link ) {
			link.classList.remove( 'is-current' );
			link.removeAttribute( 'aria-current' );
		} );
		var active = map[ id ];
		if ( ! active ) {
			return;
		}
		active.classList.add( 'is-current' );
		active.setAttribute( 'aria-current', 'true' );
		// 모바일 가로 목차에서 현재 항목이 보이도록 스크롤합니다.
		if ( toc.scrollWidth > toc.clientWidth ) {
			toc.scrollTo( {
				left: active.offsetLeft - ( toc.clientWidth - active.offsetWidth ) / 2,
				behavior: 'smooth',
			} );
		}
	}

	var observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					setCurrent( entry.target.id );
				}
			} );
		},
		{ rootMargin: '-35% 0px -60% 0px' }
	);

	Object.keys( map ).forEach( function ( id ) {
		observer.observe( document.getElementById( id ) );
	} );
} )();
