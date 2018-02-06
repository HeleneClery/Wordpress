( function( $ ) {
	var $body = $( 'body' );

	/**
	* Focus styles for menus.
	*
    */
	$( '.main-navigation' ).find( 'a' ).on( 'focus.argent blur.argent', function() {
		$( this ).parents().toggleClass( 'focus' );
	} );

	/**
    * Slick slideshow configuration
    *
    */
	function slickInit() {
		var $slider = $( '.slick-slider' );

		if ( $slider.length !== 0 ) {
			$slider.slick( {
				arrows		: false,
				dots		: true,
				infinite	: true,
				speed		: 300,
				slidesToShow: 1,
				centerMode	: true,
				variableWidth: true
			} );
		} else {
			return;
		}
		// Fade in the slideshow
		$( '.slick' ).fadeTo( 100, 1 );

		// Add next/prev navigation to the carousel
		$slider.on( 'click', function(e) {
			var clickXPosition = e.pageX - this.offsetLeft;

			// Go to the previous image if the click occurs in the left half of gallery,
			// or the next image if the click occurs in the right half.
			if (clickXPosition < $( window ).width() / 2 ) {
				$( this ).slick('slickPrev');
			} else {
				$( this ).slick('slickNext');
			}
			return false;
		});

        // Add classes to allow next/prev cursor styling
		$slider.on( 'mousemove', function(e){
			var mouseXPosition = e.pageX - this.offsetLeft;
			if (mouseXPosition < $( window ).width() / 2 ) {
				$( this ).removeClass( "right-arrow" );
				$( this ).addClass( "left-arrow" );
			} else {
				$( this ).removeClass( "left-arrow" );
				$( this ).addClass( "right-arrow" );
			}
		});
	}

	/*
	 * Add extra class to large images on pages and in single project view. Props to Intergalactic theme
	 */
	function outdentImages() {
		$( '.page-content img.size-full, .jetpack-portfolio.hentry .entry-content img.size-full' ).each( function() {
			var img = $( this ),
			    caption = $( this ).closest( 'figure' ),
				new_img = new Image();

				new_img.src = img.attr( 'src' );

				$( new_img ).load( function() {

					var img_width = new_img.width;
					if ( img_width >= 780 ) {
						$( img ).addClass( 'size-big' );
					}

				if ( caption.hasClass( 'wp-caption' ) && img_width >= 780 ) {
					caption.addClass( 'caption-big' );
				}
			} );
		} );
	}

	// Initialize Slick slideshow
	$( window ).load( function() {
		slickInit();
		outdentImages();
	});

} )( jQuery );
