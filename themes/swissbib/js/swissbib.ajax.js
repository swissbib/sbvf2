/**
 * Swissbib AJAX helper methods
 */
var sbAjax = {

	/**
	 * Create AJAX spinner element, containing hidden value of requested AJAX uri
	 *
	 * @param	{String}	ajaxUrl
	 * @param	{String}	tabId				Actual tab ID, e.g. "external", "swissbib", ...
	 * @param	{String}	containerId			Container, e.g. 'content' or 'sidebar'
	 * @param	{String}	[wrapperDivClass]	e.g. 'filters'
	 * @return	{Element}
	 */
	createSpinnerElement: function(ajaxUrl, tabId, containerId, wrapperDivClass) {
		var spinner	= $('<div/>', {
			class:	'ajax_loading_spinner_transp',
			style:	'width:32px;height:32px;'
		}).append(swissbib.createHiddenField('ajaxuri_' + tabId + '_' + containerId, ajaxUrl));

			// Wrap spinner (if class given)
		if( typeof wrapperDivClass == 'string') {
			spinner	= $('<div/>', {class:	wrapperDivClass }).append(spinner);
		}

		return spinner;
	},



	/**
	 * Get AJAX request URL for "tabbed" search result item (content of tab or sidebar)
	 *
	 * @param	{String}	tabId
	 * @param	{String}	[action]
	 * @param	{String}	[controller]
	 * @param	{Number}	[page]
	 * @return	{String}
	 */
	getTabbedUrl: function(tabId, action, controller, page) {
		page		= page 		? parseInt(page, 10) : 0;
		controller	= controller? controller : 'Search';
		action		= action	? action : 'Tabcontent';

			// Remove 'tabbed_' prefix if left
		var tabKey	= tabId.substr(0, 7) != 'tabbed_' ? tabId : tabId.split('tabbed_')[1];

		return window.location.protocol + "//" + window.location.host + "/vufind/"
			+	controller + "/"
			+	action
			+ 	"?" + swissbib.getSearchQuery()
			+	"&tab=" + tabKey
			+ (page > 0 ? ('&page=' + page) : '')
			;
	},



	/**
	 * Create options object for jQuery AJAX request
	 *
	 * @param	{String}	url
	 * @param	{Boolean}	isCached
	 * @param	{String}	type
	 * @param	{String}	dataType
	 * @return	{Object}
	 */
	setupRequestOptions: function(url, isCached, type, dataType) {
		return {
			type:		type ? type : "GET",
			url:		url,
			cache:		!!isCached,
			dataType:	dataType ? dataType : "html"
		};
	}

};