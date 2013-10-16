/**
 * Highlighting of Search Terms in Autosuggest
 */
jQuery.ui.autocomplete.prototype._renderItem = function( ul, item){
	var term = this.term.split(' ').join('|');
	var re = new RegExp("(" + term + ")", "gi") ;
	var t = item.label.replace(re,"<strong>$1</strong>");
	return $( "<li></li>" )
		.data( "item.autocomplete", item )
		.append( "<a>" + t + "</a>" )
		.appendTo( ul );
};

jQuery.ui.autocomplete.prototype.options.position = {
	my: "left top-1",
	at: "left bottom",
	collision: "none"
};