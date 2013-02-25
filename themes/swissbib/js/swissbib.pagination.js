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

		var ajaxUrl;
		if( numPage > 0 ) {
			ajaxUrl	= sbAjax.getTabbedUrl(idTab, 'Tabcontent', 'Search', numPage)
		} else {
			ajaxUrl	= sbAjax.getTabbedUrl(idTab, 'Tabcontent', 'Search')
		}

		return ajaxUrl;
	}

};