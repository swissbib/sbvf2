/**
 * Enhanced search.
 * @author NOSE
 * @requires jquery.autocomplete.js
 * @version 1.0.0	initial version			
 */

jQuery.fn.enhancedSearchSOLR = function(op) {
    //alert ("angekommen");

    //$("#tags").autocomplete( {
    //    source: "/TouchPoint/search/autosuggest"
    //});

};


jQuery.fn.enhancedSearch = function(op) {	
	// defaults
	var defaults =  {	
		selectorSearch:"#search",
		selectorExtendedSearch:"#AdvancedSearchForm",
		selectorInputSearchTerm:"#tags",
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

            //alert (ind);
            //alert (el);

			// init autocomplete
			if (defaults.server) {
                //alert ("jetzt initialisierung");
                //$("#tags").autocomplete('/TouchPoint/search/autosuggest');
                $("#tags").autocomplete( {
                    enable:true,
                    source: "/TouchPoint/search/autosuggest"
                    //source: function(request,response) {
                        //alert (request.term);
                        //response ("response1");
                    //    response (["response1","response2","response3"]);

                    //}

                //$('#tags').autocomplete('enable');

            })
            }

			else {
				
				// test values
				var avals = [];
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
