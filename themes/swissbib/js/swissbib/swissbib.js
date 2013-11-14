/**
 * swissbib VuFind Javascript
 *
 * @requires jquery, jquery.debug, jquery.cookie, jquery.bgIframe, jquery.hoverIntent, jquery.easing,
 *           jquery.toggler, jquery.menu, jquery.nyroModal, jquery.autocomplete, jquery.dropdown,
 *           jquery.ui, jquery.ui.tabs
 */
var swissbib = {

    /** @var    {Boolean}    ie */
    ie: false,

    /** @var    {Boolean}    ie6 */
    ie6: false,


    /**
     * Initialize on ready.
     */
    initOnReady: function () {
        window.DEBUG = false;	// debug flag

//      jQuery.migrateMute = true;

        // Context elements
        var contextHeader = $("#header");
//      var contextSearch = $("#search");
        var contextMain = $("#main");
        var contextContent = $("#content");
        var contextAll = $("#header, #search, #main");

        // Init UI elements
        this.initNavigation(contextHeader);

//      swissbib.initAutocomplete(ctxAll);

        this.initToggler(contextMain);
        this.initTabs(contextContent);

        // "Forms" covers initialization of: Rollovers, Tooltips, DropDowns, Sliders, Checkboxes
        this.initForms(contextAll);

        this.initModal(contextMain);
        this.initLinks(contextMain);
        this.initModalNBImages(contextMain);
        this.initAdvancedSearch();

        this.initHints(contextMain);

        this.initBulkExport();

        this.Account.init();
    },


    /**
     * Initializes the navigation
     *
     * @param    {Element}    ctx        Selector context
     */
    initNavigation: function (ctx) {
        $("#navigation", ctx).menunav();
    },


    /**
     * Initializes all (expand-/collapse-able) toggler elements
     *
     * @param    {Element}    ctx        Selector context
     */
    initToggler: function (ctx) {
        var animate = !swissbib.ie6;

        $(".toggler", ctx).each(function (ind, el) {
            var id = $(el).attr("id");
            var msgExpanded = null;
            var msgCollapsed = null;
            var expanded = $(el).hasClass("expanded");
            var title = $(this).attr("title");

            if (title != null && title.indexOf("$") >= 0) {
                var msgs = title.split("$");
                msgCollapsed = msgs[0];
                msgExpanded = msgs[1];
            }

            // Toggle
            $(el).toggler(
                "." + id,
                {    expanded: expanded,
                    msgCollapsed: msgCollapsed,
                    msgExpanded: msgExpanded,
                    animate: animate
                }
            );
        });
    },


    /**
     * Initializes the tabs.
     * @param    {Element}    ctx        Selector context
     */
    initTabs: function (ctx) {
        $(".tabs").tabs({cookie: {expires: 30}});
    },


    /**
     * Get currently selected tab
     *
     * @param    {String}    baseClassname    Default: 'tabbed'
     * @return    {Element}
     */
    getSelectedTab: function (baseClassname) {
        baseClassname = baseClassname ? baseClassname : 'tabbed';
        // Get selected tab
        var tab = $("#" + baseClassname + " ul li.selected")[0];

        if (typeof tab == "undefined") {
            // Fallback: first tab
            tab = $("#" + baseClassname + " ul li")[0];
        }

        return tab;
    },


    /**
     * Get id of selected tab
     *
     * @param    {String}            classnamePrefix
     * @return    {String|Boolean}    Selected tab ID (w/o "tabbed_" prefix)
     */
    getIdSelectedTab: function (classnamePrefix) {
        classnamePrefix = classnamePrefix ? classnamePrefix : "tabbed";

        var element = this.getSelectedTab(classnamePrefix);

        return element ? element.id : false;
    },


    /**
     * Is content of given tab loaded?
     * Check presence of data table as first level child
     *
     * @param    {String}    tabId
     * @return    {Boolean}
     */
    isTabContentLoaded: function (tabId) {
        return $('.' + tabId).children('table.data').length === 1;
    },


    /**
     * @param    {String}    fieldId
     * @param    {String}    fieldValue
     * @return    {Element}
     */
    createHiddenField: function (fieldId, fieldValue) {
        return $('<input/>', {
            type: 'hidden',
            id: fieldId,
            value: fieldValue
        });
    },


    /**
     * Get current search query
     *
     * @param    {Boolean}    [withoutFilters]    default: true
     * @param    {Boolean}    [withoutPageNum]    default: true
     * @return    {String}
     */
    getSearchQuery: function (withoutFilters, withoutPageNum) {
        withoutFilters = withoutFilters ? withoutFilters : true;
        withoutPageNum = withoutPageNum ? withoutPageNum : true;

        var query = $('div#meta ul li.selected a')[0].href.split('?')[1];

        if (withoutFilters) {
            query = query.split('&filter')[0];
        }

        if (withoutPageNum) {
            // Remove page num from query (e.g. '&page=1')
            query = query.replace(/\&page\=(\d)+/, '')
        }

        return query;
    },


    /**
     * Initializes the hints.
     *
     * @param    {Element}    ctx        Selector context
     */
    initHints: function (ctx) {
        $(".hint").each(function (i, hint) {
            $(hint).hint();
        });
    },


    /**
     * Initializes the forms.
     *
     * @param    {Element}    ctx        Selector context
     */
    initForms: function (ctx) {
        this.initRollovers(ctx);
        this.initTooltips(ctx);
        this.initDropDowns(ctx);
        this.initSliders(ctx);
        this.initCheckboxes(ctx);
    },


    /**
     * Init all rollover elements
     *
     * @param    {Element}    ctx        Selector context
     */
    initRollovers: function (ctx) {
        var zIndex = 100;

        $(".info.rollover", ctx).each(function (i, el) {
            $(el).rollover();
            $(el).css("z-index", zIndex--);
        });
    },


    /**
     * Init all tooltip elements
     *
     * @param    {Element}    ctx        Selector context
     */
    initTooltips: function (ctx) {
        $(".info.tooltip", ctx).each(function (i, el) {
            $(el).info();
        });
    },


    /**
     * Init all styled drop-downs
     *
     * @param    {Element}    ctx        Selector context
     */
    initDropDowns: function (ctx) {
        $(".dropdown", ctx).each(function (i, el) {
            $(el).dropdown(i);
        });
    },


    /**
     * Init all sliders
     *
     * @param    {Element}    ctx        Selector context
     */
    initSliders: function (ctx) {

        $("input.slider", ctx).each(function (i, el) {
            $(el).hide();

            // Control
            $(el).after("<div id='slider_" + i + "'></div>");

            var slidecontrol = $("#slider_" + i);
            var slidetitel = $(el).attr("title");
            var slidevalue = 0;

            if ($(el).attr("value") != null && $(el).attr("value") != "") {
                slidevalue = parseInt($(el).attr("value"));
            }

            // Create slider
            var vmin = 0;
            var vmax = 1000;
            var vstep = 100;

            if ($(el).attr("rel") != null) {
                var params = $(el).attr("rel").split(";");
                for (var ii = 0; ii < params.length; ii++) {
                    var p = params[ii].split(":");
                    switch (p[0]) {
                        case "min":
                            vmin = parseInt(p[1]);
                            break;
                        case "max":
                            vmax = parseInt(p[1]);
                            break;
                        case "step":
                            vstep = parseInt(p[1]);
                            break;
                    }
                }
            }

            $(slidecontrol).slider({"value": slidevalue, "min": vmin, "max": vmax, "step": vstep});

            // Init
            slidecontrol.attr("title", slidetitel + ": " + slidevalue + " / " + vmax);

            // Events
            $(slidecontrol).bind("slidechange", function (e, ui) {
                $(el).attr("value", ui.value);
                slidecontrol.attr("title", slidetitel + ": " + ui.value + " / " + vmax);
            });
            $(slidecontrol).bind("slide", function (e, ui) {
                slidecontrol.attr("title", slidetitel + ": " + ui.value + " / " + vmax);
            });
        });
    },


    /**
     * Init all checkboxes
     *
     * @param    {Element}    ctx        Selector context
     */
    initCheckboxes: function (ctx) {
        $(".checker").each(function (i, checker) {
            $(checker).checker();
        });
    },


    /**
     * Initialize the modal
     *
     * @param    {Element}    ctx        Selector context
     */
    initModal: function (ctx) {
//        $(".modal", ctx).nyroModal({
//
//      });
    },


    /**
     * Init modal NB images
     *
     * @param    {Element}    ctx        Selector context
     */
    initModalNBImages: function (ctx) {
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
     * @param    {Element}    ctx        Selector context
     */
    initLinks: function (ctx) {
        // External links
        $(".externallink", ctx).each(function (i, el) {
            $(this).attr("target", "_blank");
            try {
                var t = $(el).attr("title");
                $(el).attr("title", "Externer Link: " + t);
            } catch (ex) { }
        });

        // Back links
        $(".back", ctx).bind("click", function () {
            history.back();
            return false;
        });

        // Print links
        $("#pagefunction_print", ctx).click(function () {
            window.print();
        });
    },


    /**
     * Call detection and initialization of advanced search
     */
    initAdvancedSearch: function () {
        this.AdvancedSearch.init();
    },


    initBulkExport: function () {
        var hasResults = $('#content').find('a.singleLink').length > 0,
            iconElement = $('#pagefunction_save.bulkExport');

        if (hasResults) {
            iconElement.find('.menu a').click($.proxy(this.onBulkExportFormatClick, this));
        } else {
            iconElement.hide();
        }
    },


    /**
     * Handle click on bulk export
     * Append list of record ids to existing link
     *
     * @param    {Object}    event
     */
    onBulkExportFormatClick: function (event) {
        var driver = this.getIdSelectedTab()==='tab_summon' ? 'Summon' : 'VuFind';
        var baseUrl = event.target.href,
            idArgs = [],
            fullUrl,
            ids = $('#content a.singleLink').map(function () {
                return driver + '|' + this.href.split('/').pop()
            }).get();

        event.preventDefault();

        $.each(ids, function (index, id) {
            idArgs.push('i[]=' + id);
        });

        fullUrl = baseUrl + '&' + idArgs.join('&');

//      console.log(fullUrl);

        window.open(fullUrl);
//      location.href = fullUrl;
    }
};


/**
 * Init Swissbib on ready & load
 */
$(document).ready(function () {
    swissbib.initOnReady();
});