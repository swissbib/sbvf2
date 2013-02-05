/**
 * Toggles tab areas.
 * @author NOSE
 * @version 1.0.0	initial version			
 */
jQuery.fn.tabbed = function(op) {
		
	// settings
	var settings =  {
		selectorTab:"ul li",
		selectorTabbed:".tabbed",
		classTabSelected:"selected",
		classTabbedSelected:"tabbed_selected",
		classTabbedHidden:"tabbed_hidden",
		animate:true,
		timeAnimate:600,
		easingAnimate:"easeOutQuad",
		cookie:"tabbed_",
		persist:true,
		debug:false
	};
	jQuery.extend(settings, op);
	
	// elements
	var elContainer = jQuery(this);
	var elsTabs = jQuery(settings.selectorTab,elContainer);
	var elsTabbed = jQuery(settings.selectorTabbed);
	
	// events
	jQuery(elsTabs).bind("click",actionTabbed);
	
	// initialize
	initialize();
	
	
	/**
	* Initializes the tabbed.
	*/
	function initialize() {
		dlog("initialize");
		
		// restore from cookie
		if (settings.persist) {
			
			// id
			var tid = jQuery("."+settings.classTabSelected,elContainer).attr("id");
			dlog("tid: " + tid);
			
			// cookie
			var cname = encodeURI(settings.cookie+jQuery(elContainer).attr("rel"));
			if (jQuery.cookie(cname)) {
				var tid = jQuery.cookie(cname);
				dlog("cookie: " + tid);
			}
			
			// change
			if (tid) {
				changeTabbed(tid);
			}
		}
	}
	
	/**
	* Changes the tabs.
	*/
	function actionTabbed(e) {
		dlog("actionTabbed");
		
		// prevent
		var evt = jQuery.Event(e);
		evt.preventDefault();
		evt.stopPropagation();	
		
		// id
		var tid = jQuery(this).attr("id");
		
		// persist
		if (settings.persist) {
			// write cookie
			var cname = encodeURI(settings.cookie+jQuery(elContainer).attr("rel"));
			var v = tid;
			jQuery.cookie(cname,v,{path: '/'});
		}
		
		// change
		changeTabbed(tid,settings.animate);	
	}
	function changeTabbed(tid,animate) {
		dlog("changeTabbed: " + tid);
		
		// selected
		jQuery(elsTabs).removeClass(settings.classTabSelected);
		jQuery("#"+tid,elContainer).addClass(settings.classTabSelected);
		
		// selected
		var elsTabbedSelected = jQuery("."+tid);
		
		// change
		jQuery(elsTabbed).addClass(settings.classTabbedHidden);
		jQuery(elsTabbed).removeClass(settings.classTabbedSelected);
		jQuery(elsTabbedSelected).removeClass(settings.classTabbedHidden);
		jQuery(elsTabbedSelected).addClass(settings.classTabbedSelected);
		
		// animate
		if (animate) {
			jQuery(elsTabbedSelected).css({"opacity":0});
			jQuery(elsTabbedSelected).animate({"opacity":1},settings.timeAnimate,settings.easingAnimate,function() {
				jQuery(elsTabbedSelected).css({"opacity":null});
			});
		}
		
	}
	
	
	
	/*
	* Debug log.
	*/
	function dlog(msg) {
		if (settings.debug) {
			console.log("Tabbed: " + msg);
		}
	}
  
    // return
    return this;
};