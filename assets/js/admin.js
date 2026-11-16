/**
 * Skyhop 404 – settings screen: live theme previews and preview link.
 */
( function () {
	'use strict';

	var engine = window.Skyhop404;
	if ( ! engine ) {
		return;
	}

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var previews = [];

	document.querySelectorAll( '.sh-theme__canvas' ).forEach( function ( canvas ) {
		var item = { canvas: canvas, scene: engine.preview( canvas, canvas.getAttribute( 'data-theme' ) ) };
		previews.push( item );

		if ( canvas.hasAttribute( 'data-static' ) || reduceMotion ) {
			return;
		}

		var card = canvas.closest( '.sh-theme' );
		var raf = 0;
		var started = 0;

		function loop( now ) {
			if ( ! started ) {
				started = now;
			}
			item.scene.render( ( now - started ) / 1000 );
			raf = window.requestAnimationFrame( loop );
		}

		card.addEventListener( 'mouseenter', function () {
			started = 0;
			raf = window.requestAnimationFrame( loop );
		} );

		card.addEventListener( 'mouseleave', function () {
			window.cancelAnimationFrame( raf );
			item.scene.render( 0 );
		} );
	} );

	var resizeTimer = 0;
	window.addEventListener( 'resize', function () {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( function () {
			previews.forEach( function ( item ) {
				item.scene.resize();
				item.scene.render( 0 );
			} );
		}, 150 );
	} );

	// Keep the preview link in sync with unsaved theme / difficulty choices.
	var link = document.getElementById( 'sh-preview' );
	var form = document.querySelector( '.sh-form' );

	function checked( field ) {
		var input = form.querySelector( 'input[name="skyhop404_settings[' + field + ']"]:checked' );
		return input ? input.value : '';
	}

	function syncPreview() {
		var url = new URL( link.getAttribute( 'data-base' ) );
		url.searchParams.set( 'sh_theme', checked( 'theme' ) );
		url.searchParams.set( 'sh_difficulty', checked( 'difficulty' ) );
		link.href = url.toString();
	}

	if ( link && form ) {
		form.addEventListener( 'change', syncPreview );
		syncPreview();
	}
}() );
