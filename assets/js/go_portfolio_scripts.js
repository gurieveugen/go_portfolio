/* -------------------------------------------------------------------------------- /

	Plugin Name: Go – Responsive Portfolio for WP
	Author: Granth
	Version: 1.0.0

	+----------------------------------------------------+
		TABLE OF CONTENTS
	+----------------------------------------------------+

    [1] SETUP
    [2] SLIDER
    [3] POPUP
    [4] ISOTOPE 
    [5] EFFECTS
    [6] OTHER

/ -------------------------------------------------------------------------------- */

(function ($, undefined) {
	"use strict";
	$(function () {

	/* -------------------------------------------------------------------------------- /
		[1]	SETUP
	/ -------------------------------------------------------------------------------- */
		
		/* Detect IE & Chrome */
		var isIE = document.documentMode != undefined && document.documentMode >5 ? document.documentMode : false,
			isChrome = !!window.chrome && !!window.chrome.webstore;
		
		var $portfolio=$('.gw-gopf'),
			$portfolioFilter=$portfolio.filter('.gw-gopf-grid-type').find('.gw-gopf-filter'),
			$portfolioPosts=$portfolio.filter('.gw-gopf-grid-type').find('.gw-gopf-posts'),
			$sliders = $portfolio.filter('.gw-gopf-slider-type').find('.gw-gopf-posts');

		$portfolio.filter('.gw-gopf-slider-type').find('.gw-gopf-col-wrap').css({'display' : 'block', 'visibility' : 'visible' });

		/* Add css correction for chrome */
		if (isChrome) $portfolio.addClass('gw-gopf-chrome');
		
		/* Fix iframe hover */
		if (isIE) { 
			$portfolio.find('.gw-gopf-post iframe').delegate(this, 'mouseenter mouseleave', function (event) {
				var $this = $(this);
				if (event.type == 'mouseenter') {
					$this.closest('.gw-gopf-post').trigger('mouseenter').addClass('gw-gopf-current');
					$this.closest('.gw-gopf-col-wrap').css('zIndex',3);
				} else {
					/* This is not required, just in case
					$this.closest('.gw-gopf-post').trigger('mouseleave').removeClass('gw-gopf-current');
					$this.closest('.gw-gopf-col-wrap').css('zIndex',2);*/
				};
			});
		};		
		
	/* -------------------------------------------------------------------------------- /
		[2]	SLIDER - CarouFredSel Slider
	/ -------------------------------------------------------------------------------- */		
		
		if (jQuery().carouFredSel && $sliders.length) {
			
			var $scrollOverlay = $('<div class="gw-gopf-posts-wrap-inner-overlay">').appendTo('.gw-gopf-posts-wrap-inner').css({
				'position' : 'absolute',
				'top' : 0,
				'z-index' : '2',
				'width' : '100%',
				'height' : '100%',
				'display' : 'none'
			});			
				
			$sliders.each(function(index, element) {
				var $this=$(this);
				
				$this.addClass('gw-gopf-slider').data('sliderDefaults', {				
					responsive : true,
					height : 'variable',
					width : '100%',
					next : {
						button : $this.closest('.gw-gopf-posts-wrap').find('.gw-gopf-slider-controls-wrap').find('.gw-gopf-control-next'),
						onAfter : function(data) {
							if ($this.css('letterSpacing')=='30px') {
								var id=0;
							} else if ($this.css('letterSpacing')=='20px') {
								var id=1;
							} else {
								var id=$this.data('col')-1;						
							};						

							$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
							var items = $this.triggerHandler('currentVisible');
							$this.find('.gw-gopf-col-wrap').css({ 
								'opacity' : 0,
								'zIndex' :	0
							}).eq(id).css({
								'position' : 'relative',
								'left' : '0',
								'z-index' : 'auto'
							});
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
							});
							$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();								
						}
					},
					prev : {
						button : $this.closest('.gw-gopf-posts-wrap').find('.gw-gopf-slider-controls-wrap').find('.gw-gopf-control-prev'),
						onAfter : function(data) {
							if ($this.css('letterSpacing')=='30px') {
								var id=1;
							} else if ($this.css('letterSpacing')=='20px') {
								var id=2;
							} else {
								var id=$this.data('col');						
							};								

							$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
							var items = $this.triggerHandler('currentVisible');
							$this.find('.gw-gopf-col-wrap').css({ 
								'opacity' : 0,
								'z-index' : 0
							}).eq(id).css({
								'position' : 'relative',
								'left': '0',
								'z-index' : 'auto'
							});						
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
							});	
							$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();
						}
					},
					scroll : {
						items: 1,
						onBefore : function(data) {
							var items = $this.triggerHandler('currentVisible');
							
							if ($this.css('letterSpacing')=='30px') {
								var id=1;
							} else if ($this.css('letterSpacing')=='20px') {
								var id=2;
							} else {
								var id=$this.data('col');						
							};

							$this.closest('.gw-gopf-posts-wrap').css('overflow', 'hidden');
							var items = $this.triggerHandler('currentVisible');							
							$this.find('.gw-gopf-col-wrap').css({ 
								'visibility' : 'hidden',
								'position': 'relative',
								'opacity' : 0,
								'zIndex' :	0
							}).eq(id).css({
								'position' : 'absolute',
								'left' : $this.closest('.caroufredsel_wrapper').width()+'px',
								'z-index' : '200',
								'visibility' : 'visible', 
								'opacity' : 1,
								'zIndex' : 'auto'
							});
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
							});	

							$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').show();
							$this.trigger('resume');
						},
						onAfter : function(data) {
							var direction = $this.triggerHandler('configuration', 'direction');
							
							/* Scrolling left */
							if (direction=='left') {
								if ($this.css('letterSpacing')=='30px') {
									var id=0;
								} else if ($this.css('letterSpacing')=='20px') {
									var id=1;
								} else {
									var id=$this.data('col')-1;						
								};							
	
								$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
								var items = $this.triggerHandler('currentVisible');
								$this.find('.gw-gopf-col-wrap').css({
									'opacity' : 0,
									'zIndex' :	0
								}).eq(id).css({
									'position' : 'relative',
									'left' : '0',
									'z-index' : 'auto'
								});
								items.each(function(index, element) {
									$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
								});	
								$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();
							}
							
							/* Scrolling right */
							if (direction=='right') {
								if ($this.css('letterSpacing')=='30px') {
									var id=1;
								} else if ($this.css('letterSpacing')=='20px') {
									var id=2;
								} else {
									var id=$this.data('col');						
								}								
	
								$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
								var items = $this.triggerHandler('currentVisible');
								$this.find('.gw-gopf-col-wrap').css({ 
									'opacity' : 0,
									'z-index' : 0
								}).eq(id).css({
									'position' : 'relative',
									'left': '0',
									'z-index' : 'auto'
								});						
								items.each(function(index, element) {
									$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
								});	
								$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();
							} 	
						}							
					},
					swipe :	{
						onTouch	: true
					},					
					items :	{
						height : 'variable',
						visible : {
							min : 1,
							max : $this.data('col')
						}
					},								
				    onCreate : function (data) {
						$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
						var items = $this.triggerHandler('currentVisible');
						$this.find('.gw-gopf-col-wrap').css({ 
							'visibility' : 'hidden', 
							'position': 'absolute',
							'opacity' : 0
						});
						items.each(function(index, element) {
							$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative' });
						});
						
				        $(window).on('resize', function(){
							var items = $this.triggerHandler('currentVisible');
							$this.find('.gw-gopf-col-wrap').css({ 
								'visibility' : 'hidden', 
								'position': 'absolute',
								'opacity' : 0
							});
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative' });
							});
							var paused = $this.triggerHandler('isPaused');
							if ($this.css('letterSpacing')=='30px') {
								$this.trigger('configuration', ['items.visible', 1]);
							} else if ($this.css('letterSpacing')=='20px') {
								$this.trigger('configuration', ['items.visible', 2]);							
							} else {
								$this.trigger('configuration', ['items.visible', $this.data('col')]);							
							};
							if (paused) { $this.trigger('pause', true); }
				        }).trigger('resize');
				    }	
				});
				
				/* Call slider */
				$this.carouFredSel(jQuery.extend($this.data('slider'), $this.data('sliderDefaults')));
			});

		};
		
	/* -------------------------------------------------------------------------------- /
		[3]	POPUP - Magnific Popup
	/ -------------------------------------------------------------------------------- */		

		if (jQuery().magnificPopup) {
			$portfolio.find('.gw-gopf-magnific-popup').each(function(index, element) {
				$(this).magnificPopup({
					type : 'image',
					closeOnContentClick : true,
					removalDelay : 300,
					mainClass : 'my-mfp-slide-bottom',
					closeMarkup : '<a title="%title%" class="gw-gopf-mfp-close"></a>'
				});		
			});
			
			$portfolio.find('.gw-gopf-magnific-popup-html').each(function(index, element) {
				$(this).magnificPopup({
					type : 'iframe',
					mainClass : 'my-mfp-slide-bottom',
					closeMarkup : '<a title="%title%" class="gw-gopf-mfp-close"></a>',
					callbacks : {
						open : function() {
							var forcedHeight = $(this.currItem.el).data('height');
							if (forcedHeight != undefined) {
								$('.mfp-iframe-scaler').css({
									'paddingTop' : 0,
									'display' : 'table-cell',
									'verticalAlign' : 'middle',
									'height' : forcedHeight
								});
							};
						},
						afterClose: function() {
							/* Firefox bug fix - force to redraw thumbnail */
							var timer=setInterval(function() {
							if ($('.mfp-bg').length==0) {
								clearInterval(timer);
								$portfolio.filter('.gw-gopf-grid-type').removeClass('gw-gopf-isotope-ready');
								$portfolio.find('.gw-gopf-post').css('opacity','0.99');
								setTimeout(function(){ $portfolio.find('.gw-gopf-post').css('opacity','1'); },20)
							}
							},50);
						}
						
					},
					iframe: {					
						patterns: {
							vimeo: {
					      		index: 'vimeo.com/',
							    id: '/',
					      		src: '//player.vimeo.com/video/%id%&amp;autoplay=1'
					   		},
							dailymotion: {
								index: 'dailymotion.com/',
								id: '/',
								src:'//dailymotion.com/embed/video/%id%?autoPlay=1'
							},
							metacafe : {
					      		index : 'metacafe.com/',
							    id: '/',
					      		src: 'http://www.metacafe.com/embed/%id%?ap=1'
					   		},
							soundcloud : {
					      		index : 'soundcloud.com',
							    id: null,
					      		src: '%id%'
					   		},
							mixcloud : {
					      		index : 'mixcloud.com',
							    id: null,
					      		src: '%id%'
					   		},
							beatport : {
					      		index : 'beatport.com',
							    id: null,
					      		src: '%id%'
					   		}																										
						}
					}
				});
			});
		};	
		
		$('body').delegate('.gw-gopf-mfp-close', 'click', function(){
			$.magnificPopup.close();
		});
		
		$portfolio.find('.gw-gopf-magnific-popup, .gw-gopf-magnific-popup-html').on('mfpOpen', function(e) {
			if (jQuery().carouFredSel && $sliders.length) {
				$portfolio.find('.gw-gopf-posts-wrap-inner-overlay').show();
				setTimeout(function() {
					$sliders.each(function(index, element) {
						var $this = $(this);
						$this.trigger('pause', true);
					});
				}, 10);
			}
		});

		$portfolio.find('.gw-gopf-magnific-popup, .gw-gopf-magnific-popup-html').on('mfpAfterClose', function(e) {
			if (jQuery().carouFredSel && $sliders.length) {
				$portfolio.find('.gw-gopf-posts-wrap-inner-overlay').hide();
				setTimeout(function() {
					$sliders.each(function(index, element) {
						var $this = $(this);
						$this.trigger('resume');
					});
				}, 10);
			}
		});
		
	/* -------------------------------------------------------------------------------- /
		[4]	ISOTOPE 
	/ -------------------------------------------------------------------------------- */		
		
		/* Isotope */
		if (jQuery().isotope) {
			
			/* Call Isotope plugin */
			$.fn.callIsotope = function ( filter ) {
				var $this = $(this);
				
				$this.isotope({
					filter : filter,
					transformsEnabled: $this.closest('.gw-gopf').data('transenabled') ? true : false,
					animationEngineString: 'css',
					containerClass : 'gw-gopf-isotope',
					hiddenClass : 'gw-gopf-isotope-hidden',
					itemClass : 'gw-gopf-isotope-item',
					layoutMode : 'masonry'   
				});
			};
			
			/* Extend the plugin to hack change column number if required */
			$.extend( $.Isotope.prototype, {
				_masonryReset : function() {
					// layout-specific props
					this.masonry = {};
					
					// FIXME shouldn't have to call this again
					this._getSegments();
					
					/* Hack - set col number manually */
					if (this.element.css('letterSpacing')=='30px') {
						this.masonry.cols = 1;
					} else if (this.element.css('letterSpacing')=='20px') {
						this.masonry.cols = 2;
					} else {
						this.masonry.cols = this.element.data('col');					
					};	
					var i = this.masonry.cols;
					/* end of Hack */
					
					this.masonry.colYs = [];
					while (i--) {
						this.masonry.colYs.push( 0 );
					}
				}					
			});
			
			/* Filter button events */
			$portfolioFilter.delegate('div a', 'click', function(e) {
				var $this=$(this), $parent=$this.closest('span'), filter;
				e.preventDefault();

				$parent.addClass('gw-gopf-current').siblings().removeClass('gw-gopf-current');				
				if ($parent.data('filter')==undefined) {
					$this.closest('.gw-gopf').find('.gw-gopf-posts').callIsotope('*');
				} else {
					$this.closest('.gw-gopf').find('.gw-gopf-posts').callIsotope('[data-filter~="'+$parent.data('filter')+'"]');
				};
			});
			
			
			/* Call Isotope plugin */
			$portfolio.filter('.gw-gopf-grid-type').each(function(index, element) {
				var $this = $(this);
				$this.find('.gw-gopf-posts').callIsotope('*');
				if (!$this.hasClass('gw-gopf-isotope-ready')) { $this.closest('.gw-gopf').addClass('gw-gopf-isotope-ready'); };
			});
			
		};

	/* -------------------------------------------------------------------------------- /
		[5]	EFFECTS
	/ -------------------------------------------------------------------------------- */	

		/* Effects */
		$portfolio.find('.gw-gopf-post').delegate(this, 'mouseenter mouseleave', function (event) {

			var $this = $(this),
				postHeight = $this.outerHeight(),
				$content = $this.find('.gw-gopf-post-content'),
				contentHeight = $content.outerHeight(),
				$overlayInner = $this.find('.gw-gopf-post-overlay-inner'),
				overlayInnerHeight = $overlayInner.height();
			
			if (event.type == 'mouseenter') {
				$this.find('.gw-gopf-post-overlay').css('height', $this.find('.gw-gopf-post-header').outerHeight());
			}
			
			/* Flex Slide Up */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-up')) {
				if (event.type == 'mouseenter') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};	
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});							
				} else {
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : postHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'height' : postHeight
					});						
				};
			};	
			
			/* Flex Slide & Push Up */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-up')) {
				if (event.type == 'mouseenter') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};						
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'top' : postHeight-contentHeight < 0 ? 100 : contentHeight,
						'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : (postHeight-contentHeight < 0 ? 100 : contentHeight)*-1,
						'marginBottom' : postHeight-contentHeight < 0 ? 100 : contentHeight
					});														
				} else {
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : postHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'top' : 0,
						'height' : postHeight
					});
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : 0,
						'marginBottom' : 0
					});															
				};
			};
			
			/* Flex Slide & Push Up Full */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-up-full')) {
				if (event.type == 'mouseenter') {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : postHeight*-1,
						'marginBottom' : postHeight
					});														
				} else {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : 0,
						'marginBottom' : 0
					});															
				};
			};			
			
			/* Flex Slide Down */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-down')) {
				if (event.type == 'mouseenter') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};									
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});							
				} else {
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : postHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'height' : postHeight
					});								
				};
			};
			
			/* Flex Slide & Push Down */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-down')) {
				if (event.type == 'mouseenter') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};						
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'bottom' : (postHeight-contentHeight < 0 ? 100 : contentHeight),
						'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
					});
					$this.find('.gw-gopf-post-header').css({
						'marginBottom' : (postHeight-contentHeight < 0 ? 100 : contentHeight)*-1,
						'marginTop' : (postHeight-contentHeight < 0 ? 100 : contentHeight)
					});														
				} else {
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : postHeight
					});
					$this.find('.gw-gopf-post-overlay').css({
						'bottom' : 0,
						'height' : postHeight
					});
					$this.find('.gw-gopf-post-header').css({
						'marginBottom' : 0,
						'marginTop' : 0
					});															
				};	
			};
			
			/* Flex Slide & Push Down Full */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-down-full')) {
				if (event.type == 'mouseenter') {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : postHeight,
						'marginBottom' : postHeight*-1
					});														
				} else {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : 0,
						'marginBottom' : 0
					});															
				};
			};
			
			/* Door style */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-door')) {
				if (event.type == 'mouseenter') {
					$this.css({
						'marginBottom' : contentHeight * -1,
						'paddingBottom' : contentHeight
					});
				} else {
					$this.css({
						'marginBottom' : 0,
						'paddingBottom' : 0
					});
				};
			};
			
			/* Delux Push Up */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-delux-push-up')) {
				if (event.type == 'mouseenter') {
					$this.find('.gw-gopf-post-content-wrap').css('top', $this.find('.gw-gopf-post-header').outerHeight());
					$this.css({
						'marginTop' : contentHeight * -1,
						'paddingBottom' : contentHeight
					});
					
				} else {
					$this.css({
						'marginTop' : 0,
						'paddingBottom' : 0
					});
				};
			};		
			
			/* Delux Push Down */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-delux-push-down')) {
				if (event.type == 'mouseenter') {
					$this.find('.gw-gopf-post-content-wrap').css('bottom', $this.find('.gw-gopf-post-header').outerHeight());
					$this.find('.gw-gopf-post-overlay').css('height', $this.find('.gw-gopf-post-header').outerHeight());
					$this.css({
						'marginBottom' : contentHeight * -1,
						'paddingTop' : contentHeight
					});
				} else {
					$this.css({
						'marginBottom' : 0,
						'paddingTop' : 0
					});
				};
			};									
											
		});			
	
	/* -------------------------------------------------------------------------------- /
		[6]	OTHER
	/ -------------------------------------------------------------------------------- */
	
		$(window).resize(function() { 
			$portfolio.find('.gw-gopf-post').trigger('mouseenter').trigger('mouseleave');
			$portfolio.find('.gw-gopf-posts iframe').each(function(index, element) {
				var $this =$(this);
				$this.css('height', $this.closest('.gw-gopf-post-media-wrap').outerHeight());
			});
			$portfolio.filter('.gw-gopf-isotope-ready').find('.gw-gopf-posts').isotope('reLayout');
		}).resize();
		
	});
}(jQuery));