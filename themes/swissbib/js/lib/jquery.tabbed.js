/**
 * Toggles tab areas.
 *
 * @author NOSE
 * @version 1.0.0	initial version			
 */
var sbTabbedSettings = {
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
	debug:				true
};

jQuery.fn.tabbed = function(op) {
	jQuery.extend(sbTabbedSettings, op);
	
		// Elements
	var elContainer = jQuery(this);
	var elsTabs		= jQuery(sbTabbedSettings.selectorTab, elContainer);
	var elsTabbed	= jQuery(sbTabbedSettings.selectorTabbed);
	
		// Events
	jQuery(elsTabs).bind("click", actionTabbed);
	
		// Initialize
	initialize();




	/**
	 * Initializes the tabs (items classname: "tabbed")
	 */
	function initialize() {
			// Restore from cookie
		if (sbTabbedSettings.persist) {
			var tabID		= jQuery("." + sbTabbedSettings.classTabSelected, elContainer).attr("id");
			var cookieName	= encodeURI(sbTabbedSettings.cookie + jQuery(elContainer).attr("rel"));

			if (jQuery.cookie(cookieName)) {
				tabID = jQuery.cookie(cookieName);
			}
				// (Re)activate last active tab
			if (tabID) {
				changeTabbed(tabID);
			}
		}
	}



	/**
	 * Loads content of tab and rel. sidebar and changes the active tab.
	 * Evoked on click event upon the observed tab items.
	 *
	 * @param	{Event}	e
	 */
	function actionTabbed(e) {
			// Prevent
		var evt = jQuery.Event(e);
		evt.preventDefault();
		evt.stopPropagation();	

		var tabId		= jQuery(this).attr("id");
		var searchQuery	= swissbib.getSearchQuery();
		dlog("actionTabbed - tab: " + tabId + " activated");

		if ( swissbib.isTabContentLoaded(tabId) == false ) {
				// AJAX-load content of tab and respective sidebar
			ajaxLoadTabContent(tabId, searchQuery);
			ajaxLoadTabSidebar(tabId, searchQuery);
		}

			// Persist active tab preference?
		if (sbTabbedSettings.persist) {
				// Write cookie
			var cookieName = encodeURI(sbTabbedSettings.cookie + jQuery(elContainer).attr("rel"));
			var cookieValue= tabId;
			jQuery.cookie(cookieName, cookieValue, {path: '/'});
		}
		
			// Change active tab
		changeTabbed(tabId, sbTabbedSettings.animate);
	}



	/**
	 * Get URL for AJAX request to item (content of tab or sidebar) of tabbed search
	 *
	 * @param	{String}	tabId
	 * @param	{String}	action
	 * @param	{String}	controller
	 * @return	{String}
	 */
	function getTabbedSearchItemAjaxUrl(tabId, action, controller) {
		controller	= controller ? controller : 'Search';
		var tabKey		= tabId.substr(0, 7) != 'tabbed_' ? tabId : tabId.split('tabbed_')[1];

		return	window.location.protocol + "//" + window.location.host + "/vufind/"
			+	controller + "/"
			+	action
			+	"?tab=" + tabKey
			+ 	"&" + swissbib.getSearchQuery();
	}



	/**
	 * @param	{String}	url
	 * @param	{Boolean}	isCached
	 * @param	{String}	type
	 * @param	{String}	dataType
	 * @return	{Object}
	 */
	function getAjaxOptions(url, isCached, type, dataType) {
		return {
			type:		type ? type : "GET",
			url:		url,
			cache:		!!isCached,
			dataType:	dataType ? dataType : "html"
		};
	}



	/**
	 * Load result tab content via AJAX
	 *
	 * @param	{String}	tabId
	 * @param	{String}	[searchQuery]
	 */
	function ajaxLoadTabContent(tabId, searchQuery) {
		var ajaxUrl			= getTabbedSearchItemAjaxUrl(tabId, "Tabcontent");
		var ajaxOptions		= getAjaxOptions(ajaxUrl, false);
		ajaxOptions.success = function(content) {
			$('#content .tabbed_selected').html(content);
			return false;
		};

		$.ajax(ajaxOptions);
		swissbib.registerTabContentLoaded(tabId);
	}



	/**
	 * Load result tab content via AJAX
	 *
	 * @param	{String}	tabId
	 * @param	{String}	[searchQuery]
	 */
	function ajaxLoadTabSidebar(tabId, searchQuery) {
		var ajaxUrl			= getTabbedSearchItemAjaxUrl(tabId, "Tabsidebar");
		var ajaxOptions		= getAjaxOptions(ajaxUrl, false);
		ajaxOptions.success = function(content) {
			$('#sidebar .' + tabId).replaceWith(content);
			$('#sidebar .' + tabId).addClass('tabbed_selected');
			return false;
		};

		$.ajax(ajaxOptions);
		swissbib.registerTabContentLoaded(tabId);
	}



	/**
	 * @param	{String}	tabID
	 * @param	{Boolean}	animate
	 */
	function changeTabbed(tabID, animate) {
		dlog("changeTabbed: " + tabID);


			// Set given tab(head) selected / others hidden
		jQuery('.' + sbTabbedSettings.classTabbedSelected).removeClass(sbTabbedSettings.classTabbedSelected);
		jQuery(elsTabs).removeClass(sbTabbedSettings.classTabSelected);

		jQuery("#" + tabID, elContainer).addClass(sbTabbedSettings.classTabSelected);

			// Fetch other classNames of that tabID
		var elsTabbedSelected = jQuery("." + tabID);
			// Set other tabbed elements hidden/ derived of active tab selected
		jQuery(elsTabbed).removeClass(sbTabbedSettings.classTabbedSelected);
		jQuery(elsTabbed).addClass(sbTabbedSettings.classTabbedHidden);
		jQuery(elsTabbedSelected).removeClass(sbTabbedSettings.classTabbedHidden);
		jQuery(elsTabbedSelected).addClass(sbTabbedSettings.classTabbedSelected);
		
			// Animate
		if (animate) {
			jQuery(elsTabbedSelected).css({"opacity":0});
			jQuery(elsTabbedSelected).animate({"opacity":1}, sbTabbedSettings.timeAnimate, sbTabbedSettings.easingAnimate, function() {
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
		if (sbTabbedSettings.debug) {
			console.log("Tabbed: " + msg);
		}
	}

    return this;
};