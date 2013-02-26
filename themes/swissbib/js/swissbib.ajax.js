/**
 * Swissbib AJAX helper methods
 */
var sbAjax = {

	/**
	 * Add AJAX spinner into active given/content tab
	 */
	addSpinner:function(containerId, tabId) {
		containerId	= containerId ? containerId : 'content';
		tabId = tabId ? tabId : swissbib.getIdSelectedTab();

		var spinner	= this.createSpinnerElement();

		$('#' + containerId + ' .' + tabId ).prepend(spinner);
	},



	/**
	 * @param	{String}	[searchQuery]
	 * @param	{String}	[tabId]
	 * @param	{String}	[containerId]
	 */
	ajaxLoadTabContent: function(searchQuery, tabId, containerId) {
		containerId	= containerId ? containerId : 'content';
		tabId = tabId ? tabId : swissbib.getIdSelectedTab();
		searchQuery	= searchQuery ? searchQuery : '';

			// Setup request
		var ajaxUrl;
		if( searchQuery == '' ) {
			ajaxUrl			= sbAjax.getTabbedUrl(tabId, "Tabcontent", "Search");
		} else {
			ajaxUrl= searchQuery + '&tab=' + tabId.replace('tabbed_', '');
		}

		var ajaxOptions		= sbAjax.setupRequestOptions(ajaxUrl, false);
		ajaxOptions.success = function(content) {
			$('#' + containerId + ' .' + tabId).html(content);
			$('#' + containerId + ' .' + tabId).append(
				swissbib.createHiddenField('ajaxuri_' + tabId + '_sidebar', searchQuery)
			);

			sbPagination.init();
			sbJumpMenu.init();

			return false;
		};

			// Show spinner, evoke request
		sbAjax.addSpinner('content', tabId);
		$.ajax(ajaxOptions);
	},



	/**
	 * Load result tab content via AJAX
	 *
	 * @param	{String}	searchQuery
	 * @param	{String}	[tabId]
	 */
	ajaxLoadSidebarContent: function(searchQuery, tabId) {
		var containerId	= 'sidebar';
		tabId = tabId ? tabId : swissbib.getIdSelectedTab();

			// Setup request
		var ajaxUrl;
		if( searchQuery == '' ) {
			ajaxUrl			= sbAjax.getTabbedUrl(tabId, "Tabsidebar", "Search");
		} else {
			ajaxUrl= searchQuery + '&tab=' + tabId.replace('tabbed_', '');
		}
		var ajaxOptions		= sbAjax.setupRequestOptions(ajaxUrl, false);

		ajaxOptions.success = function(content) {
			$('#' + containerId + ' .' + tabId).replaceWith(content);
			$('#' + containerId + ' .' + tabId).addClass('tabbed_selected');
			$('#' + containerId + ' .' + tabId).append(
				swissbib.createHiddenField('ajaxuri_' + tabId + '_sidebar', ajaxUrl)
			);

			sbPagination.init();
			sbJumpMenu.init();

			return false;
		};

		this.addSpinner('sidebar', tabId);

		$.ajax(ajaxOptions);
	},



	/**
	 * Create AJAX spinner element, containing hidden value of requested AJAX uri
	 *
	 * @param	{String}	containerId			Container, e.g. 'content' or 'sidebar'
	 * @param	{String}	[wrapperDivClass]	e.g. 'filters'
	 * @return	{Element}
	 */
	createSpinnerElement: function(containerId, wrapperDivClass) {
		var tabId	= swissbib.getIdSelectedTab();

		var spinner	= $('<div/>', {
			class:	'ajax_loading_spinner_transp',
			style:	'width:32px;height:32px;'
		});

			// Wrap spinner (if class given)
		if( typeof wrapperDivClass == 'string') {
			spinner	= $('<div/>', {class:	wrapperDivClass }).append(spinner);
		}

		return spinner;
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
	}
};