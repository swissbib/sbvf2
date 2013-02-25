/**
 * swissbib VuFind Javascript
 *
 * @requires jquery, jquery.debug, jquery.cookie, jquery.bgIframe, jquery.hoverIntent, jquery.easing,
 *           jquery.toggler, jquery.menu, jquery.nyroModal, jquery.autocomplete, jquery.dropdown,
 *			 jquery.ui, jquery.ui.tabs
 */
var swissbib = {

	/** @var	{Boolean}	ie */
	ie:	false,

	/** @var	{Boolean}	ie6 */
	ie6:	false,



    /**
     * Initialize on ready.
     */
    initOnReady: function(){
        window.DEBUG = false;	// debug flag

        	// Context elements
        var contextHeader	= $("#header");
//        var contextSearch	= $("#search");
        var contextMain		= $("#main");
        var contextContent	= $("#content");
        var contextAll		= $("#header, #search, #main");

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

		if( $('#tabbed').is('*') ) {
				// Init tabs (if present =after search)
        	swissbib.initTabbed(contextMain);
		}

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

        $("body").addClass(tag);
    },



    /**
     * Initializes the navigation.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initNavigation: function(ctx){
        $("#navigation", ctx).menunav();
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
        $(".toggler",ctx).each(function(ind,el) {
            // vars
            var id = $(el).attr("id");
            var msgExpanded = null;
            var msgCollapsed = null;
            var expanded = false;
            var title = $(this).attr("title");
            if ($(el).hasClass("expanded")) {
                expanded = true;
            }

            if (title != null && title.indexOf("$") >= 0) {
                var msgs = title.split("$");
                msgCollapsed = msgs[0];
                msgExpanded = msgs[1];
            }

            // toggle
            $(el).toggler("."+id,{expanded:expanded,msgCollapsed:msgCollapsed,msgExpanded:msgExpanded,animate:animate});

        });
    },



    /**
     * Initializes the tabs.
	 *
	  @param	{Element}	ctx		Selector context
     */
    initTabs: function(ctx) {
        $(".tabs").tabs({cookie:{expires:30}});
    },



    /**
     * Initialize "tabbed" elements
	 *
	 * @param	{Element}	ctx
     */
    initTabbed: function(ctx) {
			// Register already loaded tab: store it's AJAX URL (content, sidebar)
		var containerIDs= ['content', 'sidebar'];

		$.each(containerIDs, function(index, containerId) {
			var tabId	= swissbib.getIdSelectedTab();
			var url		= swissbib.getTabbedAjaxUrl(tabId, "Tab" + containerId);

			var fieldId	= 'ajaxuri_' + tabId + '_' + containerId;
			$('#' + containerId + ' .' + tabId).append(
				swissbib.createHiddenField(fieldId, url)
			);
		});
			// Init "tabbed" containers
        $("#tabbed").each(function(i, tabbed){
            $(tabbed).tabbed({"animate":!swissbib.ie});
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
			// Get selected tab
		var tab = $("#" + baseClassname + " ul li.selected")[0];

		if( typeof tab == "undefined" ) {
			// Fallback: first tab
			tab	= $("#" + baseClassname + " ul li")[0];
		}

		return tab;
	},



	/**
	 * Get URL for AJAX request to item (content of tab or sidebar) of tabbed search
	 *
	 * @param	{String}	tabId
	 * @param	{String}	[action]
	 * @param	{String}	[controller]
	 * @return	{String}
	 */
	getTabbedAjaxUrl: function(tabId, action, controller) {
		controller	= controller ? controller : 'Search';
		action		= action	 ? action : 'Tabcontent';

			// Remove 'tabbed_' prefix if left
		var tabKey	= tabId.substr(0, 7) != 'tabbed_' ? tabId : tabId.split('tabbed_')[1];

		return window.location.protocol + "//" + window.location.host + "/vufind/"
			+	controller + "/"
			+	action
			+ 	"?" + swissbib.getSearchQuery()
			+	"&tab=" + tabKey;
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



	/**
	 * Is content of given tab loaded?
	 *
	 * @param	{String}	tabId
	 * @return	{Boolean}
	 */
	isTabContentLoaded: function(tabId) {
		var el	= $('input#ajaxuri_' + tabId + '_content');
		if( el.is('*') == false ) {
				// No AJAX URL stored in tab? = not loaded
			return false;
		}
			// Compare URL
		var loadedUrl	= el[0].value;
		return loadedUrl == this.getTabbedAjaxUrl(tabId);
	},



	/**
	 * @param	{String}	fieldId
	 * @param	{String}	fieldValue
	 * @return	{Element}
	 */
	createHiddenField: function(fieldId, fieldValue) {
		var el= $('<input/>', {
			type:	'hidden',
			id:		fieldId,
			value:	fieldValue
		});

		return el;
	},



	/**
	 * Get current search query
	 *
	 * @param	{Boolean}	withoutFilters
	 * @return	{String}
	 */
	getSearchQuery: function(withoutFilters) {
		withoutFilters	= withoutFilters ? withoutFilters : true;

		var query	= $('div#meta ul li.selected a')[0].href.split('?')[1];

		return withoutFilters ? (query.split('&filter')[0]) : query;
	},



    /**
     * Initializes the hints.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initHints: function(ctx) {
        $(".hint").each(function(i, hint){
            $(hint).hint();
        });
    },



    /**
     * Initializes the forms.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initForms: function(ctx) {
        var zIndex = 100;

        $(".info.rollover", ctx).each(function(i, el){
            $(el).rollover();
            $(el).css("z-index", zIndex--)
        });

        $(".info.tooltip", ctx).each(function(i, el){
            $(el).info();
        });


        	// Styled dropdowns
        $(".dropdown", ctx).each(function(i, el){
            $(el).dropdown(i);
        });

        	// Slider
        $("input.slider", ctx).each(function(i, el){
            $(el).hide();

            	// Control
            $(el).after("<div id='slider_"+i+"'></div>");
            var slidecontrol = $("#slider_"+i);
            var slidetitel = $(el).attr("title");
            var slidevalue = 0;
            if ($(el).attr("value") != null && $(el).attr("value") != "") {
                slidevalue = parseInt($(el).attr("value"));
            }

            	// Create slider
            vmin = 0;
            vmax = 1000;
            vstep = 100;

            if ($(el).attr("rel") != null) {
                var params = $(el).attr("rel").split(";");
                for (var i = 0; i < params.length; i++) {
                    var p = params[i].split(":");
                    switch(p[0]) {
                        case "min": vmin = parseInt(p[1]);
                        case "max": vmax = parseInt(p[1]);
                        case "step": vstep = parseInt(p[1]);
                    }
                }
            }

            $(slidecontrol).slider({"value":slidevalue,"min":vmin,"max":vmax,"step":vstep});

            	// Init
            slidecontrol.attr("title",slidetitel + ": " + slidevalue + " / " + vmax);

            	// Events
            $(slidecontrol).bind("slidechange", function(e, ui) {
                $(el).attr("value", ui.value);
                slidecontrol.attr("title", slidetitel + ": " + ui.value + " / " + vmax);
            });
            $(slidecontrol).bind("slide", function(e, ui) {
                slidecontrol.attr("title", slidetitel + ": " + ui.value + " / " + vmax);
            });
        });


        	// Checker
        $(".checker").each(function(i, checker){
            $(checker).checker();
        });
    },



    /**
     * Initialize the modal.
	 *
	 * @param	{Element}	ctx		Selector context
     */
    initModal: function(ctx){
//        $(".modal", ctx).nyroModal({
//
//      });
    },



	/**
	 * Init modal NB images
	 *
	 * @param	{Element}	ctx		Selector context
	 */
    initModalNBImages: function(ctx){
//        $(".modalNB", ctx).nyroModal({
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
        $(".externallink", ctx).each(function(i, el){
            $(this).attr("target","_blank");
            try {
                var t = $(el).attr("title");
                $(el).attr("title","Externer Link: " + t);
            }
            catch (ex) {
            }
        });

        	// Back links
        $(".back", ctx).bind("click", function() {
            history.back();
            return false;
        });

        	// Print links
        $(".print", ctx).click(function(){window.print();});
    }
};



/**
 * Init swissbib on ready & load
 */
$(document).ready(function(){
    swissbib.initOnReady();
});

$(window).bind("load", function() {
    swissbib.initOnLoad();
});