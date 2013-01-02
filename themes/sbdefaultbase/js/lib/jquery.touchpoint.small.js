with (jQuery) {


jQuery.extend({




/**
 * Show search query completion for simple search
 */
simpleSearchQueryCompletion: function(form, field, initialFocus) {
	if (!arguments.callee.result_regexp) arguments.callee.result_regexp = /("|')\s*(.*?)\s*\1\s*,\s*(("|')\s*(?:\d+\/)*\d*\s*\4|\d+)\s*,/g;
	var _result_regexp = arguments.callee.result_regexp;

	if (field) {
		$(field).autocomplete(
			"QueryCompletionProxy",
			{
				initialFocus: initialFocus,
                selectFirst:false,
                scrollHeight:'300px',
                max:30,


                parse: function(data) {
                    var result_regexp = new RegExp(_result_regexp),
                        parsed = [],
                        match;
                    var resultArray = new Array();
                    var i = 0;
                    while (match = result_regexp.exec(data)) {
                        var item = match[2];
                        resultArray[i] = item;
                        i++;
                        /*
                        amount = match[4] ? match[3].substring(1, -2).split("/") : [match[3]];
                        for (var i=amount.length-1; i >= 0; i--) {
                            amount[i] = amount[i].toInt() || 0;
                        }
                        */
                    }
                    resultArray.sort();
                    for(e=0;e<i;e++)
                    {
                        var item = resultArray[e];
                        parsed.push({
                            data: [item],
                            value: item,
                            result: item
                        });
                    }
                    return parsed;
                },

                formatItem: function(item, pos, len, value, q) {
                    return (pos !== 1 || len === 1 || item[0] != q) ?
                            (/*
                                item[1] ?
                                item[0] + " <span class=\"amount\">("
                                    + ((item[1] instanceof Array) ? item[1].join("/") : item[1])
                                    + " " + i18n.map["search.searchfield.suggestion.hits"] + ")</span>" :
                            */
                                item[0]) :
                            false;
                        }

            });
       }
},

/**
 * Reload the search form when changing special form elements.
 *
 * @param form The search form
 */
doSearchFormAction: function(form, focusControl) {
    /*
	var submitCall = function(methodToCallParameter) {
		form[0].elements['methodToCallParameter'].value = methodToCallParameter;
		form[0].submit();
	};
	*/
    /*
	form = $(form)

		.find("input.selectDatabase").click(function() { submitCall("selectDatabase"); }).end()
		.find("select.searchParameters").change(function() { submitCall("searchParameters"); }).end();
    */
	// Query auto-completion
	/*if (form.find("#search-adv").length === 0) */
		$.simpleSearchQueryCompletion(form, focusControl, true);
}




});

}


