/**
 * Skyhop 404 – game engine and themes.
 *
 * The world is a fixed 600 units tall; its width follows the viewport's aspect
 * ratio. Every theme draws in world units, so art scales cleanly on any screen.
 *
 * Exposes window.Skyhop404 = { themes, mount, preview }.
 */
( function ( window, document ) {
	'use strict';

	var WORLD_H = 600;
	var GROUND = 64;
	var FLOOR = WORLD_H - GROUND;
	var COL_W = 72;
	var PI = Math.PI;
	var TAU = PI * 2;
	var DIGITS = '404';
	var BEST_KEY = 'skyhop404.best';
	var THEME_KEY = 'skyhop404.theme';
	var SOUND_KEY = 'skyhop404.sound';

	var DIFFICULTY = {
		easy: { gravity: 1250, flap: 400, speed: 150, gap: 210, spacing: 280 },
		normal: { gravity: 1500, flap: 440, speed: 180, gap: 178, spacing: 255 },
		hard: { gravity: 1750, flap: 480, speed: 215, gap: 152, spacing: 238 }
	};

	/* Helpers ------------------------------------------------------------ */

	function clamp( v, min, max ) {
		return v < min ? min : v > max ? max : v;
	}

	function mod( a, n ) {
		return ( ( a % n ) + n ) % n;
	}

	function seeded( seed ) {
		return function () {
			seed = ( seed + 0x6d2b79f5 ) | 0;
			var t = Math.imul( seed ^ ( seed >>> 15 ), 1 | seed );
			t = ( t + Math.imul( t ^ ( t >>> 7 ), 61 | t ) ) ^ t;
			return ( ( t ^ ( t >>> 14 ) ) >>> 0 ) / 4294967296;
		};
	}

	function hash( a, b, c ) {
		var h = Math.imul( a | 0, 374761393 ) + Math.imul( b | 0, 668265263 ) + Math.imul( c | 0, 1274126177 );
		h = Math.imul( h ^ ( h >>> 13 ), 1274126177 );
		return ( h ^ ( h >>> 16 ) ) >>> 0;
	}

	function circle( ctx, x, y, r ) {
		ctx.beginPath();
		ctx.arc( x, y, r, 0, TAU );
	}

	function ellipse( ctx, x, y, rx, ry, rot ) {
		ctx.beginPath();
		ctx.ellipse( x, y, rx, ry, rot || 0, 0, TAU );
	}

	function rrect( ctx, x, y, w, h, r ) {
		r = Math.max( 0, Math.min( r, w / 2, h / 2 ) );
		ctx.beginPath();
		ctx.moveTo( x + r, y );
		ctx.arcTo( x + w, y, x + w, y + h, r );
		ctx.arcTo( x + w, y + h, x, y + h, r );
		ctx.arcTo( x, y + h, x, y, r );
		ctx.arcTo( x, y, x + w, y, r );
		ctx.closePath();
	}

	function vgrad( ctx, y0, y1, stops ) {
		var g = ctx.createLinearGradient( 0, y0, 0, y1 );
		for ( var i = 0; i < stops.length; i++ ) {
			g.addColorStop( i / ( stops.length - 1 ), stops[ i ] );
		}
		return g;
	}

	function hgrad( ctx, x0, x1, stops ) {
		var g = ctx.createLinearGradient( x0, 0, x1, 0 );
		for ( var i = 0; i < stops.length; i++ ) {
			g.addColorStop( i / ( stops.length - 1 ), stops[ i ] );
		}
		return g;
	}

	function glow( ctx, x, y, r, color ) {
		var g = ctx.createRadialGradient( x, y, 0, x, y, r );
		g.addColorStop( 0, color );
		g.addColorStop( 1, 'rgba(0,0,0,0)' );
		ctx.fillStyle = g;
		ctx.fillRect( x - r, y - r, r * 2, r * 2 );
	}

	function hills( ctx, w, baseY, offset, height, amp, period, color ) {
		ctx.fillStyle = color;
		ctx.beginPath();
		ctx.moveTo( -4, baseY + 2 );
		for ( var x = -4; x <= w + 8; x += 8 ) {
			var u = ( ( x + offset ) / period ) * TAU;
			ctx.lineTo( x, baseY - height + Math.sin( u ) * amp + Math.sin( u * 2.3 + 1.7 ) * amp * 0.35 );
		}
		ctx.lineTo( w + 8, baseY + 2 );
		ctx.closePath();
		ctx.fill();
	}

	function cloud( ctx, x, y, s ) {
		circle( ctx, x + 14 * s, y - 2 * s, 15 * s );
		ctx.fill();
		circle( ctx, x + 36 * s, y - 12 * s, 21 * s );
		ctx.fill();
		circle( ctx, x + 60 * s, y - 3 * s, 14 * s );
		ctx.fill();
		rrect( ctx, x, y - 2 * s, 76 * s, 16 * s, 8 * s );
		ctx.fill();
	}

	function scatter( r, count, make ) {
		var out = [];
		for ( var i = 0; i < count; i++ ) {
			out.push( make( r, i ) );
		}
		return out;
	}

	/* Themes ------------------------------------------------------------- */

	var THEMES = {};

	THEMES.meadow = {
		base: '#bfe6fb',
		accent: '#e8692c',
		init: function ( r ) {
			return {
				clouds: scatter( r, 7, function ( r ) {
					return { x: r() * 1400, y: 50 + r() * 170, s: 0.6 + r() * 0.7 };
				} )
			};
		},
		bg: function ( ctx, w, h, t, s, d ) {
			ctx.fillStyle = vgrad( ctx, 0, FLOOR, [ '#5db8ec', '#a6dcf7', '#e4f5fd' ] );
			ctx.fillRect( 0, 0, w, h );

			var sx = w * 0.8;
			glow( ctx, sx, 108, 150, 'rgba(255,236,160,0.75)' );
			ctx.fillStyle = '#ffe37a';
			circle( ctx, sx, 108, 38 );
			ctx.fill();

			ctx.fillStyle = 'rgba(255,255,255,0.95)';
			var span = w + 240;
			d.clouds.forEach( function ( c ) {
				cloud( ctx, mod( c.x - s * 0.1 * c.s, span ) - 120, c.y, c.s );
			} );

			hills( ctx, w, FLOOR, s * 0.15, 110, 34, 300, '#b6e2a2' );
			hills( ctx, w, FLOOR, s * 0.35, 54, 20, 190, '#86cc72' );
		},
		column: function ( ctx, x, y, cw, ch, capBottom ) {
			ctx.fillStyle = '#d8774b';
			ctx.fillRect( x, y, cw, ch );

			ctx.strokeStyle = 'rgba(110,44,18,0.28)';
			ctx.lineWidth = 2;
			ctx.beginPath();
			for ( var yy = Math.ceil( y / 20 ) * 20; yy < y + ch; yy += 20 ) {
				var off = Math.round( yy / 20 ) % 2 ? cw * 0.3 : cw * 0.72;
				ctx.moveTo( x, yy );
				ctx.lineTo( x + cw, yy );
				ctx.moveTo( x + off, yy );
				ctx.lineTo( x + off, Math.min( yy + 20, y + ch ) );
			}
			ctx.stroke();

			ctx.fillStyle = 'rgba(255,255,255,0.16)';
			ctx.fillRect( x + 6, y, 7, ch );
			ctx.fillStyle = 'rgba(80,30,10,0.14)';
			ctx.fillRect( x + cw - 11, y, 11, ch );

			var cy = capBottom ? y + ch - 24 : y;
			ctx.fillStyle = '#b9552e';
			rrect( ctx, x - 6, cy, cw + 12, 24, 5 );
			ctx.fill();
			ctx.fillStyle = 'rgba(255,255,255,0.18)';
			ctx.fillRect( x - 2, cy + 4, cw + 4, 3 );
		},
		ground: function ( ctx, w, gy, gh, s ) {
			ctx.fillStyle = '#e2bd73';
			ctx.fillRect( 0, gy, w, gh );
			ctx.fillStyle = '#69bd56';
			ctx.fillRect( 0, gy, w, 13 );
			ctx.fillStyle = '#58a947';
			for ( var x = -mod( s, 22 ); x < w; x += 22 ) {
				ctx.beginPath();
				ctx.moveTo( x, gy + 13 );
				ctx.lineTo( x + 5, gy + 20 );
				ctx.lineTo( x + 11, gy + 13 );
				ctx.fill();
			}
			ctx.fillStyle = 'rgba(140,92,40,0.28)';
			for ( x = -mod( s, 64 ); x < w + 64; x += 64 ) {
				circle( ctx, x + 18, gy + 38, 3 );
				ctx.fill();
				circle( ctx, x + 44, gy + 52, 2 );
				ctx.fill();
			}
		},
		player: function ( ctx, t ) {
			var buzz = Math.sin( t * 46 ) * 0.45;

			ctx.fillStyle = 'rgba(255,255,255,0.7)';
			ctx.strokeStyle = 'rgba(70,120,180,0.45)';
			ctx.lineWidth = 1.5;
			ellipse( ctx, -4, -16, 7, 12, -0.5 + buzz );
			ctx.fill();
			ctx.stroke();

			ctx.fillStyle = '#ffc83d';
			ellipse( ctx, 0, 0, 19, 15 );
			ctx.fill();
			ctx.save();
			ctx.clip();
			ctx.fillStyle = '#2a2018';
			ctx.fillRect( -9, -16, 5, 32 );
			ctx.fillRect( 1, -16, 5, 32 );
			ctx.fillStyle = 'rgba(255,255,255,0.25)';
			ellipse( ctx, 2, -9, 12, 4 );
			ctx.fill();
			ctx.restore();

			ctx.fillStyle = '#2a2018';
			ctx.beginPath();
			ctx.moveTo( -18, -3 );
			ctx.lineTo( -25, 0 );
			ctx.lineTo( -18, 3 );
			ctx.fill();

			ctx.fillStyle = '#fff';
			circle( ctx, 11, -4, 5 );
			ctx.fill();
			ctx.fillStyle = '#2a2018';
			circle( ctx, 12.6, -4, 2.6 );
			ctx.fill();
			ctx.fillStyle = 'rgba(240,96,70,0.45)';
			circle( ctx, 12, 5, 2.8 );
			ctx.fill();

			ctx.strokeStyle = '#2a2018';
			ctx.lineWidth = 1.6;
			ctx.beginPath();
			ctx.moveTo( 10, -13 );
			ctx.quadraticCurveTo( 13, -21, 18, -21 );
			ctx.stroke();

			ctx.fillStyle = 'rgba(255,255,255,0.8)';
			ctx.strokeStyle = 'rgba(70,120,180,0.5)';
			ctx.lineWidth = 1.5;
			ellipse( ctx, 3, -17, 6, 11, 0.4 - buzz );
			ctx.fill();
			ctx.stroke();
		}
	};

	THEMES.night = {
		base: '#151a4a',
		accent: '#ffd36b',
		init: function ( r ) {
			var x = 0;
			return {
				stars: scatter( r, 110, function ( r ) {
					return { x: r() * 1600, y: r() * 360, r: 0.5 + r() * 1.3, p: r() * TAU };
				} ),
				skyline: scatter( r, 34, function ( r ) {
					var b = { x: x, w: 34 + r() * 46, h: 70 + r() * 170 };
					x += b.w + 4;
					return b;
				} )
			};
		},
		bg: function ( ctx, w, h, t, s, d ) {
			ctx.fillStyle = vgrad( ctx, 0, FLOOR, [ '#060a24', '#151a4a', '#3a3378' ] );
			ctx.fillRect( 0, 0, w, h );

			d.stars.forEach( function ( st ) {
				ctx.globalAlpha = 0.45 + 0.55 * Math.sin( t * 1.6 + st.p ) * Math.sin( t * 1.6 + st.p );
				ctx.fillStyle = '#fff';
				circle( ctx, mod( st.x - s * 0.02, w + 20 ) - 10, st.y, st.r );
				ctx.fill();
			} );
			ctx.globalAlpha = 1;

			var mx = w * 0.8;
			glow( ctx, mx, 96, 120, 'rgba(255,244,214,0.35)' );
			ctx.fillStyle = '#f6eed6';
			circle( ctx, mx, 96, 32 );
			ctx.fill();
			ctx.fillStyle = 'rgba(180,170,140,0.35)';
			circle( ctx, mx - 10, 88, 7 );
			ctx.fill();
			circle( ctx, mx + 9, 104, 5 );
			ctx.fill();
			circle( ctx, mx + 12, 84, 3 );
			ctx.fill();

			var last = d.skyline[ d.skyline.length - 1 ];
			var total = last.x + last.w + 4;
			var off = -mod( s * 0.2, total );
			ctx.fillStyle = '#1d2259';
			for ( var rep = off; rep < w; rep += total ) {
				d.skyline.forEach( function ( b ) {
					var bx = rep + b.x;
					if ( bx > w || bx + b.w < 0 ) {
						return;
					}
					ctx.fillRect( bx, FLOOR - b.h, b.w, b.h );
				} );
			}
		},
		column: function ( ctx, x, y, cw, ch, capBottom, t, id ) {
			ctx.fillStyle = '#262d68';
			ctx.fillRect( x, y, cw, ch );
			ctx.fillStyle = 'rgba(0,0,0,0.18)';
			ctx.fillRect( x + cw - 12, y, 12, ch );

			for ( var yy = Math.ceil( ( y + 6 ) / 22 ) * 22; yy + 12 < y + ch - 6; yy += 22 ) {
				for ( var c = 0; c < 3; c++ ) {
					var lit = hash( id, c, Math.round( yy / 22 ) ) % 10 < 4;
					ctx.fillStyle = lit ? '#ffd36b' : 'rgba(255,255,255,0.07)';
					ctx.fillRect( x + 11 + c * 18, yy, 10, 12 );
				}
			}

			var cy = capBottom ? y + ch - 14 : y;
			ctx.fillStyle = '#3a4390';
			ctx.fillRect( x - 5, cy, cw + 10, 14 );
			ctx.fillStyle = 'rgba(255,255,255,0.12)';
			ctx.fillRect( x - 5, cy, cw + 10, 2 );
		},
		ground: function ( ctx, w, gy, gh, s ) {
			ctx.fillStyle = '#10143a';
			ctx.fillRect( 0, gy, w, gh );
			ctx.fillStyle = '#2b3175';
			ctx.fillRect( 0, gy, w, 6 );
			ctx.fillStyle = 'rgba(255,211,107,0.75)';
			for ( var x = -mod( s, 50 ); x < w; x += 50 ) {
				ctx.fillRect( x, gy + 33, 26, 3 );
			}
		},
		player: function ( ctx, t, ft ) {
			var wing = ft < 0.16 ? -1 : 0.25;

			ctx.fillStyle = '#7d63c9';
			ctx.beginPath();
			ctx.moveTo( -6, -12 );
			ctx.lineTo( -10, -22 );
			ctx.lineTo( 0, -14 );
			ctx.moveTo( 9, -13 );
			ctx.lineTo( 12, -23 );
			ctx.lineTo( 16, -11 );
			ctx.fill();

			ctx.fillStyle = '#8e74dc';
			ellipse( ctx, 0, 0, 18, 16 );
			ctx.fill();
			ctx.fillStyle = '#cbbdf6';
			ellipse( ctx, 2, 6, 10, 8 );
			ctx.fill();

			ctx.fillStyle = '#fff';
			circle( ctx, 3, -4, 6 );
			ctx.fill();
			circle( ctx, 14, -3, 5.5 );
			ctx.fill();
			ctx.fillStyle = '#f5a524';
			circle( ctx, 4.5, -4, 3.6 );
			ctx.fill();
			circle( ctx, 15, -3, 3.3 );
			ctx.fill();
			ctx.fillStyle = '#1a1433';
			circle( ctx, 5, -4, 1.8 );
			ctx.fill();
			circle( ctx, 15.5, -3, 1.6 );
			ctx.fill();

			ctx.fillStyle = '#f5a524';
			ctx.beginPath();
			ctx.moveTo( 8, 1 );
			ctx.lineTo( 12, 1.5 );
			ctx.lineTo( 9.5, 5 );
			ctx.fill();

			ctx.fillStyle = '#6a51b8';
			ellipse( ctx, -6, 3, 10, 6, wing );
			ctx.fill();
		}
	};

	THEMES.neon = {
		base: '#1c0b3d',
		accent: '#2ef2ff',
		init: function ( r ) {
			var x = 0;
			return {
				stars: scatter( r, 60, function ( r ) {
					return { x: r() * 1600, y: r() * 260, r: 0.4 + r(), p: r() * TAU };
				} ),
				peaks: scatter( r, 18, function ( r ) {
					var p = { x: x, h: 40 + r() * 90 };
					x += 60 + r() * 70;
					return p;
				} ),
				peaksW: x
			};
		},
		bg: function ( ctx, w, h, t, s, d ) {
			var sky = vgrad( ctx, 0, FLOOR, [ '#0b021f', '#22104a', '#5b1670', '#d42f7a' ] );
			ctx.fillStyle = sky;
			ctx.fillRect( 0, 0, w, h );

			ctx.fillStyle = '#fff';
			d.stars.forEach( function ( st ) {
				ctx.globalAlpha = 0.3 + 0.4 * Math.sin( t * 2 + st.p ) * Math.sin( t * 2 + st.p );
				circle( ctx, mod( st.x - s * 0.03, w ), st.y, st.r );
				ctx.fill();
			} );
			ctx.globalAlpha = 1;

			var cx = w * 0.5;
			var cy = FLOOR - 36;
			var R = 118;
			glow( ctx, cx, cy, 240, 'rgba(255,60,140,0.35)' );
			ctx.fillStyle = vgrad( ctx, cy - R, cy + R * 0.4, [ '#ffe45c', '#ff9a3c', '#ff2d87' ] );
			circle( ctx, cx, cy, R );
			ctx.fill();
			ctx.fillStyle = sky;
			for ( var i = 0; i < 7; i++ ) {
				var band = cy - 10 + i * 15;
				ctx.fillRect( cx - R, band, R * 2, 2 + i * 1.3 );
			}

			ctx.fillStyle = '#16052e';
			ctx.strokeStyle = 'rgba(255,61,201,0.85)';
			ctx.lineWidth = 1.5;
			var off = -mod( s * 0.08, d.peaksW );
			for ( var rep = off - d.peaksW; rep < w; rep += d.peaksW ) {
				ctx.beginPath();
				ctx.moveTo( rep, FLOOR );
				d.peaks.forEach( function ( p, idx ) {
					var next = d.peaks[ idx + 1 ];
					var nx = next ? next.x : d.peaksW;
					ctx.lineTo( rep + p.x + ( nx - p.x ) / 2, FLOOR - p.h );
					ctx.lineTo( rep + nx, FLOOR );
				} );
				ctx.fill();
				ctx.stroke();
			}
		},
		column: function ( ctx, x, y, cw, ch, capBottom ) {
			ctx.save();
			ctx.fillStyle = 'rgba(18,4,40,0.92)';
			ctx.fillRect( x, y, cw, ch );

			ctx.strokeStyle = 'rgba(46,242,255,0.18)';
			ctx.lineWidth = 1;
			ctx.beginPath();
			for ( var yy = Math.ceil( y / 28 ) * 28; yy < y + ch; yy += 28 ) {
				ctx.moveTo( x + 6, yy );
				ctx.lineTo( x + cw - 6, yy );
			}
			ctx.stroke();

			ctx.shadowColor = '#2ef2ff';
			ctx.shadowBlur = 14;
			ctx.strokeStyle = '#2ef2ff';
			ctx.lineWidth = 3;
			ctx.strokeRect( x + 1.5, y, cw - 3, ch );

			var cy = capBottom ? y + ch - 18 : y;
			ctx.shadowColor = '#ff3dc9';
			ctx.strokeStyle = '#ff3dc9';
			ctx.fillStyle = '#2a0a4a';
			ctx.fillRect( x - 5, cy, cw + 10, 18 );
			ctx.strokeRect( x - 5, cy, cw + 10, 18 );
			ctx.restore();
		},
		ground: function ( ctx, w, gy, gh, s ) {
			ctx.fillStyle = '#12002a';
			ctx.fillRect( 0, gy, w, gh );

			ctx.save();
			ctx.beginPath();
			ctx.rect( 0, gy, w, gh );
			ctx.clip();
			ctx.strokeStyle = 'rgba(255,61,201,0.7)';
			ctx.lineWidth = 1.2;
			ctx.beginPath();
			var phase = mod( s / 30, 1 );
			for ( var i = 0; i < 8; i++ ) {
				var k = ( i + phase ) / 8;
				var yy = gy + gh * k * k;
				ctx.moveTo( 0, yy );
				ctx.lineTo( w, yy );
			}
			var vx = w / 2;
			var shift = mod( s, 60 );
			for ( var j = -24; j <= 24; j++ ) {
				var X = j * 60 - shift;
				ctx.moveTo( vx + X * 0.35, gy );
				ctx.lineTo( vx + X * 1.8, gy + gh );
			}
			ctx.stroke();
			ctx.restore();

			ctx.save();
			ctx.shadowColor = '#ff3dc9';
			ctx.shadowBlur = 12;
			ctx.fillStyle = '#ff3dc9';
			ctx.fillRect( 0, gy, w, 2 );
			ctx.restore();
		},
		player: function ( ctx, t, ft ) {
			var boost = ft < 0.22 ? 1 - ft / 0.22 : 0;
			var len = 6 + boost * 16 + Math.sin( t * 60 ) * 2;

			ctx.save();
			ctx.shadowColor = '#ff3dc9';
			ctx.shadowBlur = 12;
			ctx.fillStyle = '#ff3dc9';
			ctx.beginPath();
			ctx.moveTo( -10, -5 );
			ctx.lineTo( -10 - len, 0 );
			ctx.lineTo( -10, 5 );
			ctx.fill();

			ctx.shadowColor = '#2ef2ff';
			ctx.shadowBlur = 14;
			ctx.fillStyle = 'rgba(46,242,255,0.16)';
			ctx.strokeStyle = '#2ef2ff';
			ctx.lineWidth = 2.6;
			ctx.lineJoin = 'round';
			ctx.beginPath();
			ctx.moveTo( 21, 0 );
			ctx.lineTo( -14, -13 );
			ctx.lineTo( -8, 0 );
			ctx.lineTo( -14, 13 );
			ctx.closePath();
			ctx.fill();
			ctx.stroke();

			ctx.fillStyle = '#ffffff';
			ctx.shadowBlur = 6;
			ellipse( ctx, 4, 0, 4, 2.4 );
			ctx.fill();
			ctx.restore();
		}
	};

	THEMES.ocean = {
		base: '#05507f',
		accent: '#ffd166',
		init: function ( r ) {
			return {
				bubbles: scatter( r, 34, function ( r ) {
					return { x: r() * 1400, y: r() * 640, r: 1.5 + r() * 4.5, sp: 18 + r() * 34, p: r() * TAU };
				} ),
				rays: scatter( r, 5, function ( r, i ) {
					return { x: ( i + r() * 0.6 ) / 5, w: 36 + r() * 50, p: r() * TAU };
				} )
			};
		},
		bg: function ( ctx, w, h, t, s, d ) {
			ctx.fillStyle = vgrad( ctx, 0, FLOOR, [ '#0f9bd1', '#0a6aa6', '#05436f', '#032c4c' ] );
			ctx.fillRect( 0, 0, w, h );

			d.rays.forEach( function ( ray ) {
				var x = ray.x * w + Math.sin( t * 0.3 + ray.p ) * 30;
				ctx.fillStyle = 'rgba(255,255,255,' + ( 0.05 + 0.03 * Math.sin( t * 0.7 + ray.p ) ) + ')';
				ctx.beginPath();
				ctx.moveTo( x, 0 );
				ctx.lineTo( x + ray.w, 0 );
				ctx.lineTo( x + ray.w * 2.2 - 120, FLOOR );
				ctx.lineTo( x - 120, FLOOR );
				ctx.fill();
			} );

			hills( ctx, w, FLOOR, s * 0.12, 120, 40, 340, '#06466f' );
			hills( ctx, w, FLOOR, s * 0.3, 60, 22, 210, '#043657' );

			ctx.strokeStyle = 'rgba(255,255,255,0.45)';
			ctx.lineWidth = 1.2;
			d.bubbles.forEach( function ( b ) {
				var y = mod( b.y - t * b.sp, 660 ) - 30;
				var x = mod( b.x - s * 0.3, w + 40 ) - 20 + Math.sin( t * 2 + b.p ) * 4;
				circle( ctx, x, y, b.r );
				ctx.stroke();
			} );
		},
		column: function ( ctx, x, y, cw, ch, capBottom, t, id ) {
			ctx.fillStyle = '#f2705f';
			rrect( ctx, x, y, cw, ch, 12 );
			ctx.fill();

			ctx.fillStyle = '#d9574a';
			for ( var yy = Math.ceil( y / 26 ) * 26; yy < y + ch - 8; yy += 26 ) {
				var hx = hash( id, Math.round( yy / 26 ), 7 );
				circle( ctx, x + 14 + ( hx % 44 ), yy + 10, 3 + ( hx % 3 ) );
				ctx.fill();
			}
			ctx.fillStyle = 'rgba(255,255,255,0.14)';
			ctx.fillRect( x + 8, y + 6, 6, ch - 12 );

			var cy = capBottom ? y + ch - 26 : y;
			ctx.fillStyle = '#ff917f';
			rrect( ctx, x - 7, cy, cw + 14, 26, 13 );
			ctx.fill();
			ctx.fillStyle = '#ffc2b5';
			for ( var px = x; px <= x + cw; px += 14 ) {
				circle( ctx, px, capBottom ? cy + 26 : cy, 3 );
				ctx.fill();
			}
		},
		ground: function ( ctx, w, gy, gh, s, t ) {
			for ( var x = -mod( s, 90 ); x < w + 90; x += 90 ) {
				var sway = Math.sin( t * 1.8 + ( x + s ) * 0.02 ) * 7;
				ctx.strokeStyle = '#2f9e6c';
				ctx.lineWidth = 4;
				ctx.lineCap = 'round';
				ctx.beginPath();
				ctx.moveTo( x + 20, gy + 6 );
				ctx.quadraticCurveTo( x + 14, gy - 12, x + 20 + sway, gy - 30 );
				ctx.moveTo( x + 30, gy + 6 );
				ctx.quadraticCurveTo( x + 34, gy - 8, x + 28 + sway * 0.8, gy - 20 );
				ctx.stroke();
			}
			ctx.lineCap = 'butt';

			ctx.fillStyle = '#e8d29a';
			ctx.beginPath();
			ctx.moveTo( 0, gy + gh );
			for ( x = 0; x <= w + 8; x += 8 ) {
				ctx.lineTo( x, gy + 4 + Math.sin( ( x + s ) / 34 ) * 3 );
			}
			ctx.lineTo( w + 8, gy + gh );
			ctx.fill();

			ctx.strokeStyle = 'rgba(160,120,60,0.3)';
			ctx.lineWidth = 2;
			ctx.beginPath();
			for ( x = -mod( s, 70 ); x < w + 70; x += 70 ) {
				ctx.moveTo( x + 10, gy + 32 );
				ctx.quadraticCurveTo( x + 25, gy + 26, x + 40, gy + 32 );
			}
			ctx.stroke();
		},
		player: function ( ctx, t, ft ) {
			var wag = Math.sin( t * ( ft < 0.3 ? 22 : 10 ) ) * 0.35;

			ctx.save();
			ctx.translate( -13, 0 );
			ctx.rotate( wag );
			ctx.fillStyle = '#f08a12';
			ctx.beginPath();
			ctx.moveTo( 2, 0 );
			ctx.lineTo( -13, -11 );
			ctx.quadraticCurveTo( -9, 0, -13, 11 );
			ctx.closePath();
			ctx.fill();
			ctx.restore();

			ctx.fillStyle = '#f08a12';
			ctx.beginPath();
			ctx.moveTo( -4, -12 );
			ctx.quadraticCurveTo( 2, -21, 8, -12 );
			ctx.fill();

			ctx.fillStyle = '#ffb21e';
			ellipse( ctx, 0, 0, 19, 13.5 );
			ctx.fill();
			ctx.save();
			ctx.clip();
			ctx.fillStyle = '#fff';
			ctx.fillRect( -3, -15, 6, 30 );
			ctx.strokeStyle = '#2a2018';
			ctx.lineWidth = 1.2;
			ctx.strokeRect( -3, -15, 6, 30 );
			ctx.fillStyle = 'rgba(255,255,255,0.3)';
			ellipse( ctx, 6, -8, 9, 3 );
			ctx.fill();
			ctx.restore();

			ctx.fillStyle = '#fff';
			circle( ctx, 10, -3, 4.6 );
			ctx.fill();
			ctx.fillStyle = '#16324f';
			circle( ctx, 11.3, -3, 2.4 );
			ctx.fill();

			ctx.strokeStyle = '#9a4b00';
			ctx.lineWidth = 1.3;
			ctx.beginPath();
			ctx.arc( 15, 4, 2.5, 0.2, PI - 0.6 );
			ctx.stroke();
		}
	};

	THEMES.space = {
		base: '#0c0628',
		accent: '#ff5a77',
		init: function ( r ) {
			var depth = [ 0.03, 0.09, 0.22 ];
			return {
				stars: scatter( r, 150, function ( r, i ) {
					return { x: r() * 1800, y: r() * FLOOR, z: depth[ i % 3 ], r: 0.4 + ( i % 3 ) * 0.5 + r() * 0.4, p: r() * TAU };
				} ),
				craters: scatter( r, 6, function ( r ) {
					return { x: r() * 420, w: 10 + r() * 18, y: 20 + r() * 28 };
				} )
			};
		},
		bg: function ( ctx, w, h, t, s, d ) {
			ctx.fillStyle = vgrad( ctx, 0, FLOOR, [ '#03010c', '#0c0628', '#1d0d46' ] );
			ctx.fillRect( 0, 0, w, h );
			glow( ctx, w * 0.28, 220, 280, 'rgba(130,60,210,0.22)' );
			glow( ctx, w * 0.72, 430, 240, 'rgba(40,120,230,0.16)' );

			ctx.fillStyle = '#fff';
			d.stars.forEach( function ( st ) {
				ctx.globalAlpha = 0.5 + 0.5 * Math.sin( t * 1.3 + st.p ) * Math.sin( t * 1.3 + st.p );
				circle( ctx, mod( st.x - s * st.z, w + 10 ) - 5, st.y, st.r );
				ctx.fill();
			} );
			ctx.globalAlpha = 1;

			var px = w * 0.78;
			var py = 150;
			ctx.save();
			ctx.translate( px, py );
			ctx.rotate( -0.32 );
			ctx.strokeStyle = 'rgba(255,214,170,0.55)';
			ctx.lineWidth = 5;
			ctx.beginPath();
			ctx.ellipse( 0, 0, 82, 17, 0, PI, TAU );
			ctx.stroke();
			var pg = ctx.createRadialGradient( -16, -18, 4, 0, 0, 46 );
			pg.addColorStop( 0, '#ffc78a' );
			pg.addColorStop( 1, '#c9466a' );
			ctx.fillStyle = pg;
			circle( ctx, 0, 0, 44 );
			ctx.fill();
			ctx.beginPath();
			ctx.ellipse( 0, 0, 82, 17, 0, 0, PI );
			ctx.stroke();
			ctx.restore();
		},
		column: function ( ctx, x, y, cw, ch, capBottom, t, id ) {
			ctx.fillStyle = hgrad( ctx, x, x + cw, [ '#41465d', '#9298b2', '#5a5f78', '#3a3f55' ] );
			ctx.fillRect( x, y, cw, ch );

			ctx.strokeStyle = 'rgba(0,0,0,0.3)';
			ctx.lineWidth = 1.5;
			ctx.beginPath();
			for ( var yy = Math.ceil( y / 44 ) * 44; yy < y + ch; yy += 44 ) {
				ctx.moveTo( x, yy );
				ctx.lineTo( x + cw, yy );
			}
			ctx.stroke();
			ctx.fillStyle = 'rgba(255,255,255,0.35)';
			for ( yy = Math.ceil( y / 44 ) * 44 + 8; yy < y + ch; yy += 44 ) {
				circle( ctx, x + 8, yy, 1.6 );
				ctx.fill();
				circle( ctx, x + cw - 8, yy, 1.6 );
				ctx.fill();
			}

			var cy = capBottom ? y + ch - 20 : y;
			ctx.fillStyle = '#2b2f43';
			rrect( ctx, x - 6, cy, cw + 12, 20, 4 );
			ctx.fill();
			var blink = 0.35 + 0.65 * Math.max( 0, Math.sin( t * 4 + id ) );
			ctx.fillStyle = 'rgba(255,90,119,' + blink + ')';
			circle( ctx, x + cw / 2, cy + 10, 3.5 );
			ctx.fill();
			glow( ctx, x + cw / 2, cy + 10, 16, 'rgba(255,90,119,' + blink * 0.5 + ')' );
		},
		ground: function ( ctx, w, gy, gh, s, t, d ) {
			ctx.fillStyle = '#767b93';
			ctx.fillRect( 0, gy, w, gh );
			ctx.fillStyle = '#9ca2bb';
			ctx.fillRect( 0, gy, w, 6 );
			ctx.fillStyle = 'rgba(40,44,64,0.35)';
			for ( var rep = -mod( s, 420 ); rep < w; rep += 420 ) {
				d.craters.forEach( function ( c ) {
					ellipse( ctx, rep + c.x, gy + c.y, c.w, c.w * 0.32 );
					ctx.fill();
				} );
			}
		},
		player: function ( ctx, t, ft ) {
			var boost = ft < 0.22 ? 1 - ft / 0.22 : 0;
			var len = 8 + boost * 16 + Math.sin( t * 55 ) * 2.5;

			ctx.fillStyle = '#ffb703';
			ctx.beginPath();
			ctx.moveTo( -15, -6 );
			ctx.quadraticCurveTo( -15 - len, 0, -15, 6 );
			ctx.fill();
			ctx.fillStyle = '#fff1c2';
			ctx.beginPath();
			ctx.moveTo( -15, -3 );
			ctx.quadraticCurveTo( -15 - len * 0.55, 0, -15, 3 );
			ctx.fill();

			ctx.fillStyle = '#ff5a77';
			ctx.beginPath();
			ctx.moveTo( -8, -8 );
			ctx.lineTo( -17, -17 );
			ctx.lineTo( -16, -6 );
			ctx.moveTo( -8, 8 );
			ctx.lineTo( -17, 17 );
			ctx.lineTo( -16, 6 );
			ctx.fill();

			ctx.fillStyle = '#eef0f7';
			ellipse( ctx, 0, 0, 18, 9.5 );
			ctx.fill();
			ctx.fillStyle = '#ff5a77';
			ctx.beginPath();
			ctx.moveTo( 9, -8 );
			ctx.quadraticCurveTo( 26, 0, 9, 8 );
			ctx.quadraticCurveTo( 13, 0, 9, -8 );
			ctx.fill();
			ctx.fillStyle = 'rgba(0,0,0,0.08)';
			ctx.fillRect( -16, 3, 26, 4 );

			ctx.fillStyle = '#56c7f0';
			circle( ctx, 1, -1, 4.5 );
			ctx.fill();
			ctx.strokeStyle = '#8d95b2';
			ctx.lineWidth = 2;
			ctx.stroke();
		}
	};

	THEMES.candy = {
		base: '#ffe3ef',
		accent: '#f0457a',
		init: function ( r ) {
			var palette = [ '#ff8fc1', '#8fd3ff', '#ffd36b', '#b39cff', '#7ee0b4' ];
			return {
				clouds: scatter( r, 7, function ( r ) {
					return { x: r() * 1400, y: 50 + r() * 180, s: 0.6 + r() * 0.7, c: r() > 0.5 ? '#ffffff' : '#ffd6ec' };
				} ),
				pops: scatter( r, 9, function ( r, i ) {
					return { x: i * 150 + r() * 60, h: 90 + r() * 120, r: 18 + r() * 14, c: palette[ i % palette.length ] };
				} ),
				sprinkles: scatter( r, 40, function ( r, i ) {
					return { x: r() * 1400, y: r() * 600, a: r() * PI, c: palette[ i % palette.length ] };
				} ),
				palette: palette
			};
		},
		bg: function ( ctx, w, h, t, s, d ) {
			ctx.fillStyle = vgrad( ctx, 0, FLOOR, [ '#ffbfdc', '#ffe0ee', '#fff3dd' ] );
			ctx.fillRect( 0, 0, w, h );

			var span = w + 240;
			d.clouds.forEach( function ( c ) {
				ctx.fillStyle = c.c;
				cloud( ctx, mod( c.x - s * 0.1 * c.s, span ) - 120, c.y, c.s );
			} );

			var popSpan = 1350;
			d.pops.forEach( function ( p ) {
				var x = mod( p.x - s * 0.25, Math.max( popSpan, w + 100 ) ) - 50;
				var top = FLOOR - p.h;
				ctx.fillStyle = 'rgba(255,255,255,0.8)';
				ctx.fillRect( x - 2, top, 4, p.h );
				ctx.globalAlpha = 0.55;
				ctx.fillStyle = p.c;
				circle( ctx, x, top, p.r );
				ctx.fill();
				ctx.strokeStyle = 'rgba(255,255,255,0.9)';
				ctx.lineWidth = 3;
				ctx.beginPath();
				ctx.arc( x, top, p.r * 0.55, 0, PI * 1.4 );
				ctx.stroke();
				ctx.globalAlpha = 1;
			} );

			d.sprinkles.forEach( function ( sp ) {
				var x = mod( sp.x - s * 0.18, w + 20 ) - 10;
				var y = mod( sp.y + t * 26, FLOOR );
				ctx.save();
				ctx.translate( x, y );
				ctx.rotate( sp.a + t * 0.6 );
				ctx.fillStyle = sp.c;
				rrect( ctx, -4, -1.2, 8, 2.4, 1.2 );
				ctx.fill();
				ctx.restore();
			} );
		},
		column: function ( ctx, x, y, cw, ch, capBottom ) {
			ctx.save();
			rrect( ctx, x, y, cw, ch, 6 );
			ctx.fillStyle = '#fffafc';
			ctx.fill();
			ctx.clip();
			ctx.fillStyle = '#f0457a';
			var rise = cw * 0.6;
			for ( var yy = Math.floor( y / 30 ) * 30; yy < y + ch + rise; yy += 30 ) {
				ctx.beginPath();
				ctx.moveTo( x, yy );
				ctx.lineTo( x + cw, yy - rise );
				ctx.lineTo( x + cw, yy - rise + 13 );
				ctx.lineTo( x, yy + 13 );
				ctx.fill();
			}
			ctx.fillStyle = 'rgba(255,255,255,0.4)';
			ctx.fillRect( x + 9, y, 6, ch );
			ctx.fillStyle = 'rgba(120,20,60,0.1)';
			ctx.fillRect( x + cw - 10, y, 10, ch );
			ctx.restore();

			var cy = capBottom ? y + ch - 24 : y;
			ctx.fillStyle = '#ff8fc1';
			rrect( ctx, x - 6, cy, cw + 12, 24, 12 );
			ctx.fill();
			ctx.fillStyle = 'rgba(255,255,255,0.55)';
			rrect( ctx, x + 2, cy + 5, cw * 0.45, 5, 2.5 );
			ctx.fill();
		},
		ground: function ( ctx, w, gy, gh, s, t, d ) {
			ctx.fillStyle = '#6e3f2e';
			ctx.fillRect( 0, gy, w, gh );

			ctx.fillStyle = '#ff8fc1';
			ctx.fillRect( 0, gy, w, 9 );
			for ( var x = -mod( s, 34 ); x < w + 34; x += 34 ) {
				var dr = 5 + ( hash( Math.round( ( x + s ) / 34 ), 3, 9 ) % 5 );
				circle( ctx, x + 17, gy + 9, dr * 0.75 );
				ctx.fill();
				ctx.fillRect( x + 17 - dr * 0.75, gy + 6, dr * 1.5, dr * 0.6 );
			}

			for ( x = -mod( s, 46 ); x < w + 46; x += 46 ) {
				var k = Math.round( ( x + s ) / 46 );
				ctx.fillStyle = d.palette[ k % d.palette.length ];
				ctx.save();
				ctx.translate( x + 20, gy + 34 + ( k % 3 ) * 7 );
				ctx.rotate( ( k % 5 ) * 0.7 );
				rrect( ctx, -4, -1.2, 8, 2.4, 1.2 );
				ctx.fill();
				ctx.restore();
			}
		},
		player: function ( ctx, t, ft ) {
			var squish = ft < 0.18 ? Math.sin( ( ft / 0.18 ) * PI ) * 0.16 : 0;
			ctx.scale( 1 - squish, 1 + squish );

			var g = ctx.createRadialGradient( -6, -7, 2, 0, 0, 19 );
			g.addColorStop( 0, '#b9f1ff' );
			g.addColorStop( 1, '#38b1dc' );
			ctx.fillStyle = g;
			circle( ctx, 0, 0, 17 );
			ctx.fill();

			ctx.fillStyle = 'rgba(255,255,255,0.75)';
			ellipse( ctx, -6, -8, 5, 3, -0.5 );
			ctx.fill();

			ctx.fillStyle = '#123247';
			circle( ctx, 5, -3, 2.2 );
			ctx.fill();
			circle( ctx, 12, -2, 2 );
			ctx.fill();
			ctx.strokeStyle = '#123247';
			ctx.lineWidth = 1.5;
			ctx.lineCap = 'round';
			ctx.beginPath();
			ctx.arc( 8.5, 2, 3.2, 0.25, PI - 0.25 );
			ctx.stroke();
			ctx.lineCap = 'butt';
			ctx.fillStyle = 'rgba(255,120,170,0.5)';
			circle( ctx, 2, 4, 2.4 );
			ctx.fill();
			circle( ctx, 15, 4, 2.1 );
			ctx.fill();
		}
	};

	var THEME_KEYS = Object.keys( THEMES );

	function decorFor( key ) {
		var theme = THEMES[ key ];
		return theme.init ? theme.init( seeded( 404 ) ) : {};
	}

	/* Scene rendering (shared by the game and admin previews) ------------- */

	function drawScene( ctx, theme, decor, w, scene ) {
		theme.bg( ctx, w, WORLD_H, scene.t, scene.scroll, decor );

		scene.gates.forEach( function ( g ) {
			if ( g.x > w || g.x + COL_W < -12 ) {
				return;
			}
			var top = g.gy - g.gap / 2;
			var bottom = g.gy + g.gap / 2;

			// Gates spawned on-screen slide in from the edges instead of popping in.
			var reveal = g.born === undefined ? 1 : clamp( ( scene.t - g.born ) / 0.45, 0, 1 );
			var ease = 1 - Math.pow( 1 - reveal, 3 );
			var lift = ( 1 - ease ) * ( top + 40 );
			var drop = ( 1 - ease ) * ( FLOOR - bottom );

			theme.column( ctx, g.x, -40 - lift, COL_W, top + 40, true, scene.t, g.id );
			ctx.save();
			ctx.beginPath();
			ctx.rect( g.x - 20, 0, COL_W + 40, FLOOR );
			ctx.clip();
			theme.column( ctx, g.x, bottom + drop, COL_W, FLOOR - bottom, false, scene.t, g.id );
			ctx.restore();

			if ( g.digit ) {
				ctx.save();
				ctx.globalAlpha = 0.42 * ease;
				ctx.fillStyle = theme.accent;
				ctx.font = '800 88px ui-rounded, "SF Pro Rounded", system-ui, -apple-system, "Segoe UI", sans-serif';
				ctx.textAlign = 'center';
				ctx.textBaseline = 'middle';
				ctx.fillText( g.digit, g.x + COL_W / 2, g.gy + 4 );
				ctx.restore();
			}
		} );

		theme.ground( ctx, w, FLOOR, GROUND, scene.scroll, scene.t, decor );

		var p = scene.player;
		ctx.save();
		ctx.translate( p.x, p.y );
		ctx.rotate( p.rot );
		theme.player( ctx, scene.t, p.ft );
		ctx.restore();
	}

	function fitCanvas( canvas ) {
		var dpr = Math.min( window.devicePixelRatio || 1, 2 );
		var cw = canvas.clientWidth || 300;
		var ch = canvas.clientHeight || 150;
		canvas.width = Math.round( cw * dpr );
		canvas.height = Math.round( ch * dpr );
		var scale = canvas.height / WORLD_H;
		return { scale: scale, w: canvas.width / scale };
	}

	/* Storage and audio --------------------------------------------------- */

	var store = {
		get: function ( key ) {
			try {
				return window.localStorage.getItem( key );
			} catch ( e ) {
				return null;
			}
		},
		set: function ( key, value ) {
			try {
				window.localStorage.setItem( key, String( value ) );
			} catch ( e ) {}
		}
	};

	function createAudio( enabled ) {
		var ac = null;

		function unlock() {
			if ( ! enabled ) {
				return;
			}
			if ( ! ac ) {
				var Ctor = window.AudioContext || window.webkitAudioContext;
				if ( ! Ctor ) {
					return;
				}
				try {
					ac = new Ctor();
				} catch ( e ) {
					return;
				}
			}
			if ( ac.state === 'suspended' ) {
				ac.resume();
			}
		}

		function tone( f0, f1, dur, type, vol, delay ) {
			if ( ! enabled || ! ac ) {
				return;
			}
			var now = ac.currentTime + ( delay || 0 );
			var osc = ac.createOscillator();
			var gain = ac.createGain();
			osc.type = type;
			osc.frequency.setValueAtTime( f0, now );
			osc.frequency.exponentialRampToValueAtTime( f1, now + dur );
			gain.gain.setValueAtTime( 0.0001, now );
			gain.gain.exponentialRampToValueAtTime( vol, now + 0.01 );
			gain.gain.exponentialRampToValueAtTime( 0.0001, now + dur );
			osc.connect( gain );
			gain.connect( ac.destination );
			osc.start( now );
			osc.stop( now + dur + 0.02 );
		}

		return {
			unlock: unlock,
			isOn: function () {
				return enabled;
			},
			set: function ( on ) {
				enabled = !! on;
				if ( enabled ) {
					unlock();
				}
			},
			flap: function () {
				tone( 380, 720, 0.11, 'triangle', 0.1 );
			},
			point: function () {
				tone( 880, 900, 0.07, 'square', 0.04 );
				tone( 1320, 1340, 0.1, 'square', 0.04, 0.07 );
			},
			hit: function () {
				tone( 240, 50, 0.4, 'sawtooth', 0.1 );
			}
		};
	}

	function fmt( str ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		var i = 0;
		return String( str ).replace( /%(?:(\d)\$)?d/g, function ( m, pos ) {
			return String( pos ? args[ pos - 1 ] : args[ i++ ] );
		} );
	}

	/* Game ---------------------------------------------------------------- */

	function createGame( canvas, cfg, ui ) {
		var ctx = canvas.getContext( '2d' );
		var diff = DIFFICULTY[ cfg.difficulty ] || DIFFICULTY.normal;
		var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var audio = createAudio( cfg.sound );

		var theme, decor;
		var view = { scale: 1, w: 800 };
		var state = 'ready';
		var paused = false;
		var t = 0;
		var scroll = 0;
		var deadAt = 0;
		var shake = 0;
		var flash = 0;
		var gates = [];
		var nextId = 0;
		var spawned = 0;
		var score = 0;
		var best = parseInt( store.get( BEST_KEY ), 10 ) || 0;
		var player = { x: 120, y: 0, vy: 0, rot: 0, ft: 1, r: 14 };

		function restY() {
			return FLOOR * 0.46;
		}

		function setTheme( key ) {
			if ( ! THEMES[ key ] ) {
				key = THEME_KEYS[ 0 ];
			}
			theme = THEMES[ key ];
			decor = decorFor( key );
			return key;
		}

		function resize() {
			view = fitCanvas( canvas );
			player.x = clamp( view.w * 0.28, 70, 260 );
		}

		function setState( next ) {
			state = next;
			ui.state( next, { score: score, best: best } );
		}

		function reset() {
			gates = [];
			spawned = 0;
			score = 0;
			player.y = restY();
			player.vy = 0;
			player.rot = 0;
			ui.score( 0 );
		}

		function spawn( x ) {
			var min = diff.gap / 2 + 56;
			var max = FLOOR - diff.gap / 2 - 56;
			var prev = gates.length ? gates[ gates.length - 1 ].gy : ( min + max ) / 2;
			gates.push( {
				id: nextId++,
				x: x,
				born: x < view.w ? t : undefined,
				gy: clamp( prev + ( Math.random() * 2 - 1 ) * 170, min, max ),
				gap: diff.gap,
				scored: false,
				digit: spawned < DIGITS.length ? DIGITS.charAt( spawned ) : ''
			} );
			spawned++;
		}

		function hits( rx, ry, rw, rh ) {
			var r = player.r * 0.86;
			var cx = clamp( player.x, rx, rx + rw );
			var cy = clamp( player.y, ry, ry + rh );
			var dx = player.x - cx;
			var dy = player.y - cy;
			return dx * dx + dy * dy < r * r;
		}

		function boost() {
			player.vy = -diff.flap;
			player.ft = 0;
			audio.flap();
		}

		function start() {
			reset();
			setState( 'playing' );
			boost();
		}

		function die() {
			var isBest = score > best;
			if ( isBest ) {
				best = score;
				store.set( BEST_KEY, best );
			}
			deadAt = t;
			shake = reduceMotion ? 0 : 0.32;
			flash = 1;
			audio.hit();
			state = 'over';
			ui.state( 'over', { score: score, best: best, isBest: isBest && score > 0 } );
		}

		function flap() {
			audio.unlock();
			if ( paused ) {
				paused = false;
				setState( 'playing' );
				boost();
				return;
			}
			if ( state === 'ready' ) {
				start();
			} else if ( state === 'playing' ) {
				boost();
			} else if ( state === 'over' && t - deadAt > 0.55 ) {
				start();
			}
		}

		function pause() {
			if ( state === 'playing' && ! paused ) {
				paused = true;
				ui.state( 'paused', { score: score, best: best } );
			}
		}

		function togglePause() {
			if ( paused ) {
				flap();
			} else {
				pause();
			}
		}

		function update( dt ) {
			t += dt;
			player.ft += dt;
			shake = Math.max( 0, shake - dt );
			flash = Math.max( 0, flash - dt * 3 );

			if ( state === 'ready' ) {
				scroll += diff.speed * 0.5 * dt;
				player.y = restY() + Math.sin( t * 3 ) * 8;
				player.rot = Math.sin( t * 3 ) * 0.08;
				return;
			}

			if ( state === 'playing' ) {
				var step = ( diff.speed + Math.min( score * 2, 70 ) ) * dt;
				scroll += step;
				gates.forEach( function ( g ) {
					g.x -= step;
				} );
				while ( gates.length && gates[ 0 ].x < -COL_W - 20 ) {
					gates.shift();
				}
				var last = gates[ gates.length - 1 ];
				if ( ! last ) {
					// About 2.4s of flight before the first gate, whatever the screen width.
					spawn( Math.min( view.w + 60, player.x + diff.speed * 2.4 ) );
				} else if ( last.x < view.w + 60 - diff.spacing ) {
					spawn( last.x + diff.spacing );
				}
			}

			player.vy = Math.min( player.vy + diff.gravity * dt, 900 );
			player.y += player.vy * dt;
			var target = clamp( player.vy / 520, -0.45, 1.25 );
			player.rot += ( target - player.rot ) * Math.min( 1, dt * 10 );

			if ( player.y - player.r < 0 ) {
				player.y = player.r;
				player.vy = 0;
			}
			if ( player.y + player.r >= FLOOR ) {
				player.y = FLOOR - player.r;
				player.vy = 0;
				if ( state === 'playing' ) {
					die();
				}
			}

			if ( state !== 'playing' ) {
				return;
			}

			for ( var i = 0; i < gates.length; i++ ) {
				var g = gates[ i ];
				var top = g.gy - g.gap / 2;
				var bottom = g.gy + g.gap / 2;
				if ( hits( g.x, -40, COL_W, top + 40 ) || hits( g.x, bottom, COL_W, FLOOR - bottom ) ) {
					die();
					return;
				}
				if ( ! g.scored && g.x + COL_W < player.x ) {
					g.scored = true;
					score++;
					ui.score( score );
					audio.point();
				}
			}
		}

		function draw() {
			ctx.setTransform( 1, 0, 0, 1, 0, 0 );
			ctx.fillStyle = theme.base;
			ctx.fillRect( 0, 0, canvas.width, canvas.height );

			ctx.setTransform( view.scale, 0, 0, view.scale, 0, 0 );
			if ( shake > 0 ) {
				var m = shake * 16;
				ctx.translate( ( Math.random() - 0.5 ) * m, ( Math.random() - 0.5 ) * m );
			}

			drawScene( ctx, theme, decor, view.w, { t: t, scroll: scroll, gates: gates, player: player } );

			if ( flash > 0 ) {
				ctx.setTransform( 1, 0, 0, 1, 0, 0 );
				ctx.fillStyle = 'rgba(255,255,255,' + flash * 0.55 + ')';
				ctx.fillRect( 0, 0, canvas.width, canvas.height );
			}
		}

		var last = 0;
		function frame( now ) {
			window.requestAnimationFrame( frame );
			var dt = last ? Math.min( ( now - last ) / 1000, 1 / 30 ) : 0;
			last = now;
			if ( ! paused ) {
				update( dt );
			}
			draw();
		}

		setTheme( cfg.theme );
		resize();
		reset();
		window.addEventListener( 'resize', resize );
		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				pause();
			}
		} );
		window.addEventListener( 'blur', pause );
		window.requestAnimationFrame( frame );

		return {
			flap: flap,
			togglePause: togglePause,
			setTheme: setTheme,
			audio: audio,
			getBest: function () {
				return best;
			}
		};
	}

	/* DOM wiring for the 404 page ----------------------------------------- */

	function mount() {
		var cfg = window.Skyhop404Config;
		var canvas = document.getElementById( 'sh-canvas' );
		if ( ! cfg || ! canvas ) {
			return;
		}

		var strings = cfg.strings || {};
		var body = document.body;
		var scoreEl = document.querySelector( '.sh-score' );
		var titleEl = document.querySelector( '.sh-message__title' );
		var subEl = document.querySelector( '.sh-message__sub' );
		var soundBtn = document.querySelector( '.sh-sound' );
		var swatches = Array.prototype.slice.call( document.querySelectorAll( '.sh-swatch' ) );

		var theme = cfg.theme;
		if ( cfg.allowSwitch && THEMES[ store.get( THEME_KEY ) ] ) {
			theme = store.get( THEME_KEY );
		} else if ( theme === 'random' || ! THEMES[ theme ] ) {
			theme = THEME_KEYS[ Math.floor( Math.random() * THEME_KEYS.length ) ];
		}

		var savedSound = store.get( SOUND_KEY );
		var sound = savedSound === null ? !! cfg.sound : savedSound === '1';
		var game;

		function message( title, sub ) {
			titleEl.textContent = title;
			subEl.textContent = sub;
		}

		var ui = {
			score: function ( n ) {
				scoreEl.textContent = n;
			},
			state: function ( s, info ) {
				body.setAttribute( 'data-state', s );
				if ( s === 'ready' ) {
					message( strings.ready, info.best ? strings.start + '  ·  ' + fmt( strings.best, info.best ) : strings.start );
				} else if ( s === 'paused' ) {
					message( strings.paused, strings.resume );
				} else if ( s === 'over' ) {
					message( info.isBest ? strings.newBest : strings.gameOver, fmt( strings.scoreLine, info.score, info.best ) + '  ·  ' + strings.retry );
				} else {
					message( '', '' );
				}
			}
		};

		function applyTheme( key ) {
			body.className = body.className.replace( /\bsh-theme-[a-z]+\b/g, '' ).trim() + ' sh-theme-' + key;
			swatches.forEach( function ( sw ) {
				sw.setAttribute( 'aria-pressed', sw.getAttribute( 'data-theme' ) === key ? 'true' : 'false' );
			} );
		}

		function applySound() {
			soundBtn.setAttribute( 'aria-pressed', sound ? 'true' : 'false' );
			var label = sound ? strings.soundOn : strings.soundOff;
			soundBtn.setAttribute( 'title', label );
			soundBtn.querySelector( '.screen-reader-text' ).textContent = label;
		}

		cfg.theme = theme;
		cfg.sound = sound;
		game = createGame( canvas, cfg, ui );
		applyTheme( theme );
		applySound();
		ui.state( 'ready', { best: game.getBest() } );

		canvas.addEventListener( 'pointerdown', function ( e ) {
			if ( e.button > 0 ) {
				return;
			}
			e.preventDefault();
			game.flap();
		} );

		document.addEventListener( 'keydown', function ( e ) {
			var tag = ( e.target && e.target.tagName ) || '';
			if ( /^(INPUT|TEXTAREA|SELECT|BUTTON|A)$/.test( tag ) || e.metaKey || e.ctrlKey || e.altKey ) {
				return;
			}
			var key = e.key;
			if ( key === ' ' || key === 'Spacebar' || key === 'ArrowUp' || key === 'w' || key === 'W' ) {
				e.preventDefault();
				if ( ! e.repeat ) {
					game.flap();
				}
			} else if ( key === 'p' || key === 'P' || key === 'Escape' ) {
				game.togglePause();
			}
		} );

		soundBtn.addEventListener( 'click', function () {
			sound = ! sound;
			store.set( SOUND_KEY, sound ? '1' : '0' );
			game.audio.set( sound );
			applySound();
		} );

		swatches.forEach( function ( sw ) {
			sw.addEventListener( 'click', function () {
				var key = game.setTheme( sw.getAttribute( 'data-theme' ) );
				store.set( THEME_KEY, key );
				applyTheme( key );
			} );
		} );
	}

	/* Admin previews ------------------------------------------------------ */

	function preview( canvas, key ) {
		var ctx = canvas.getContext( '2d' );
		var theme = THEMES[ key ] || THEMES[ THEME_KEYS[ 0 ] ];
		var decor = decorFor( key );
		var view = fitCanvas( canvas );
		var spacing = 230;
		var heights = [ 250, 205, 300, 235 ];
		var count = Math.ceil( ( view.w + COL_W ) / spacing ) + 1;
		var period = count * spacing;
		var gates = [];
		for ( var i = 0; i < count; i++ ) {
			gates.push( { id: i, x: 0, gy: heights[ i % heights.length ], gap: 180, digit: '' } );
		}
		var player = { x: view.w * 0.26, y: 250, rot: -0.18, ft: 1 };

		function render( t ) {
			var scroll = t * 150;
			gates.forEach( function ( g, idx ) {
				g.x = mod( view.w * 0.5 + idx * spacing - scroll, period ) - COL_W;
			} );
			player.y = 250 + Math.sin( t * 3 ) * 10;
			player.ft = t ? mod( t, 0.6 ) : 1;
			ctx.setTransform( view.scale, 0, 0, view.scale, 0, 0 );
			drawScene( ctx, theme, decor, view.w, { t: t, scroll: scroll, gates: gates, player: player } );
		}

		render( 0 );

		return {
			render: render,
			resize: function () {
				view = fitCanvas( canvas );
				player.x = view.w * 0.26;
			}
		};
	}

	window.Skyhop404 = { themes: THEME_KEYS, mount: mount, preview: preview };

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', mount );
	} else {
		mount();
	}
}( window, document ) );
