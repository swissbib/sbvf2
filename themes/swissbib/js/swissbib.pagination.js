/**
 * Swissbib AJAX pagination
 */
var sbPagination = {

	/**
	 * @param	{Number}	numPage
	 */
	paginate: function(numPage) {
		var ajaxUrl	= this.getPaginationUrl(numPage);
		var idTab	= swissbib.getIdSelectedTab();

		var containerId	= 'content';
			// Setup request
		var ajaxOptions		= sbAjax.setupRequestOptions(ajaxUrl, false);
		ajaxOptions.success = function(content) {
			$('#' + containerId + ' .' + idTab).html(content);
			$('#' + containerId + ' .' + idTab).append(
				swissbib.createHiddenField('ajaxuri_' + idTab + '_content', ajaxUrl)
			);

			sbSorting.init();
			return false;
		};
			// Show AJAX spinner to indicate loading process
		$('#' + containerId + ' .' + idTab).prepend(
			sbAjax.createSpinnerElement(ajaxUrl, idTab, containerId)
		);
			// Evoke request
		$.ajax(ajaxOptions);
	},



	/**
	 * @param	{Number}	numPage
	 */
	getPaginationUrl: function(numPage) {
		numPage		= numPage	? parseInt(numPage, 10) : 0;
		var idTab	= swissbib.getIdSelectedTab();

		return sbAjax.getTabbedUrl(idTab, 'Tabcontent', 'Search', numPage)
	},



	/**
 	 * @param	{String}	idTab
	 * @return {Number}
	 */
	getNumCurrentPage: function(idTab) {
		var activePageEl	= $('#content div.' + idTab + ' div.paging_pages li span');

		if( activePageEl.is('*') ) {
			return parseInt( activePageEl[0].innerHTML, 10)
		}
			// Default
		return 0;
	}
};

// No on-DOM-ready init here (paging-links are inline JS w/o observer)