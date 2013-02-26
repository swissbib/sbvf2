/**
 * Swissbib AJAX limit(ing)
 */
var sbLimiting = {

	init: function() {
//		sbLimiting.getSelectorEl().value	= "title_sort";
		this.initObserver();
	},



	/**
	 * Add change-observer (evoke AJAX request) on limit selector element
	 */
	initObserver: function() {
		sbLimiting.clearObserver();

			// Observer: Attach AJAX request to change event
		var idTab		= swissbib.getIdSelectedTab();
		var selectorEl	= this.getSelectorEl(idTab);

		if( $(selectorEl).is('*') ) {
			$(selectorEl).change(function(event) {
				sbLimiting.requestUpdate(event.currentTarget.value);
			});
		}
	},



	/**
	 * (Clone limit selector node to) remove all event listeners
	 */
	clearObserver: function() {
		var selectorEl = this.getSelectorEl( swissbib.getIdSelectedTab() );
		var selectorClean = selectorEl.cloneNode(true);
		selectorEl.parentNode.replaceChild(selectorClean, selectorEl);
	},



	/**
	 * Re-limit: update content with changed limit flag
	 *
	 * @param	{String}	limit
	 */
	requestUpdate: function(limit) {
		alert(limit);
	},



	/**
	 * Get limit selector ("hits per page") of given tab
	 *
	 * @param	{String}	[idTab]	Default: current active tab
	 * @return {Element}
	 */
	getSelectorEl: function(idTab) {
		idTab	= idTab ? idTab : swissbib.getIdSelectedTab();

		return $('#content .' + idTab + ' select.jumpMenu[name=limit]')[0];
	}

};



/* Deactivate default change-observer on sorting selector element
 * @see common.js $('select.jumpMenu').change(function....
 */
$(document).ready(function(){
	if( $(sbLimiting.getSelectorEl()).is('*') ) {
		sbLimiting.init();
	}
});