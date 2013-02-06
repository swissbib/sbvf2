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
	 * Initialize on ready.
	 */
	initOnReady: function(){
		// debug flag
		window.DEBUG = false;
		console.log("swissbib: initOnReady");
		
		
		// context
		var ctxHeader = jQuery("#header");
		var ctxSearch = jQuery("#search");
		var ctxMain = jQuery("#main");
		var ctxContent = jQuery("#content");
		var ctxAll = jQuery("#header, #search, #main");
		
		// init
		swissbib.initBrowserFlags();
		swissbib.initNavigation(ctxHeader);
		//swissbib.initAutocomplete(ctxAll);
		swissbib.initToggler(ctxMain);
		swissbib.initTabs(ctxContent);
		swissbib.initForms(ctxAll);
		swissbib.initModal(ctxMain);
		swissbib.initLinks(ctxMain);
        swissbib.initModalNBImages(ctxMain);
		swissbib.initTabbed(ctxMain);
		swissbib.initHints(ctxMain);
	
	},
	/**
	 * Initialize on load.
	 */
	initOnLoad: function(){
		console.log("swissbib: initOnLoad");
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
		console.log("swissbib: initNavigation");
		jQuery("#navigation",ctx).menunav(); 
	},
	
	/**
	 * Initializes the toggler.
	 */
	initToggler: function(ctx){
		console.log("swissbib: initToggler");
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
		console.log("swissbib: initTabs");
		jQuery(".tabs").tabs({cookie:{expires:30}});	
	},
	
	/**
	* Initializes the tabbed.
	*/
	initTabbed: function(ctx) {
		console.log("swissbib: initTabbed");
		
		// tabbed
		jQuery("#tabbed").each(function(i,tabbed){
			jQuery(tabbed).tabbed({"animate":!swissbib.ie});
		});	
	},
	
	
	/**
	* Initializes the hints.
	*/
	initHints: function(ctx) {
		console.log("swissbib: initHints");
		
		// tabbed
		jQuery(".hint").each(function(i,hint){
			jQuery(hint).hint();
		});	
	},

	
	/**
	* Initializes the forms.
	*/
	initForms: function(ctx) {
		console.log("swissbib: initForms");
		
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
	initAutocomplete: function(ctx) {
		var options = {
			setWidth : 5 /* below this number of results the width is calculated */
		};
		var availableTags = [
			"ActionScript",
			"AppleScript",
			"Asp",
			"BASIC",
			"C",
			"C++",
			"Clojure",
			"COBOL",
			"ColdFusion",
			"Erlang",
			"Fortran",
			"Groovy",
			"Haskell",
			"Java",
			"JavaScript",
			"Lisp, und da hat es dann noch ganz viel Text um zu schauen was passiert wenn der so lang wird.",
			"Perl",
			"PHP",
			"Python",
			"Ruby",
			"Scala",
			"Scheme"
		];
		function highlight(value, term) {
			term = term.split(' ');
			for (var i = 0; i < term.length; i++) {
				value = value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term[i].replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
			}
			return value;
		}
		function setwidth(element, width, maxWidth) {
			/* don't use with large resultset */
			jQuery(element).css({'display':'inline','white-space':'nowrap'});
			var elwidth = jQuery(element).width();
			if (elwidth > width) {
				width = elwidth;
			} 
			if (elwidth > maxWidth) {
				width = maxWidth;
			}
			jQuery(element).css({'display':'block','white-space':'normal'});
			console.log(width);
			return width;
		}
		function uiserach_open(event, ui) {
			var term = event.target.value;
			var wdgt = jQuery(this).autocomplete('widget');
			var width = jQuery(wdgt).width();
			if (options.setWidth > 0 && jQuery('a', wdgt).length < options.setWidth) {
				var setWidth = true;
				var maxWidth = width;
				var width = jQuery(this).outerWidth();
			}
			console.log('count', jQuery('a', wdgt).length);
			jQuery('a', wdgt).each(function(e) {
				jQuery(this).html(highlight(jQuery(this).html(), term));
				if (setWidth) {
					width = setwidth(this, width, maxWidth);
				}
			});
			jQuery(wdgt).css('width',width);
		}
		jQuery('#search_term').autocomplete({
			source: availableTags,
			open: uiserach_open
		});
	},
	
	/**
	 * Initialize the modal.
	 */
	initModal: function(ctx){
		console.log("swissbib: initModal");
		jQuery(".modal",ctx).nyroModal({

		});

	},
	
    initModalNBImages: function(ctx){
        console.log("swissbib: initModalNBImages");
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
    },

	/**
	 * Initialize the links.
	 */
	initLinks: function(ctx){
		console.log("swissbib: initLinks");	
		
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