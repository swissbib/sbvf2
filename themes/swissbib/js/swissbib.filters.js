/**
 * Swissbib filters (box called "Your Search", above facets)
 */
var sbFilters = {

	/**
	 * Prevent default HREF triggering and reroute to AJAX request
	 */
	init: function() {
		var filters	= $('#sidebar div.filters a');
		filters.click(function(event) {
			event.stopPropagation();
			event.preventDefault();

			sbAjax.ajaxLoadTabContent(this.href.replace('/Results?', '/Tabcontent?'));
			sbAjax.ajaxLoadSidebarContent(this.href.replace('/Results?', '/Tabsidebar?'));
		});
	}
};

	// Init on DOM-ready
$(document).ready(function(){
	if( $('#sidebar div.filters a').is('*') ) {
		sbFilters.init();
	}
});