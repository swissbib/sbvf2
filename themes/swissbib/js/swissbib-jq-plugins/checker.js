/**
 * Checker.
 * @author NOSE
 * @version 1.0.0	initial version			
 */
jQuery.fn.checker = function(op) {
		
	// settings
	var settings =  {
		classCheckerOn:"checker_on",
		classCheckerOff:"checker_off",
		debug:false
	};
	jQuery.extend(settings, op);
	
	// elements
	var elChecker = jQuery(this);
	var inputName = jQuery(elChecker).attr("for");
	var elCheckbox = jQuery("input[name="+inputName+"]");
	
	// prepare
	prepare();
	
	// events
	jQuery(elCheckbox).bind("change",update);
	
	
	/**
	* Prepares the component.
	*/
	function prepare() {
		dlog("prepare");
		
		// hide checkbox
		jQuery(elCheckbox).css({"position":"absolute","left":"-100000px","visibility":"visible"});
		
		// check on load
		update();
	}
	
	/**
	* Updates the component.
	*/
	function update() {
		dlog("update");
		
		// current value
		var checked = isChecked();
		
		// checker
		jQuery(elChecker).removeClass(settings.classCheckerOn);
		jQuery(elChecker).removeClass(settings.classCheckerOff);
		if (checked) {
			jQuery(elChecker).addClass(settings.classCheckerOn);
		}
		else {
			jQuery(elChecker).addClass(settings.classCheckerOff);
		}
	}
	
	/**
	* Determines if checked.
	*/
	function isChecked() {
		return !!jQuery(elCheckbox).prop('checked');
	}
	
	/*
	* Debug log.
	*/
	function dlog(msg) {
		if (settings.debug) {
			console.log("jquery.checker: " + msg);
		}
	}
  
    // return
    return this;
};