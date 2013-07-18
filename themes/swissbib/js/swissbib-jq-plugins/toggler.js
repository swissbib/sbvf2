/**
 * Toggler allows to show/hide a single item.
 * @author NOSE
 *
 * @requires jquery.js, jquery.cookie.js
 * @example $("#item1").toggler("#item1Content",{msgCollapsed:"item 1 collapsed",msgExpanded:"item 1 expanded"});
 * @before  
	<h3 class="collapsed" id="item1">item 1 collapsed</h3>
	<div class="hide" id="item1Content">
	content 1
	</div>
 *
 * @param content The id of the content to show/hide.
 * 
 * @option classCollapsed 	Class name collapsed.
 * @option classExpanded 	Class name expanded.
 * @option classPersist 	Class name persist.
 * @option msgCollapsed		Message for state collapsed.
 * @option msgExpanded		Message for state expanded.
 * @option slide			True to slide.
 * @option slideDownSpeed 	Values slow or fast.
 * @option slideUpSpeed 	Values slow or fast.
 * @option collapsed		True if initially collapsed.
 * @option persist			True to persist state (requires jquery.cookie.js).
 * 
 * 
 * @version 1.0.0	initial version
 */
jQuery.fn.toggler = function(content,op) {
	var defaults =  {	
			classCollapsed:"collapsed",
			classExpanded:"expanded",
			classPersist:"persist",
			msgCollapsed:null,
			msgExpanded:null,
			animate:true,
			timeAnimateShow:240,
			timeAnimateHide:120,
			easingAnimateShow:"easeOutSine",
			easingAnimateHide:"easeInSine",
			expanded:false,
			persist:false,
			cookie:"toggler_"
	};
	jQuery.extend(defaults, op);
	
	
	// references
	var elItem = jQuery(this);
	var elItemContent = jQuery(this);
	if (elItemContent.children().size() > 0) {
		elItemContent = elItemContent.children();	
	}
	jQuery(elItemContent).attr("title",jQuery.trim(jQuery(elItemContent).html()));
	
	// params
	if (jQuery(elItem).hasClass(defaults.classPersist)) {
		defaults.persist = true;	
	}
	var cname = defaults.cookie+jQuery(elItem).attr("id");
    //GH: 20100920: difficulties with cookies set by the toggler mechanism in relation to shibboleth
    // so an additional uriEncoding is used
    cname = encodeURI(cname);
    //alert (cname);
	var expanded = defaults.expanded;
	if (defaults.persist && jQuery.cookie(cname)) {
		var v = jQuery.cookie(cname);
		expanded = v == "expanded";
	}
	
	
	// init
	if (expanded) {
		showItem();
	}
	else {
		hideItem();
	}
	
	// event
	jQuery(this).click(function(){
		toggleItem();
		return false;
	});
	
	/*
	 * Toggles the item.
	 */
	function toggleItem() {
		// item
		if (expanded) {
			hideItem(defaults.animate);
		}	
		else {
			showItem(defaults.animate);
		}
		// state
		expanded = ! expanded;
		if (defaults.persist) {
			var v = "hidden";
			if (expanded) {v = "expanded"}
			jQuery.cookie(cname,v,{path: '/'});
		}
	}
	/*
	 * Shows the item.
	 */
	function showItem(animate) {
		// item
		jQuery(elItem).removeClass(defaults.classCollapsed);
		jQuery(elItem).addClass(defaults.classExpanded);
		if (defaults.msgExpanded) {
			jQuery(elItemContent).html(defaults.msgExpanded).attr("title",defaults.msgExpanded);
		}
		
		// content
		if (animate) {
			jQuery(content).animate({"height":"toggle"},defaults.timeAnimateShow,defaults.easingAnimateShow);
		}
		else {
			jQuery(content).show();
		}		
	}
	/*
	 * Hides the item.
	 */
	function hideItem(animate) {
		// item
		jQuery(elItem).removeClass(defaults.classExpanded);
		jQuery(elItem).addClass(defaults.classCollapsed);
		if (defaults.msgExpanded) {
			jQuery(elItemContent).html(defaults.msgCollapsed).attr("title",defaults.msgCollapsed);
		}
		
		// content
		if (animate) {
			jQuery(content).animate({"height":"toggle"},defaults.timeAnimateHide,defaults.easingAnimateHide);
		}
		else {
			jQuery(content).hide();
		}
	}

  
    // return
    return this;
};
