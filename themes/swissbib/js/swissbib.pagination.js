/**
 * Swissbib pagination
 */
var sbPagination = {

	/**
	 * Prevent default paging HREF triggering and reroute to AJAX request
	 */
	init: function() {
		$('#content div.paging a').click(function(event) {
			event.stopPropagation();
			event.preventDefault();

			var url	= this.href.replace('/Results?', '/Tabcontent?');

			sbAjax.ajaxLoadTabContent(url);
		});
	}
};

	// Init on DOM-ready
$(document).ready(function(){
	if ( $('#content div.paging a').is('*') ) {
		sbPagination.init();
	}
});