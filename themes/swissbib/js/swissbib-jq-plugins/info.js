/**
 * Shows an info box.
 * @author NOSE
 * @version 1.0.0	initial version			
 */
jQuery.fn.info = function(op) {	
	// defaults
	var defaults =  {
		selectorInfoOverlay:"#info_overlay",
		selectorAppend:"#content",
		markupInfo:"<div id='info_overlay'><span id='info_arrow'><!-- arrow --></span>txt</div>",
		classField:"ifield",
		offsetX:33,
		offsetY:-12,
		timeShow:200,
		timeHide:100
	};
	jQuery.extend(defaults, op);
	
	// elements
	var self = jQuery(this);
	var elOverlay = null;
	
	// event	
	jQuery(self).bind("mouseover",showInfo);
	jQuery(self).bind("mouseout",hideInfo);
	
	// ifield
	if (jQuery(this).hasClass(defaults.classField)) {
		elField = jQuery(this).prev();
		jQuery(elField).bind("focus",showInfo);
		jQuery(elField).bind("blur",hideInfo);
	}
		
	
	/**
	* Show /hide the info.
	*/
	function showInfo() {
		
		// create
		var infoText = jQuery(self).html();
		var markup = defaults.markupInfo.replace(/txt/g, infoText);
		//jQuery(defaults.selectorAppend).append(markup);
		jQuery(self.parent()).append(markup);
		
		
		// show
		var pos = jQuery(self).position();
		var posLeft = pos.left + defaults.offsetX;
		var posTop = pos.top + defaults.offsetY;
		elOverlay = jQuery(defaults.selectorInfoOverlay);
		elOverlay.css({"position":"absolute","z-index":1000,"left":posLeft,"top":posTop});
		elOverlay.fadeIn(defaults.timeShow);	
		
		// return
		return false;
		
	}
	function hideInfo() {
		
		// hide & remove
		elOverlay.fadeOut(defaults.timeHide,function(){
			elOverlay.remove();											 
		});	
		
		// return
		return false;
	}
	
	
  
    // return
    return this;
};