/**
 * Swissbib jumpMenu
 */
var sbJumpMenu = {

	/**
	 * Clear (common.js) default jumpMenu observer(s) and reroute to AJAX request
	 */
	init: function() {
		this.clearObserver();

		var sorter	= $('select.jumpMenu');

		sorter.change(function(event) {
			event.stopPropagation();
			event.preventDefault();

				// Build URL from parent form + selected value
			var parentForm	= $(this).parent('form')[0];
			var url	= parentForm.action;
			var paramName	= this.name;
			var paramValue	= this.value;

			url	= url + ('&' + paramName + '=' + paramValue);
			url	= url.replace('/Results?', '/Tabcontent?');

				// Request
			sbAjax.ajaxLoadTabContent(url);
		});
	},



	/**
	 * Remove event listeners from jumpMenu selectors
	 *
	 * @todo	check for cleaned implementation to deactivate observer (@see common.js)
	 */
	clearObserver: function() {
		var selector	= $('select.jumpMenu');

		$.each(selector, function(index, selEl) {
			var selectorClean = selEl.cloneNode(true);
			selEl.parentNode.replaceChild(selectorClean, selEl);
		});
	}
};

/**
 * Init on DOM-ready
 */
$(document).ready(function(){
	if( $('select.jumpMenu').is('*') ) {
		sbJumpMenu.init();
	}
});