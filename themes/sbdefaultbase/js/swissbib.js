/**
 * swissbib Javascript Stuff.
 * @author NOSE
 * @version 1.0.0 initial version
 * @requires jquery, jquery.debug, jquery.cookie, jquery.bgIframe, jquery.hoverIntent, jquery.easing, 
 *           jquery.toggler, jquery.menu, jquery.nyroModal, jquery.autocomplete, jquery.dropdown,
 *			 jquery.ui, jquery.ui.tabs
 */
var swissbib = {
	
	/**
	* Properties.
	*/
	server:false,
	
	
	/**
	 * Initialize on ready.
	 */
	initOnReady: function(){
		// debug flag
		window.DEBUG = false;
		jQuery.log("Swissbib: initOnReady");
		
		
		// context
		var ctxHeader = jQuery("#header");
		var ctxSearch = jQuery("#search");
		var ctxMain = jQuery("#main");
		var ctxContent = jQuery("#content");
		var ctxAll = jQuery("#header, #search, #main");
		
		// init
		swissbib.initBrowserFlags();
		swissbib.initNavigation(ctxHeader);
		swissbib.initEnhancedSearch(ctxAll);
		swissbib.initToggler(ctxMain);
		swissbib.initTabs(ctxContent);
		swissbib.initForms(ctxAll);
		/*Swissbib.initModal(ctxMain);  */
		swissbib.initLinks(ctxMain);
        /*Swissbib.initModalNBImages(ctxMain); */
		swissbib.initTabbed(ctxMain);
		swissbib.initHints(ctxMain);
	
	},
	/**
	 * Initialize on load.
	 */
	initOnLoad: function(){
		jQuery.log("Swissbib: initOnLoad");
	},
	
	/**
	* Initializes the browser flags.
	*/
	ie:false,
	ie6:false,
	initBrowserFlags: function() {
		// browser 
		var tag = "notdetected";
		if (jQuery.browser.msie) {
			tag = "ie";
			swissbib.ie = true;
			if (jQuery.browser.version.substr(0,1)<7) {
				swissbib.ie6 = true;
			}
		}
		else if (jQuery.browser.mozilla) {
			tag = "mozilla";
		}
		else if (jQuery.browser.safari){
			tag = "safari";
		}
		else if (jQuery.browser.opera){
			tag = "opera";
		}
		// tag
		jQuery("body").addClass(tag);	
	},
	
	
	/**
	 * Initializes the navigation.
	 */
	initNavigation: function(ctx){
		jQuery.log("Swissbib: initNavigation");
		jQuery("#navigation",ctx).menu(); 
	},
	
	/**
	 * Initializes the toggler.
	 */
	initToggler: function(ctx){
		jQuery.log("Swissbib: initToggler");
		var animate = true;
		if (swissbib.ie6) {
			animate = false;	
		}
		jQuery(".toggler",ctx).each(function(ind,el) {
			// vars
			var id = jQuery(el).attr("id");
			var msgExpanded = null;
			var msgCollapsed = null;
			var expanded = false;
			var title = jQuery(this).attr("title");
			if (jQuery(el).hasClass("expanded")) {
				expanded = true;	
			}
			if (title != null && title.indexOf("$") >= 0) {
				var msgs = title.split("$");
				msgCollapsed = msgs[0];
				msgExpanded = msgs[1];
			}
			
			// toggle
			jQuery(el).toggler("."+id,{expanded:expanded,msgCollapsed:msgCollapsed,msgExpanded:msgExpanded,animate:animate});
											 
		});
	},
	
	/**
	* Initializes the tabs.
	*/
	initTabs: function(ctx) {
		jQuery.log("Swissbib: initTabs");
		jQuery(".tabs").tabs({cookie:{expires:30}});	
	},
	
	/**
	* Initializes the tabbed.
	*/
	initTabbed: function(ctx) {
		jQuery.log("Swissbib: initTabbed");
		
		// tabbed
		jQuery("#tabbed").each(function(i,tabbed){
			jQuery(tabbed).tabbed({"animate":!swissbib.ie});
		});	
	},
	
	
	/**
	* Initializes the hints.
	*/
	initHints: function(ctx) {
		jQuery.log("Swissbib: initHints");
		
		// tabbed
		jQuery(".hint").each(function(i,hint){
			jQuery(hint).hint();
		});	
	},

	
	/**
	* Initializes the forms.
	*/
	initForms: function(ctx) {
		jQuery.log("Swissbib: initForms");
		
		// info
		var z = 100;
		jQuery(".info.rollover",ctx).each(function(i,el){
			jQuery(el).rollover(); 
			jQuery(el).css("z-index",z--)
		});
		
		jQuery(".info.tooltip",ctx).each(function(i,el){
			jQuery(el).info(); 
		});
		
		
		// styled dropdowns
		jQuery(".dropdown",ctx).each(function(i,el){
			jQuery(el).dropdown(i);
		});
		
		
		// slider
		jQuery("input.slider",ctx).each(function(i,el){
				// hide input
				jQuery(el).hide();
				
				// control
				jQuery(el).after("<div id='slider_"+i+"'></div>");
				var slidecontrol = jQuery("#slider_"+i);
				var slidetitel = jQuery(el).attr("title");
				var slidevalue = 0;
				if (jQuery(el).attr("value") != null && jQuery(el).attr("value") != "") {
					slidevalue = parseInt(jQuery(el).attr("value"));
				}
				
				
				// create slider
				vmin = 0;
				vmax = 1000;
				vstep = 100;
				if (jQuery(el).attr("rel") != null) {
					var params = jQuery(el).attr("rel").split(";");
					for (var i = 0; i < params.length; i++) {
						var p = params[i].split(":");
						switch(p[0]) {
							case "min": vmin = parseInt(p[1]);
							case "max": vmax = parseInt(p[1]);
							case "step": vstep = parseInt(p[1]);
						}
					}
				}
				jQuery(slidecontrol).slider({"value":slidevalue,"min":vmin,"max":vmax,"step":vstep});
				
				// init
				slidecontrol.attr("title",slidetitel + ": " + slidevalue + " / " + vmax);
				
				// events
				jQuery(slidecontrol).bind("slidechange", function(e, ui) {
					jQuery(el).attr("value",ui.value);
					slidecontrol.attr("title",slidetitel + ": " + ui.value + " / " + vmax);
				});
				jQuery(slidecontrol).bind("slide", function(e, ui) {
					slidecontrol.attr("title",slidetitel + ": " + ui.value + " / " + vmax);
				});

		
		});
		
		
		// checker
		jQuery(".checker").each(function(i,checker){
			jQuery(checker).checker();
		});
		
		
	},
	
	/**
	 * Initialize the enhanced search.
	 */
	initEnhancedSearch: function(ctx){
		jQuery.log("Swissbib: initEnhancedSearch");
		
		// test values
		jQuery(ctx).enhancedSearch({server:swissbib.server});
	},
	
	/**
	 * Initialize the modal.

	initModal: function(ctx){
		jQuery.log("Swissbib: initModal");
		jQuery(".modal",ctx).nyroModal({

		});

	},
	
    initModalNBImages: function(ctx){
        jQuery.log("Swissbib: initModalNBImages");
        jQuery(".modalNB",ctx).nyroModal({
            bgColor:'#4F545F',
            closeSelector:'.modal_close',
            type: 'image',
            hideContent:function hideModal(elts, settings, callback) {
              elts.wrapper.hide().animate({opacity: 0}, {complete: callback, duration: 80});
            },
            showBackground:function showBackground(elts, settings, callback) {
                elts.bg.css({opacity:0}).fadeTo(300, 0.75, callback);
            }
        });
    }, */

	/**
	 * Initialize the links.
	 */
	initLinks: function(ctx){
		jQuery.log("Swissbib: initLinks");
		
		// external links
		jQuery(".externallink",ctx).each(function(i,el){
			jQuery(this).attr("target","_blank");
			try {
				var t = jQuery(el).attr("title");
				jQuery(el).attr("title","Externer Link: " + t);
			}
			catch (ex) {
			}										  
		});
		
		// backlinks
		jQuery(".back",ctx).bind("click",function() {
				history.back();
				return false;
		});
		
		// print links
		jQuery(".print",ctx).click(function(){window.print();});
	}
}
jQuery(document).ready(function(){
	swissbib.initOnReady();
});
jQuery(window).bind("load",function() {
	swissbib.initOnLoad();
});