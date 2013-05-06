/**
 * Swissbib facets
 */
var sbFacets = {

	/**
	 * Prevent default HREF triggering and reroute to AJAX request
	 */
	init: function() {
		var facets	= $('#sidebar div.facets a');
			// Exclude facets own options and publication date slider
		facets	= facets.not('li.facet_more a');
		facets	= facets.not('a.ui-slider-handle');

			// Setup re-routing
		facets.click(function(event) {
			event.stopPropagation();
			event.preventDefault();

			var searchQueryTab, searchQuerySidebar;

			if( this.href.indexOf("/Results?") != -1 ) {
				searchQueryTab		= this.href.replace('/Results?', '/Tabcontent?');
				searchQuerySidebar	= this.href.replace('/Results?', '/Tabsidebar?')
			} else if( this.href.indexOf("/Summon/Search?") ) {
				searchQueryTab		= this.href.replace('/Summon/Search?', '/Summon/Tabcontent?tab=summon&');
				searchQuerySidebar	= this.href.replace('/Summon/Search?', '/Summon/Tabsidebar?tab=summon&')
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