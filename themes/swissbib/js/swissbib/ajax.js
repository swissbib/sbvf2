/**
 * Swissbib AJAX helper methods
 *
 * @todo remove
 */
var sbAjax = {


	/**
	 * Create AJAX spinner element, containing hidden value of requested AJAX uri
	 *
	 * @param	{String}	elementId
	 * @param	{String}	[addStyle]
	 * @return	{Element}
	 */
	createSpinnerElement: function(elementId, addStyle) {
		return $(
			'<div/>', {
				id:		elementId,
				class:	'ajax_spinner',
				style:	'width:26px; height:26px;' + addStyle
		});
	}

};