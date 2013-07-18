/**
 * Menu.
 * @author Namics, NOSE
 * @requires jquery.bgiframe.js, jquery.hoverIntent.js
 * @version 1.0.0	initial version			
 */
jQuery.fn.menunav = function(op) {	
	// defaults
	var defaults =  {
			selectorNavitems:"ul:first > li",
			selectorSubnavigation:".subnavigation",
			classOpen:"open",
			delayMouseOver:50,
			delayMouseOut:100,
			slideDownSpeed: 240,
			slideUpSpeed:120
	};
	jQuery.extend(defaults, op);
	
	// animation
	var animation = {
		animated:false
	};
	
	// references
	var elNav = jQuery(this);
	
	
	
	// init
	// detect ie6
//	if ($.browser.msie && $.browser.version.substr(0,1)<7) {
//		jQuery(defaults.selectorSubnavigation,this).bgiframe();
//	}
	jQuery(defaults.selectorSubnavigation, this).hide();
		
	// hover intent config
	var config = {    
     	sensitivity: 3, // number = sensitivity threshold (must be 1 or higher)    
     	interval: defaults.delayMouseOver, // number = milliseconds for onMouseOver polling interval    
     	over: openNav, // function = onMouseOver callback (REQUIRED)    
     	timeout: defaults.delayMouseOut, // number = milliseconds delay before onMouseOut    
     	out: closeNav // function = onMouseOut callback (REQUIRED)    
	};
	jQuery(defaults.selectorNavitems,this).each(function(ind,el){
		if (jQuery("ul",el).size() > 0) {
			jQuery(el).hoverIntent(config);
		}
	});

			
	
	/*
	* Opens the navigation.
	*/
   function openNav(){     
   		var $snav = jQuery(this);
		var $sub = getSub($snav);                                                        
		if($sub.css('display') !== 'block'){
			// animation
			stopAnimation();
			
			// reset class
			jQuery("li",elNav).removeClass(defaults.classOpen); 
			jQuery($snav).addClass(defaults.classOpen);  
			
			// animate
			animation.animated = $sub; 
			$sub.slideDown(defaults.slideDownSpeed, function(){   
					animation.animated = false;
			 });     

		 }
    }
		
	/*
	* Closes the navigation.
	*/
	function closeNav(){
		var $snav = jQuery(this);
		var $sub = getSub($snav);
		
		// animation
		stopAnimation();

		animation.animated = $sub;   
		$sub.slideUp(defaults.slideUpSpeed, function(){
			animation.animated = false;
			jQuery($snav).removeClass(defaults.classOpen); 
		});
		
    }
		
	/*
	* Stops the animation.
	*/
	function stopAnimation(){    
		if(typeof(animation.animated)  === 'object'){
			animation.animated.stop(true,true).hide();
		}
     }
	 
	/*
	* Gets the sub nav.
	*/
	function getSub(el){
            return el.find(defaults.selectorSubnavigation);
    }
  
    // return
    return this;
};