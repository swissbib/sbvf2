/**
 * Swissbib facets
 */
var sbFacets = {

	/**
	 * Prevent default HREF triggering and reroute to AJAX request
	 */
	init: function() {
		var facets	= $('#sidebar div.facets a');
			// Exclude facets own options
		facets	= facets.not('facet_more');
			// Setup re-routing
		facets.click(function(event) {
			event.stopPropagation();
			event.preventDefault();

			sbAjax.ajaxLoadTabContent(this.href.replace('/Results?', '/Tabcontent?'));
			sbAjax.ajaxLoadSidebarContent(this.href.replace('/Results?', '/Tabsidebar?'));
		});
	}
};

	// Init on DOM-ready
$(document).ready(function(){
	if( $('#sidebar div.facets a').is('*') ) {
		sbFacets.init();
	}
});