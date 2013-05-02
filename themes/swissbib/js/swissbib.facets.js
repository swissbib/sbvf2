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
		facets	= facets.not('li.facet_more a');
			// Setup re-routing
		facets.click(function(event) {
			event.stopPropagation();
			event.preventDefault();

				// Default links in swissbib Solr Tab
			if( this.href.indexOf("/Results?") != -1 ) {
				var searchQueryTab		= this.href.replace('/Results?', '/Tabcontent?');
				var searchQuerySidebar	= this.href.replace('/Results?', '/Tabsidebar?')
			}
			sbAjax.ajaxLoadTabContent(searchQueryTab);
			sbAjax.ajaxLoadSidebarContent(searchQuerySidebar);
		});
	}
};

	// Init on DOM-ready
$(document).ready(function(){
	if( $('#sidebar div.facets a').is('*') ) {
		sbFacets.init();
	}
});