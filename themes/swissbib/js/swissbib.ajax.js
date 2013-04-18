/**
 * Swissbib AJAX helper methods
 */
var sbAjax = {

	/**
	 * Active AJAX requests
	 */
	loadsContent: false,
	loadsSidebar: false,



	/**
	 * Init all elements with AJAX rerouting
	 */
	initAjaxElements: function() {
		sbPagination.init();
		sbJumpMenu.init();
		sbFilters.init();
		sbFacets.init();

		this.loadsContent	= false;
		this.loadsSidebar	= false;
	},



	/**
	 * Add AJAX spinner into active given/content tab
	 *
	 * @param	{String}	[idContainer]
	 * @param	{String}	[idTab]
	 */
	addSpinner:function(idContainer, idTab) {
		idContainer	= idContainer ? idContainer : 'content';
		idTab		= idTab ? idTab : swissbib.getIdSelectedTab();

		var destinationEl	= $('#' + idContainer + ' .' + idTab);
		if( destinationEl.length > 1 ) {
			destinationEl	= $('#' + idContainer + ' .' + idTab + ':first');
		}
		destinationEl.prepend( this.createSpinnerElement() );
	},



	/**
	 * @param	{String}	searchQuery
	 * @param	{String}	tabId			e.g. 'content' or 'sidebar'
	 * @param	{String}	containerId
	 * @param	{Boolean}	replace			replace destination element? (else: update)
	 * @return	{Object}
	 */
	getAjaxOptions: function(searchQuery, tabId, containerId, replace) {
		containerId	= containerId ? containerId : 'content';
		tabId		= tabId ? tabId : swissbib.getIdSelectedTab();
		searchQuery	= searchQuery ? searchQuery : '';
		replace		= replace ? replace : false;

			// Setup request
		var action	= "Tab" + containerId;
		var ajaxUrl = (searchQuery == '') ? sbAjax.getTabbedUrl(tabId, action, "Search") : searchQuery;
			ajaxUrl	= ajaxUrl.replace('?', '?tab=' + tabId.replace('tabbed_', '') + '&')

		var options	= sbAjax.setupRequestOptions(ajaxUrl, false);

		if(replace== true) {
				// Update (content of) element from response
			options.success = function(content) {
				$('#' + containerId + ' .' + tabId).html(content);
				$('#' + containerId + ' .' + tabId).append(swissbib.createHiddenField('ajaxuri_' + tabId + '_sidebar', ajaxUrl));
				sbAjax.initAjaxElements();
				swissbib.initForms();
				return false;
			};
		} else {
				// Replace element from response
			options.success	= function(content) {
				$('#' + containerId + ' .' + tabId).replaceWith(content);
				$('#' + containerId + ' .' + tabId).addClass('tabbed_selected');
				$('#' + containerId + ' .' + tabId).append(swissbib.createHiddenField('ajaxuri_' + tabId + '_sidebar', ajaxUrl));
				sbAjax.initAjaxElements();
				swissbib.initForms();
				return false;
			}
		}

		return options;
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
	 * @param	{String}	[searchQuery]
	 * @param	{String}	[tabId]
	 * @param	{String}	[containerId]
	 */
	ajaxLoadTabContent: function(searchQuery, tabId, containerId) {
		if( this.loadsContent == false ) {
			containerId	= containerId ? containerId : 'content';
            var ajaxOptions	= this.getAjaxOptions(searchQuery, tabId, containerId, true);

			this.loadsContent	= true;
			this.addSpinner(containerId, tabId);
			$.ajax(ajaxOptions);
		}
	},



	/**
	 * Load result tab content via AJAX
	 *
	 * @param	{String}	searchQuery
	 * @param	{String}	[tabId]
	 * @param	{String}	[containerId]
	 */
	ajaxLoadSidebarContent: function(searchQuery, tabId, containerId) {
		if( this.loadsSidebar == false ) {
			containerId	= containerId ? containerId : 'sidebar';
    		var ajaxOptions	= this.getAjaxOptions(searchQuery, tabId, containerId, false);

			this.loadsSidebar	= true;
			this.addSpinner(containerId, tabId);
			$.ajax(ajaxOptions);
		}
	},



	/**
	 * Create AJAX spinner element, containing hidden value of requested AJAX uri
	 *
	 * @return	{Element}
	 */
	createSpinnerElement: function() {
		return $('<div/>', {
			class:	'ajax_loading_spinner_transp',
			style:	'width:32px;height:32px;'
		});
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
			+ 	"?tab=" + tabKey
			+ (page > 0 ? ('&page=' + page) : '')
			+ "&" + swissbib.getSearchQuery();	// must be last part as it might end on #
	}
};