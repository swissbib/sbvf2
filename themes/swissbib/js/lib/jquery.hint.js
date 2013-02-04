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
		classHintColor:"hint_color",
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
	
	// hint
	jQuery(this).addClass(settings.classHintColor);
	var hintColor= jQuery(this).css("color");
	var hintColorBackground = jQuery(elHint).css("background-color");
	jQuery(this).removeClass(settings.classHintColor);
	
	// indicate
	indicate();	
	
	/**
	* Indicates a hint.
	*/
	function indicate() {
		dlog("indicate");
		
		// animate
		jQuery(elHint).animate({"backgroundColor":hintColorBackground,"color":hintColor}, settings.timeIndicate, settings.easingIndicate, function() {
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