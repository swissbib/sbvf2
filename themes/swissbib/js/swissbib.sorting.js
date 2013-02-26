/**
 * Swissbib AJAX sorting
 */
var sbSorting = {

	init: function() {
		this.initObserver();
	},



	/**
	 * Add change-observer (evoke AJAX request) on sorting selector element
	 */
	initObserver: function() {
		sbSorting.clearObserver();

			// Observer: Attach AJAX request to change event
		var idTab		= swissbib.getIdSelectedTab();
		var selectorEl	= this.getSelectorEl(idTab);

		$(selectorEl).change(function(event) {
			sbSorting.requestUpdate(event.currentTarget.value);
		});
	},



	/**
	 * (Clone sorter node to) remove all event listeners from sorting selector element
	 */
	clearObserver: function() {
		var idTab		= swissbib.getIdSelectedTab();
		var selectorEl	= this.getSelectorEl(idTab);

		if( $(selectorEl).is('*') ) {
			var selectorClean = selectorEl.cloneNode(true);
			selectorEl.parentNode.replaceChild(selectorClean, selectorEl);
		}
	},



	/**
	 * Get sorting selector of given tab
	 *
	 * @param	{String}	[idTab]	Default: current active tab
	 * @return {Element}
	 */
	getSelectorEl: function(idTab) {
		idTab	= idTab ? idTab : swissbib.getIdSelectedTab();

		return $('#content .' + idTab + ' select.jumpMenu[name=sort]')[0];
	},



	/**
	 * Re-sort: update content with changed sorting flag
	 *
	 * @param	{String}	sortBy
	 */
	requestUpdate: function(sortBy) {
		var ajaxUrl	= this.getSortingUrl(sortBy);
		var idTab	= swissbib.getIdSelectedTab();

		var containerId	= 'content';
			// Setup request
		var ajaxOptions		= sbAjax.setupRequestOptions(ajaxUrl, false, 'POST');
//		ajaxOptions.data = { "sort": sbSorting.getSortParam(sortBy) };
		ajaxOptions.data = { "sort": sortBy };
		ajaxOptions.success = function(content) {
			$('#' + containerId + ' .' + idTab).html(content);
			$('#' + containerId + ' .' + idTab).append(
				swissbib.createHiddenField('ajaxuri_' + idTab + '_content', ajaxUrl)
			);

			sbSorting.getSelectorEl().value	= "title_sort";
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



//	/**
//	 * Get VF2 sorting _GET-param from value of sorting selector
//	 * The _POST is used for sorting, the _GET is needed to be available to JS w/o writing it into the DOM
//	 *
//	 * @param	{String}	sortBy
//	 * @return	{String}
//	 */
//	getSortParam: function(sortBy) {
//		switch(sortBy) {
//			case 'author_sort':
//				return '&sort=Author';
//
//			case 'title_sort':
//				return '&sort=Title';
//
//			case 'publishDateSort asc':
//				return '&sort=Date';
//
//			case 'publishDateSort desc':
//				return '&sort=Date,,0';
//
//			case 'relevance':
//			default:
//				return '&sort=relevance';
//		}
//	},



	/**
	 * @param	{String}	sortBy
	 * @return	{String}
	 */
	getSortingUrl: function(sortBy) {
		var idTab	= swissbib.getIdSelectedTab();
		var numPage	= sbPagination.getNumCurrentPage(idTab);
		numPage = (numPage > 0) ? numPage : null;

		var query	= sbAjax.getTabbedUrl(idTab, 'Tabcontent', 'Search', numPage);
		query	= query.replace(/\&sort\=([A-Z|a-z])+/, '');
		query	= query + '&sort=' + sortBy; //this.getSortParam(sortBy);

		return query;
	}

};



/* Deactivate default change-observer on sorting selector element
 * @see common.js $('select.jumpMenu').change(function....
 */
$(document).ready(function(){
	if( $(sbSorting.getSelectorEl()).is('*') ) {
		sbSorting.init();
	}
});