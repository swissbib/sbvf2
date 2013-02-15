/**
 * Toggles tab areas.
 *
 * @author NOSE
 * @version 1.0.0	initial version			
 */
jQuery.fn.tabbed = function(op) {

	var settings =  {
		selectorTab:		"ul li",
		selectorTabbed:		".tabbed",
		classTabSelected:	"selected",
		classTabbedSelected:"tabbed_selected",
		classTabbedHidden:	"tabbed_hidden",
		animate:			true,
		timeAnimate:		600,
		easingAnimate:		"easeOutQuad",
		cookie:				"tabbed_",
		persist:			true,
		debug:				false
	};

	jQuery.extend(settings, op);
	
		// Elements
	var elContainer = jQuery(this);
	var elsTabs = jQuery(settings.selectorTab, elContainer);
	var elsTabbed = jQuery(settings.selectorTabbed);
	
		// Events
	jQuery(elsTabs).bind("click", actionTabbed);
	
		// Initialize
	initialize();



	/**
	 * Initializes the tabbed.
	 */
	function initialize() {
		dlog("initialize");
		
			// Restore from cookie
		if (settings.persist) {
				// ID
			var tid = jQuery("."+settings.classTabSelected, elContainer).attr("id");
			dlog("tid: " + tid);
			
				// Cookie
			var cname = encodeURI(settings.cookie+jQuery(elContainer).attr("rel"));
			if (jQuery.cookie(cname)) {
				var tid = jQuery.cookie(cname);
				dlog("cookie: " + tid);
			}
			
				// Change
			if (tid) {
				changeTabbed(tid);
			}
		}
	}



	/**
	 * Changes the tabs.
	 *
	 * @param	{Event}	e
	 */
	function actionTabbed(e) {
		dlog("actionTabbed");
		
			// Prevent
		var evt = jQuery.Event(e);
		evt.preventDefault();
		evt.stopPropagation();	
		
		var tabID = jQuery(this).attr("id");
		
			// Persist active tab preference?
		if (settings.persist) {
				// Write cookie
			var cookieName = encodeURI(settings.cookie+jQuery(elContainer).attr("rel"));
			var cookieValue= tabID;
			jQuery.cookie(cookieName, cookieValue, {path: '/'});
		}
		
			// Change active tab
		changeTabbed(tabID, settings.animate);
	}



	/**
	 * @param	{String}	tabId
	 * @param	{Boolean}	animate
	 */
	function changeTabbed(tabId, animate) {
		dlog("changeTabbed: " + tabId);
		
			// Selected
		jQuery(elsTabs).removeClass(settings.classTabSelected);
		jQuery("#"+tabId, elContainer).addClass(settings.classTabSelected);

		var elsTabbedSelected = jQuery("."+tabId);
		
			// Change
		jQuery(elsTabbed).addClass(settings.classTabbedHidden);
		jQuery(elsTabbed).removeClass(settings.classTabbedSelected);
		jQuery(elsTabbedSelected).removeClass(settings.classTabbedHidden);
		jQuery(elsTabbedSelected).addClass(settings.classTabbedSelected);
		
			// Animate
		if (animate) {
			jQuery(elsTabbedSelected).css({"opacity":0});
			jQuery(elsTabbedSelected).animate({"opacity":1}, settings.timeAnimate, settings.easingAnimate, function() {
				jQuery(elsTabbedSelected).css({"opacity":null});
			});
		}
	}
	
	
	
	/*
	 * Debug log.
	 *
	 * @param	{String) msg
	 */
	function dlog(msg) {
		if (settings.debug) {
			console.log("Tabbed: " + msg);
		}
	}

    return this;
};