/**
 * Styled Dropdown.
 * @author NOSE
 * @version 1.0.0	initial version			
 */
jQuery.fn.dropdown = function(nb,op) {	
	// defaults
	var defaults =  {
			timeAnimateShow: 90,
			timeAnimateHide: 20,
			maxCharacters:23,
//			easingAnimateShow:"easeOutSine",
			easingAnimateShow:"easeInSine"
	};
	jQuery.extend(defaults, op);
	
	// references
	var elSelect = jQuery(this);
	var elsSelectOption;
	elSelect.hide();
	var elDropdown;
	var elDropdownList;
	
	
	// prepare
	initMarkup();
	
	
	/*
	* Initializes the markup.
	*/
	function initMarkup() {	
		
		// select options
		elsSelectOption = jQuery("option",elSelect);
		var elOptionSelected = jQuery("option:selected",elSelect);
		
		// markup
		var id = "dropdown_"+nb;
		var markup = "<div class='dropdown' id='"+id+"'><h3><a href='#'>&nbsp;</a></h3>";
		markup += "<ul>";
		for (var i = 0; i < elsSelectOption.length; i++) {
			var elOption = jQuery(elsSelectOption[i]);
			markup += "<li><a href='#' rel='option_"+i+"'>"+elOption.html()+"</a></li>";
		}
		markup += "</ul>";
		
		// append
		elSelect.after(markup);
		
		
		// references
		elDropdown = jQuery("#"+id);
		elDropdownList = jQuery("ul",elDropdown);
		//elDropdownList.bgiframe();
		elDropdownList.hide();
		
		// text
		setSelectedText(elOptionSelected.html());
		
		// events
		jQuery("h3",elDropdown).bind("click",showDropdown);
		jQuery("ul li a",elDropdown).bind("click",selectOption);
		
	}
	
	/*
	* Selects the option.
	*/
	function selectOption() {
		// index
		var rel = jQuery(this).attr("rel");
		var ind = rel.substring(7,rel.length);
		var elOptionSelected = jQuery(elsSelectOption[ind]);
		
		// select
		jQuery(elsSelectOption).attr("selected",false);
		jQuery(elOptionSelected).attr("selected",true);
		
		// text
		setSelectedText(elOptionSelected.html());
		
		// hide
		hideDropdown();
		return false;
	}
				
	
	/*
	* Shows the dropdown.
	*/
   function showDropdown(){   
   		// show
		jQuery(elDropdownList).slideDown(defaults.timeAnimateShow,function(){
			// event
			jQuery("html, body").bind("click",hideDropdown);
			jQuery("h3",elDropdown).bind("click",hideDropdown);	
		});
		
		return false;
    }
	
		
	/*
	* Hides the dropdown.
	*/
	function hideDropdown(){
		jQuery(elDropdownList).slideUp(defaults.timeAnimateHide,defaults.easingAnimateHide);
		
		// event
		jQuery("html, body").unbind("click",hideDropdown);
		jQuery("h3",elDropdown).unbind("click",hideDropdown);
    }
	
	/**
	* Extracts the text.
	*/
	function setSelectedText(txt) {
		// title
		jQuery("h3 a",elDropdown).attr("title",txt);
		
		// content
		if (txt != null && txt.length > defaults.maxCharacters) {
			txt = txt.substring(0,defaults.maxCharacters) + "...";	
		}
		jQuery("h3 a",elDropdown).html(txt);
	}
	
	
  
    // return
    return this;
};