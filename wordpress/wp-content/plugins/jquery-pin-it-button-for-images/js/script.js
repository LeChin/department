(function($){
	"use strict";

	window.jpibfi = (function(){

		/* PRIVATE VARIABLES */
		var jpibfi_debug = false;

		/* SETTINGS */

		var pinButtonDimensions = {};
		var pinButtonMargins = {};

		var notSelector = "";
		var filterSelector = "*";

		//max index of images with the data-jpibfi-indexer attribute
		var imageMaxIndex = 0;

		var jpibfi = {};

		//here settings are stored
		jpibfi.settings = {};

		/* FUNCTIONS THAT CAN BE OVERRIDDEN */
		jpibfi.fn = {};

		jpibfi.fn.getImageUrl = function( $elem ) {
			return $elem.data('media') || $elem.attr('src');
		};

		/* FUNCTIONS THAT SHOULDN'T BE OVERRIDDEN */

		jpibfi.addImages = function( selector ) {
			jpibfiLog( '>>addImages' );

			var $elements = $( selector )
					.not( notSelector )
					.not( '[data-jpibfi-indexer]' )
					.filter( filterSelector );

			$elements.each( function () {
				$( this ).attr('data-jpibfi-indexer', imageMaxIndex);
				imageMaxIndex++;
			});
			jpibfiLog( 'Images caught by selectors: ' + imageMaxIndex );
			return $elements;
		};

		jpibfi.prepareImages = function ( $elements ) {
			jpibfiLog( '>>Add Elements' );
			jpibfiLog( 'Elements: ' + $elements.length );
			jpibfiLog( 'Min width:' + jpibfi.settings.minImageWidth);
			jpibfiLog( 'Min height:' + jpibfi.settings.minImageHeight);
			var imageCount = 0;
			$elements.each(function () {
				var $image = $(this);
				if ( this.clientWidth < jpibfi.settings.minImageWidth || this.clientHeight < jpibfi.settings.minImageHeight ) {
					$image.removeAttr( 'data-jpibfi-indexer' );
					return;
				}
				if ( jpibfi.settings.mode == 'static' )
					jpibfiCreateAndShowOverlayDiv( $image, jpibfi.settings.buttonPosition );

				imageCount++;
			});
			jpibfiLog( 'Images caught after filtering: ' + imageCount );
		};

		jpibfi.removeAllImages = function () {
			jpibfiLog( 'Remove Elements called' );
			$( 'div.pinit-overlay' ).remove();
		};

		/* INITIALIZE */

		jpibfi.init = function( jpibfi_options ) {
			jpibfi.settings = {
				pageUrl        		: document.URL,
				pageTitle      		: document.title,
				pageDescription		: $('meta[name="description"]').attr('content') || "",
			}

			jpibfi.settings = $.extend(jpibfi.settings, jpibfi_options);

			pinButtonDimensions = {
				height: parseInt( jpibfi_options.pinImageHeight ),
				width: parseInt( jpibfi_options.pinImageWidth )
			}

			pinButtonMargins = {
				top: parseInt( jpibfi_options.buttonMarginTop ),
				right: parseInt( jpibfi_options.buttonMarginRight ),
				bottom: parseInt( jpibfi_options.buttonMarginBottom ),
				left: parseInt( jpibfi_options.buttonMarginLeft )
			}

			jpibfi_debug = '1' == jpibfi_options.debug;

			jpibfiLog( jpibfi.settings );
			jpibfiLog( pinButtonDimensions );
			jpibfiLog( pinButtonMargins );

			var $containers = $('.jpibfi').closest(jpibfi.settings.containerSelector).addClass('jpibfi_container');

			jpibfiLog( 'Number of containers added: ' + $containers.length );

			//we need to prepare selectors
			notSelector = createSelectorFromList( jpibfi.settings.disabledClasses );
			filterSelector = createSelectorFromList( jpibfi.settings.enabledClasses ) || "*";

			jpibfiLog( 'Filter selector: ' + filterSelector );
			jpibfiLog( 'Not selector: ' + notSelector );

			//EVENT HANDLING - ADDING EVERY NEEDED EVENT

			if ( 'static' == jpibfi.settings.mode) {

				jpibfiLog( 'Adding static mode delegates');

				$( document).delegate( 'div.pinit-overlay', 'hover', function( event ) {
					var hover = event.type === 'mouseenter';
					var indexer = $(this).data("jpibfi-indexer");
					$('.pinit-button[data-jpibfi-indexer="' + indexer + '"]').toggle( hover );
					$('img[data-jpibfi-indexer="' + indexer + '"]').toggleClass( 'pinit-hover', hover );
				});

			} else if ( 'dynamic' == jpibfi.settings.mode ) {

				jpibfiLog( 'Adding dynamic mode delegates');

				$( document ).delegate( 'a.pinit-button', 'mouseenter', function() {
					var $button = $( this );
					clearTimeout( $button.data('jpibfi-timeoutId') );
				});

				$( document ).delegate( 'a.pinit-button', 'mouseleave', function() {
					var $button = $( this );
					var timeoutId = setTimeout( function(){
						$button.remove();
						$('img[data-jpibfi-indexer="' + $button.data( 'jpibfi-indexer' ) + '"]').removeClass( 'pinit-hover' );
					}, 100 );
					$button.data('jpibfi-timeoutId', timeoutId);
				});

				$( document ).delegate( 'img[data-jpibfi-indexer]', 'mouseenter', function() {
					var $image = $( this );
					var indexer = $image.data( 'jpibfi-indexer' );

					var $button = $('a.pinit-button[data-jpibfi-indexer="' + indexer + '"]');

					if ( $button.length == 0 ) {
						//button doesn't exist so we need to create it
						$button = jpibfiCreatePinitButton( indexer );
						var position = $image.offset();
						var imageDimensions = {
							width: $image.get(0).clientWidth,
							height: $image.get(0).clientHeight
						}

						switch( jpibfi.settings.buttonPosition ){
							case '0': //top-left
								position.left += pinButtonMargins.left;
								position.top += pinButtonMargins.top;
								break;
							case '1': //top-right
								position.top += pinButtonMargins.top;
								position.left = position.left + imageDimensions.width - pinButtonMargins.right - pinButtonDimensions.width;
								break;
							case '2': //bottom-left;
								position.left += pinButtonMargins.left;
								position.top = position.top + imageDimensions.height - pinButtonMargins.bottom - pinButtonDimensions.height;
								break;
							case '3': //bottom-right
								position.left = position.left + imageDimensions.width - pinButtonMargins.right - pinButtonDimensions.width;
								position.top = position.top + imageDimensions.height - pinButtonMargins.bottom - pinButtonDimensions.height;
								break;
							case '4': //middle
								position.left = Math.round( position.left + imageDimensions.width / 2 - pinButtonDimensions.width / 2 );
								position.top = Math.round( position.top + imageDimensions.height / 2 - pinButtonDimensions.height / 2 );
								break;
						}

						$image.after( $button );
						$button
							.show()
							.offset({ left: position.left, top: position.top });
					} else {
						//button exists, we need to clear the timeout that has to remove it
						clearTimeout( $button.data('jpibfi-timeoutId') );
					}
					$image.addClass( 'pinit-hover' );
				});

				$( document).delegate( 'img[data-jpibfi-indexer]', 'mouseleave', function() {
					var indexer = $(this).data("jpibfi-indexer");
					var $button = $('a.pinit-button[data-jpibfi-indexer="' + indexer + '"]');

					var timeoutId = setTimeout(function(){
						$button.remove();
						$('img[data-jpibfi-indexer="' + $button.data( 'jpibfi-indexer' ) + '"]').removeClass( 'pinit-hover' );
					}, 100 );
					$button.data('jpibfi-timeoutId', timeoutId);
				});

			}
		};

		return jpibfi;

		/* PRIVATE CREATE ELEMENTS FUNCTIONS */

		function jpibfiCreatePinitButton( indexer ){

			var $anchor = jQuery('<a/>', {
				href: '#',
				"class": 'pinit-button',
				"data-jpibfi-indexer": indexer,
				text: "Pin It"
			});

			$anchor.click( function(e) {
				jpibfiLog( 'Pin In button clicked' );
				var index = $(this).data("jpibfi-indexer");
				var $image = $('img[data-jpibfi-indexer="' + index+ '"]');

				//Bookmark description is created on click because sometimes it's lazy loaded
				var bookmarkDescription = "", descriptionForUrl = "", bookmarkUrl = "";

				//if usePostUrl feature is active, we need to get the data
				if ( jpibfi.settings.usePostUrl ) {
					var $inputWithData = $image.closest(".jpibfi_container").find("input.jpibfi").first();

					if ( $inputWithData.length ) {
						descriptionForUrl = $inputWithData.data("jpibfi-description");
						bookmarkUrl = $inputWithData.data("jpibfi-url");
					}
				}
				bookmarkUrl = bookmarkUrl || jpibfi.settings.pageUrl;

				if ( jpibfi.settings.descriptionOption == 3 )
					bookmarkDescription = $image.attr('title') || $image.attr('alt');
				else if ( jpibfi.settings.descriptionOption == 2 )
					bookmarkDescription = descriptionForUrl || jpibfi.settings.pageDescription;
				else if ( jpibfi.settings.descriptionOption == 4 )
					bookmarkDescription = jpibfi.settings.siteTitle;
				else if ( jpibfi.settings.descriptionOption == 5 )
					bookmarkDescription = $image.data( 'jpibfi-description' );

				bookmarkDescription = bookmarkDescription || ( descriptionForUrl || jpibfi.settings.pageTitle );

				var imageUrl = 'http://pinterest.com/pin/create/bookmarklet/?is_video=' + encodeURIComponent('false')
						+ "&url=" + encodeURIComponent( bookmarkUrl ) + "&media=" + encodeURIComponent( jpibfi.fn.getImageUrl( $image ) )
						+ '&description=' + encodeURIComponent( bookmarkDescription );

				window.open(imageUrl, 'Pinterest', 'width=632,height=253,status=0,toolbar=0,menubar=0,location=1,scrollbars=1');
				return false;
			});

			return $anchor;
		}


		function jpibfiCreateAndShowOverlayDiv( $image, buttonPosition ) {
			var position = $image.offset();

			var $overlay = jpibfiCreateOverlayDiv( $image, buttonPosition );

			$image.after( $overlay );

			$overlay
					.css({
						height: $image.get(0).clientHeight + 'px',
						width: $image.get(0).clientWidth + 'px'
					})
					.show()
					.offset({ left: position.left, top: position.top });

			return $overlay;
		}

		//function creates an overlay div that covers the image
		function jpibfiCreateOverlayDiv( $image, buttonPosition ) {

			var indexer = $image.data("jpibfi-indexer");

			return jQuery('<div/>', {
				"class": 'pinit-overlay',
				"data-jpibfi-indexer": indexer,
				title: $image.attr( 'title' )  || '',
				html: jpibfiCreatePinitButton( indexer).addClass( jpibfiButtonPositionToClass( buttonPosition ))
			})
		}

		/* PRIVATE UTILITY FUNCTIONS */
			
		//returns class name based on given button position
		function jpibfiButtonPositionToClass( buttonPosition ) {
			switch( buttonPosition ){
				case '0': return 'pinit-top-left';
				case '1': return 'pinit-top-right';
				case '2': return 'pinit-bottom-left';
				case '3': return 'pinit-bottom-right';
				case '4': return 'pinit-middle';
				default: return '';
			}
		}

		//function creates a selector from a list of semicolon separated classes
		function createSelectorFromList(classes) {
			var arrayOfClasses = classes.split( ';' );

			var selector = "";

			for (var i = 0; i < arrayOfClasses.length; i++) {
				if ( arrayOfClasses[i] )
					selector += '.' + arrayOfClasses[i] + ',';
			}

			if (selector)
				selector = selector.substr(0, selector.length - 1);

			return selector;
		}

		//functions logs a message or object data if plugin runs in debug mode
		function jpibfiLog( o ) {
			if ( jpibfi_debug && console && console.log ) {
				if ( 'string' == typeof o || o instanceof String ) {
					console.log( 'jpibfi debug: ' + o );
				} else if ( 'object' == typeof o  && typeof JSON !== 'undefined' && typeof JSON.stringify === 'function' ) {
					console.log( 'jpibfi debug: ' + JSON.stringify( o, null, 4 ) );
				} else if ( 'object' == typeof o ) {
					var out = '';
					for (var p in o)
						out += p + ': ' + o[p] + '\n';
					console.log( 'jpibfi debug: ' + out );
				}
			}
		};

	})();

})(jQuery);


;(function($){
	"use strict";

	$(document).ready( function() {
		jpibfi.init( jpibfi_options );

		jpibfi.addImages( jpibfi_options.imageSelector );

		$(window).load( function() {
			jpibfi.prepareImages( $('img[data-jpibfi-indexer]') );
		});

		$(window).resize ( function() {
			jpibfi.removeAllImages();
			jpibfi.prepareImages( $('img[data-jpibfi-indexer]') );
		});
	});

})(jQuery);