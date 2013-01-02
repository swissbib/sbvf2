/**
 * Enhanced search.
 * @author NOSE
 * @requires jquery.autocomplete.js
 * @version 1.0.0	initial version			
 */
jQuery.fn.enhancedSearch = function(op) {	
	// defaults
	var defaults =  {	
		selectorSearch:"#search",
		selectorExtendedSearch:"#AdvancedSearchForm",
		selectorInputSearchTerm:"#search_term",
		selectorInputExtendedSearchTerm:"#advancedsearch_searchstring_01",
		server:false
	};
	jQuery.extend(defaults, op);
	
	// references
	var elSearch = jQuery(defaults.selectorSearch);
	var elExtendedSearch = jQuery(defaults.selectorExtendedSearch);
	
	
	/*
	* Simple search.
	*/
	
	
	// focus
	jQuery(defaults.selectorInputSearchTerm,elSearch).focus();
	
	
	// autocomplete
	jQuery(defaults.selectorInputSearchTerm,elSearch).each(function(ind,el) {
																	
			// init autocomplete
			if (defaults.server) {
				// implementation goes here								  
				
			}
			else {
				
				// test values
				var avals = new Array();
				avals.push("Apfel");
				avals.push("Banane");
				avals.push("Zitrone");
				avals.push("Melone");
				avals.push("Gurke");
				avals.push("Pfirsich");
				avals.push("Kiwi");
				avals.push("Erdbeere");
				avals.push("Maulbeere");
				avals.push("Vogelbeere");
				
				// init
				jQuery(el).autocomplete(avals,{autoFill: true, matchContains: true});
			}
	});
	
	
	
	
	/*
	* Advanced search.
	*/
	
	// focus
	jQuery(defaults.selectorInputExtendedSearchTerm,elExtendedSearch).focus();
	

  
    // return
    return this;
};
