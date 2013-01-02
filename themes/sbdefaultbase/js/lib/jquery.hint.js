/**
 * Hints.
 * @author NOSE
 * @version 1.0.0	initial version			
 */
jQuery.fn.hint = function(op) {
		
	// settings
	var settings =  {
		classHintActive:"hint_active",
		timeout:1800,
		color:"#000000",
		colorBackground:"#D4F2CE",
		timeIndicate:900,
		easingIndicate:"easeOutQuad",
		timeReset:1200,
		easingReset:"easeOutQuad",
		debug:false
	};
	jQuery.extend(settings, op);
	
	// elements
	var elHint = jQuery(this);
	var originalColor= jQuery(this).css("color");
	var originalColorBackground = jQuery(elHint).css("background-color");
	
	// indicate
	indicate();	
	
	/**
	* Indicates a hint.
	*/
	function indicate() {
		dlog("indicate");
		
		// animate
		jQuery(elHint).animate({"backgroundColor":settings.colorBackground,"color":settings.color}, settings.timeIndicate, settings.easingIndicate, function() {
			// reset
			if (settings.timeout > 0) {
				setTimeout(reset, 2000 );
			}
		});

	}
	
	/**
	* Resets the hint.
	*/
	function reset() {
		dlog("reset");
		
		// animate
		jQuery(elHint).animate({"backgroundColor":originalColorBackground,"color":originalColor},settings.timeReset,settings.easingReset,function() {
			// reset
			if (settings.timeout > 0) {
				setTimeout(reset, 2000 );
			}
		});
		
	}
	
	
	/*
	* Debug log.
	*/
	function dlog(msg) {
		if (settings.debug) {
			console.log(msg);
		}
	}
  
    // return
    return this;
};