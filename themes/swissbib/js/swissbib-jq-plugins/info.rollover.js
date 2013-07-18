/**
 * Shows an info box.
 * @author NOSE Design AG, Sebastian Wohlrab, 2010-08-19
 * @version 1.0.0   initial version         
 */
 
 (function($){
     	 $.fn.extend({ 
         //plugin name - rollover
         rollover: function(options) {
             //Settings list and the default values
            var o =  {
                windowId: "infoWindow",
                windowInnerId: "infoWindowInner",
                direction: 'left',
                duration: 300
            };
            options = jQuery.extend(o, options);
			
			// all rollovers
            return this.each(function(i) {
                var o = options;
                var self = jQuery(this);
				
				// event
                jQuery(self).bind('mouseover', function() {
					
					// info window already created
                    if ( jQuery('#' + o.windowId).length > 0 ) {
                        if ( jQuery(self).find('#' + o.windowId ).length <= 0 ) {
                            jQuery('#' + o.windowId).remove();
                        }
                    }
                    
 
					// create info window
                    var content = jQuery(self).html();
                    var img = jQuery(self).css('background-image');
                    if ( jQuery('#' + o.windowId).length <= 0 ) {
	
						// markup
                        jQuery(self).append(
                            '<div id="'+ o.windowId +'"><div id="' + o.windowInnerId +'">' + content + '</div></div>'
                        );	

						// reference
						var elWindow = jQuery('#' + o.windowId );

						// left and right
                        if ( Math.ceil( ( jQuery('body').width() - jQuery( jQuery(".page")[0] ).width() ) / 2 ) >= jQuery('#' + o.windowId).width() ) {
                            if ( jQuery(self).hasClass('left') || jQuery(self).hasClass('right') ) {
                                if ( jQuery(self).hasClass('left') ) {
                                    jQuery(elWindow).addClass('left');
                                }

                                if ( jQuery(self).hasClass('right') ) {
                                    jQuery(elWindow).addClass('right');
                                }
                            }
                            else {
                                jQuery(elWindow).addClass( o.direction );
                            }
                        }
                        else {
                            jQuery(elWindow).addClass( 'left' );
                        }

						// show it
                        jQuery(elWindow).animate({'opacity': 1}, o.duration,
                            function(e) {
                                jQuery(elWindow).bind('mouseleave', function(e) {
                                    jQuery(elWindow).animate({
                                        'opacity': 0 }, Math.ceil(o.duration / 2),
                                        function() {
                                            jQuery(elWindow).remove();
                                        }
                                    );
                                });
                            }
                        );
                    }
                });

            });
         	
		}
     });
})(jQuery);