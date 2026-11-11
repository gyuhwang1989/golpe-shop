/**
 * GOLPE 메인 페이지 스크립트
 * - 모바일 카테고리 가로 스크롤: 키보드(← → Home End) 한 칸씩 이동, 현재 위치(1 / 3) 표시
 * 메인 페이지에서만 불러오며, main.js(헤더·메뉴)와 별도 파일입니다.
 */
( function () {
	'use strict';

	var scrollers = document.querySelectorAll( '[data-golpe-cat-scroller]' );
	if ( ! scrollers.length ) {
		return;
	}

	scrollers.forEach( function ( scroller ) {
		var section = scroller.closest( 'section' ) || document;
		var counter = section.querySelector( '[data-golpe-cat-count]' );
		var tiles = scroller.querySelectorAll( '.category-tile' );

		function step() {
			if ( tiles.length < 2 ) {
				return scroller.clientWidth;
			}
			return tiles[ 1 ].offsetLeft - tiles[ 0 ].offsetLeft;
		}

		function isScrollable() {
			return scroller.scrollWidth > scroller.clientWidth + 2;
		}

		function currentIndex() {
			var s = step();
			if ( ! s ) {
				return 0;
			}
			if ( scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth - 2 ) {
				return tiles.length - 1;
			}
			return Math.max( 0, Math.min( tiles.length - 1, Math.round( scroller.scrollLeft / s ) ) );
		}

		function update() {
			// 가로 스크롤이 있을 때만(모바일) 목록 자체에 키보드 포커스를 줍니다.
			if ( isScrollable() ) {
				scroller.setAttribute( 'tabindex', '0' );
			} else {
				scroller.removeAttribute( 'tabindex' );
			}
			if ( counter ) {
				counter.textContent = ( currentIndex() + 1 ) + ' / ' + tiles.length;
			}
			section.classList.toggle( 'is-scrolled-end', isScrollable() && currentIndex() === tiles.length - 1 );
		}

		function goTo( index ) {
			index = Math.max( 0, Math.min( tiles.length - 1, index ) );
			var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
			scroller.scrollTo( { left: index * step(), behavior: reduce ? 'auto' : 'smooth' } );
		}

		scroller.addEventListener( 'keydown', function ( event ) {
			if ( ! isScrollable() || event.target !== scroller ) {
				return;
			}
			var map = { ArrowRight: 1, ArrowLeft: -1 };
			if ( event.key in map ) {
				event.preventDefault();
				goTo( currentIndex() + map[ event.key ] );
			} else if ( event.key === 'Home' ) {
				event.preventDefault();
				goTo( 0 );
			} else if ( event.key === 'End' ) {
				event.preventDefault();
				goTo( tiles.length - 1 );
			}
		} );

		// Tab 으로 카드 안 링크에 들어가면, 그 카드가 화면에 다 보이도록 맞춰 스크롤합니다.
		scroller.addEventListener( 'focusin', function ( event ) {
			if ( ! isScrollable() || event.target === scroller ) {
				return;
			}
			var tile = event.target.closest( '.category-tile' );
			var index = Array.prototype.indexOf.call( tiles, tile );
			if ( index < 0 ) {
				return;
			}
			var r = tile.getBoundingClientRect();
			var box = scroller.getBoundingClientRect();
			if ( r.left < box.left || r.right > box.right ) {
				goTo( index );
			}
		} );

		var ticking = false;
		scroller.addEventListener(
			'scroll',
			function () {
				if ( ticking ) {
					return;
				}
				ticking = true;
				window.requestAnimationFrame( function () {
					update();
					ticking = false;
				} );
			},
			{ passive: true }
		);
		window.addEventListener( 'resize', update );
		update();
	} );
} )();
