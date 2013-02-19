/**
 * swissbib VuFind Javascript
 *
 * @requires jquery, jquery.debug, jquery.cookie, jquery.bgIframe, jquery.hoverIntent, jquery.easing,
 *           jquery.toggler, jquery.menu, jquery.nyroModal, jquery.autocomplete, jquery.dropdown,
 *			 jquery.ui, jquery.ui.tabs
 *
 * initial version by NOSE
 */
var swissbib = {

	/** @var	{Boolean}	ie */
	ie:	false,

	/** @var	{Boolean}	ie6 */
	ie6:	false,

	/** @var	{Object}	Log of "tabbed" tabs with already AJAXed / loaded content */
	tabbedLoadedContent: {},



    /**
     * Initialize on ready.
     */
    initOnReady: function(){
        window.DEBUG = false;	// debug flag

        	// Context
        var contextHeader	= jQuery("#header");
        var contextSearch	= jQuery("#search");
        var contextMain		= jQuery("#main");
        var contextContent	= jQuery("#content");
        var contextAll		= jQuery("#header, #search, #main");

        	// Init interface
        swissbib.initBrowserFlags();
        swissbib.initNavigation(contextHeader);
//        swissbib.initAutocomplete(ctxAll);
        swissbib.initToggler(contextMain);
        swissbib.initTabs(contextContent);
        swissbib.initForms(contextAll);
        swissbib.initModal(contextMain);
        swissbib.initLinks(contextMain);
        swissbib.initModalNBImages(contextMain);

			// "tabbed" = tab containers e.g. search result tabs
        swissbib.initTabbed(contextMain);

        swissbib.initHints(contextMain);
    },



    /**
     * Initialize on load.
     */
    initOnLoad: function(){

    },



	/**
	 * Initializes the browser flags.
	 */
    initBrowserFlags: function() {
        var tag = "notdetected";

        if (jQuery.browser.msie) {
            tag = "ie";
            swissbib.ie = true;
            if (jQuery.browser.version.substr(0,1)<7) {
                swissbib.ie6 = true;
            }
        } else if (jQuery.browser.mozilla) {
            tag = "mozilla";
        } else if (jQuery.browser.safari){
            tag = "safari";
        } else if (jQuery.browser.opera){
            tag = "opera";
        }

        jQuery("body").addClass(tag);
    },



    /**
     * Initializes the navigation.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initNavigation: function(ctx){
        jQuery("#navigation", ctx).menunav();
    },



    /**
     * Initializes the toggler.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initToggler: function(ctx){
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
	 *
	  @param	{Element}	ctx		Selector context
     */
    initTabs: function(ctx) {
        jQuery(".tabs").tabs({cookie:{expires:30}});
    },



    /**
     * Initialize "tabbed" elements
     */
    initTabbed: function(ctx) {
			// Register tabs with content already loaded
		this.tabbedLoadedContent = {};
		this.tabbedLoadedContent[this.getIdSelectedTab()] = true;

			// Init "tabbed" containers
        jQuery("#tabbed").each(function(i, tabbed){
            jQuery(tabbed).tabbed({"animate":!swissbib.ie});
        });
    },



	/**
	 * Get currently selected tab
	 *
	 * @param	{String}	baseClassname	Default: 'tabbed'
	 * @return	{Element}
	 */
	getSelectedTab: function(baseClassname) {
		baseClassname	= baseClassname ? baseClassname : 'tabbed';

		return jQuery("#" + baseClassname + " ul li.selected")[0];
	},



	/**
	 * Get id of selected tab
	 *
	 * @param	{String}			classnamePrefix
	 * @return	{String|Boolean}	Selected tab ID (w/o "tabbed_" prefix)
	 */
	getIdSelectedTab: function(classnamePrefix) {
		classnamePrefix	= classnamePrefix ? classnamePrefix : "tabbed";

		var element	= this.getSelectedTab(classnamePrefix);

		return element ? element.id : false;
	},



//	/**
//	 * Get key from (prefixed) tab ID (e.g. 'tabbed_swissbib' -> 'swissbib')
//	 *
//	 * @param	{String}	tabID
//	 * @param	{String}	classnamePrefix
//	 * @return	{String|Boolean}
//	 */
//	getTabKeyFromTabID: function(tabID, classnamePrefix) {
//		classnamePrefix	= classnamePrefix ? classnamePrefix : "tabbed";
//
//		return tabID ? tabID.split(classnamePrefix + "_")[1] : false;
//	},



	/**
	 * (Un/)register the content of the given tab as loaded.
	 *
	 * @param	{String}	tabId
	 * @param	{Boolean}	[isLoaded]		Default: true
	 */
	registerTabContentLoaded: function(tabId, isLoaded) {
		this.tabbedLoadedContent[tabId]	= isLoaded ? isLoaded : true;
	},



	/**
	 * Is content of given tab loaded?
	 *
	 * @param	{String}	tabId
	 * @return	{Boolean}
	 */
	isTabContentLoaded: function(tabId) {
		return !!this.tabbedLoadedContent[tabId];
	},



	/**
	 * Get current search query
	 *
	 * @return	{String}
	 */
	getSearchQuery: function() {
		return $('div#meta ul li.selected a')[0].href.split('?')[1];
	},



    /**
     * Initializes the hints.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initHints: function(ctx) {
        jQuery(".hint").each(function(i, hint){
            jQuery(hint).hint();
        });
    },



    /**
     * Initializes the forms.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initForms: function(ctx) {
        var zIndex = 100;

        jQuery(".info.rollover", ctx).each(function(i, el){
            jQuery(el).rollover();
            jQuery(el).css("z-index", zIndex--)
        });

        jQuery(".info.tooltip", ctx).each(function(i, el){
            jQuery(el).info();
        });


        	// Styled dropdowns
        jQuery(".dropdown", ctx).each(function(i, el){
            jQuery(el).dropdown(i);
        });

        	// Slider
        jQuery("input.slider", ctx).each(function(i, el){
            jQuery(el).hide();

            	// Control
            jQuery(el).after("<div id='slider_"+i+"'></div>");
            var slidecontrol = jQuery("#slider_"+i);
            var slidetitel = jQuery(el).attr("title");
            var slidevalue = 0;
            if (jQuery(el).attr("value") != null && jQuery(el).attr("value") != "") {
                slidevalue = parseInt(jQuery(el).attr("value"));
            }

            	// Create slider
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

            	// Init
            slidecontrol.attr("title",slidetitel + ": " + slidevalue + " / " + vmax);

            	// Events
            jQuery(slidecontrol).bind("slidechange", function(e, ui) {
                jQuery(el).attr("value", ui.value);
                slidecontrol.attr("title", slidetitel + ": " + ui.value + " / " + vmax);
            });
            jQuery(slidecontrol).bind("slide", function(e, ui) {
                slidecontrol.attr("title", slidetitel + ": " + ui.value + " / " + vmax);
            });
        });


        	// Checker
        jQuery(".checker").each(function(i, checker){
            jQuery(checker).checker();
        });
    },



    /**
     * Initialize the modal.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initModal: function(ctx){
//        jQuery(".modal", ctx).nyroModal({
//
//      });
    },



	/**
	 * Init modal NB images
	 *
	 * @param	{Element}	ctx		Selector context
	 */
    initModalNBImages: function(ctx){
//        jQuery(".modalNB", ctx).nyroModal({
//            bgColor:'#4F545F',
//            closeSelector:'.modal_close',
//            type: 'image',
//            hideContent:function hideModal(elts, settings, callback) {
//              elts.wrapper.hide().animate({opacity: 0}, {complete: callback, duration: 80});
//            },
//            showBackground:function showBackground(elts, settings, callback) {
//                elts.bg.css({opacity:0}).fadeTo(300, 0.75, callback);
//            }
//        });
    },



    /**
     * Initialize the (external-, back- , print-) links
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initLinks: function(ctx){
        	// External links
        jQuery(".externallink", ctx).each(function(i, el){
            jQuery(this).attr("target","_blank");
            try {
                var t = jQuery(el).attr("title");
                jQuery(el).attr("title","Externer Link: " + t);
            }
            catch (ex) {
            }
        });

        	// Back links
        jQuery(".back", ctx).bind("click", function() {
            history.back();
            return false;
        });

        	// Print links
        jQuery(".print", ctx).click(function(){window.print();});
    }
};



/**
 * Init swissbib on ready & load
 */
jQuery(document).ready(function(){
    swissbib.initOnReady();
});

jQuery(window).bind("load", function() {
    swissbib.initOnLoad();
});