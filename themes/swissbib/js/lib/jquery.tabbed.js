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
	var elContainer = $(this);
	var elsTabs		= $(sbTabbedSettings.selectorTab, elContainer);
	var elsTabbed	= $(sbTabbedSettings.selectorTabbed);
	
		// Events
	$(elsTabs).bind("click", actionTabbed);
	
		// Initialize
	initialize();

	/**
	 * Initializes the tabs (items classname: "tabbed")
	 */
	function initialize() {
			// Restore from cookie
		if( sbTabbedSettings.persist ) {
			var tabID		= $("." + sbTabbedSettings.classTabSelected, elContainer).attr("id");
			var cookieName	= encodeURI(sbTabbedSettings.cookie + $(elContainer).attr("rel"));

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

		var tabId		= $(this).attr("id");

			// Persist active tab preference?
		if (sbTabbedSettings.persist) {
				// Write cookie
			var cookieName = encodeURI(sbTabbedSettings.cookie + $(elContainer).attr("rel"));
			jQuery.cookie(cookieName, tabId, {path: '/'});
		}
		
			// Change active tab
		changeTabbed(tabId, sbTabbedSettings.animate);
	}



	/**
	 * @param	{String}	tabID
	 * @param	{Boolean}	animate
	 */
	function changeTabbed(tabID, animate) {
			// Set given tab(head) selected / others hidden
		$('.' + sbTabbedSettings.classTabbedSelected).removeClass(sbTabbedSettings.classTabbedSelected);
		$(elsTabs).removeClass(sbTabbedSettings.classTabSelected);

		$("#" + tabID, elContainer).addClass(sbTabbedSettings.classTabSelected);

			// Fetch other classNames of that tabID
		var elsTabbedSelected = $("." + tabID);
			// Set other tabbed elements hidden/ derived of active tab selected
		$(elsTabbed).removeClass(sbTabbedSettings.classTabbedSelected);
		$(elsTabbed).addClass(sbTabbedSettings.classTabbedHidden);
		$(elsTabbedSelected).removeClass(sbTabbedSettings.classTabbedHidden);
		$(elsTabbedSelected).addClass(sbTabbedSettings.classTabbedSelected);
		
			// Animate
		if (animate) {
			$(elsTabbedSelected).css({"opacity":0});
			$(elsTabbedSelected).animate({"opacity":1}, sbTabbedSettings.timeAnimate, sbTabbedSettings.easingAnimate, function() {
				$(elsTabbedSelected).css({"opacity":null});
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