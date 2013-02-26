/**
 * Swissbib pagination
 */
var sbPagination = {

	/**
	 * Prevent default paging HREF triggering and reroute to AJAX request
	 */
	init: function() {
		var pager	= $('#content div.paging a');

		pager.click(function(event) {
			event.stopPropagation();
			event.preventDefault();

			var url	= this.href.replace('/Results?', '/Tabcontent?');

			sbAjax.ajaxLoadTabContent(url);
		});
	}
};

	// Init on DOM-ready
$(document).ready(function(){
	if( $('#content div.paging a').is('*') ) {
		sbPagination.init();
	}
});