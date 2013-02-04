{

    /*
	collapseHandler = function(event){
		event.preventDefault();
		if (!String.isEmpty(this.href) && this.href.charAt(this.href.length-1) != "#" && this.href != document.URL) {
			$.get(this.href, "ajax");
		}
		var t = $(this)
		var sib = t.siblings();
		
		sib.filter("ul").slideToggle();
		var linkToShow = sib.filter("a:hidden");
		if (linkToShow.show().length != 0) t.hide();
		linkToShow.focus();
		return false;
	};
	*/
	
	showMoreHandler = function(event){

		event.preventDefault();
		$(this).parent().addClass('facets_loading');
		//$(this).parent().addClass('facets_loading_transp');
		$.ajax({
			  url: $(this).attr('href'),
			  success: function(data){
				var switchWith = $(data).find("div.facets div.facet").eq(event.data.i);
				$(event.data.toSwitch).replaceWith(switchWith);
				/* attach events again to "collapse icon" link */
				//switchWith.find(" > a").bind('click', collapseHandler);
				/* attach click event to "show more" link
				* here not necessary because of general Toggler mechanism
				* */
				var showMoreLink = switchWith.find("li.facet_more a").first();
				showMoreLink.bind('click',{toSwitch:$(switchWith),i:event.data.i},showMoreHandler);

                //reinitialization of the newly added structure with toggle events is necessary
                swissbib.initToggler(switchWith);


				showMoreLink.focus();
			  },
			  dataType: 'html'
		});
		return false;
	};
	
	// enable opening an closing facet groups via javascript  
	//$("ul.facets li.c11 > a, ul#div-refine > li.c11 > a").bind('click',collapseHandler);
	
	// enable "show more" with AJAX:

	//$("ul.facets li.c11").each(function(index, element){
    /*
    GH: 20100423
    We have to adapt the mechanism developed by OCLC because:
    - the original design done by Nose uses in general so called "Togglers" (special collapseHandler function of OCLC)
    - different HTML structures

    So - we don't need collapseHandler but reinitialization of the Toggler mechanism is necessary

     */
	$("div.facets div.facet").each(function(index, element){
		//$(element).find("a.showmore").first().bind('click', {toSwitch:$(element),i:index},showMoreHandler);
        // we have to find the items in question
		$(element).find("li.facet_more a").first().bind('click', {toSwitch:$(element),i:index},showMoreHandler);
	});
}
