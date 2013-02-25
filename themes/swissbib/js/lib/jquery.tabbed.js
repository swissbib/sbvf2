/**
 * Toggles search result tabs, with resp. content and sidebar content
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
	persist:			true,		// Persist activated tab to reload? (cookie)
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
		if( sbTabbedSettings.persist ) {
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
		var evt = jQuery.Event(e);
		evt.preventDefault();
		evt.stopPropagation();	

		var tabId		= jQuery(this).attr("id");
		var searchQuery	= swissbib.getSearchQuery();

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
		if( tabId != swissbib.getIdSelectedTab() ) {
			changeTabbed(tabId, sbTabbedSettings.animate);
		}
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
		var containerId	= 'content';
			// Setup request
		var ajaxUrl			= swissbib.getTabbedAjaxUrl(tabId, "Tabcontent");
		var ajaxOptions		= getAjaxOptions(ajaxUrl, false);
		ajaxOptions.success = function(content) {
			$('#' + containerId + ' .' + tabId).html(content);
			$('#' + containerId + ' .' + tabId).append(
				swissbib.createHiddenField('ajaxuri_' + tabId + '_content', ajaxUrl)
			);
			return false;
		};
			// Add AJAX spinner to indicate loading process
		$('#' + containerId + ' .' + tabId).append(
			createSpinnerElement(ajaxUrl, tabId, containerId)
		);
			// Evoke request
		$.ajax(ajaxOptions);
	}



	/**
	 * Load result tab content via AJAX
	 *
	 * @param	{String}	tabId
	 * @param	{String}	[searchQuery]
	 */
	function ajaxLoadTabSidebar(tabId, searchQuery) {
		var containerId	= 'sidebar';
			// Setup request
		var ajaxUrl			= swissbib.getTabbedAjaxUrl(tabId, "Tabsidebar");
		var ajaxOptions		= getAjaxOptions(ajaxUrl, false);
		ajaxOptions.success = function(content) {
			$('#' + containerId + ' .' + tabId).replaceWith(content);
			$('#' + containerId + ' .' + tabId).addClass('tabbed_selected');
			$('#' + containerId + ' .' + tabId).append(
				swissbib.createHiddenField('ajaxuri_' + tabId + '_sidebar', ajaxUrl)
			);
			return false;
		};
			// Add AJAX spinner to indicate loading process
		$('#' + containerId + ' .' + tabId).append(
			createSpinnerElement(ajaxUrl, tabId, containerId, 'filters')
		);
			// Evoke request
		$.ajax(ajaxOptions);
	}



	/**
	 * Create AJAX spinner element, containing hidden value of requested AJAX uri
	 *
	 * @param	{String}	ajaxUrl
	 * @param	{String}	tabId				Actual tab ID, e.g. "external", "swissbib", ...
	 * @param	{String}	containerId			Container, e.g. 'content' or 'sidebar'
	 * @param	{String}	[wrapperDivClass]	e.g. 'filters'
	 * @return	{Element}
	 */
	function createSpinnerElement(ajaxUrl, tabId, containerId, wrapperDivClass) {
		var spinner	= jQuery('<div/>', {
			class:	'ajax_loading_spinner_transp',
			style:	'width:32px;height:32px;'
		}).append(swissbib.createHiddenField('ajaxuri_' + tabId + '_' + containerId, ajaxUrl));

			// Wrap spinner (if class given)
		if( typeof wrapperDivClass == 'string') {
			spinner	= jQuery('<div/>', {class:	wrapperDivClass }).append(spinner);
		}

		return spinner;
	}



	/**
	 * @param	{String}	tabID
	 * @param	{Boolean}	animate
	 */
	function changeTabbed(tabID, animate) {
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