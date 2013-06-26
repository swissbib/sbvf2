/**
 * Swissbib search settings
 */
var sbSearchSettings = {

	/**
	 * Install onchange observer on search settings: save to local database
	 */
	init: function() {
		$('div#content form[name="form_useraccount"] select').change(function(event) {
			event.stopPropagation();
			event.preventDefault();

		    if( this.name == 'language' || this.name == 'max_hits' ) {

//				var spinnerElId = 'spinner' + jQuery.guid++;
//				$('#content').prepend(sbAjax.createSpinnerElement(spinnerElId));
//				$('#' + spinnerElId).sprite({fps: 10, no_of_frames: 12});
//				$('#' + spinnerElId).spStart();

				$.ajax({
					url:	  document.location.href.split('/vufind')[0] + '/vufind/MyResearch/Saveaccountlocal'
							+ ('?&' + this.name + '=' + this.value),
					type:		    'GET',
					cache:		    false,
					dataType:	    'html',
					//success: function(data) { $('#content').html(data); return false; },
					error: this.name== 'language' ?
							function() { document.location.reload(); }
						:	function(data) {
								$('#main').html(data.responseText);
								sbSearchSettings.init();
								return false;
							}
				});
			}
		});
	}
};

	// Init on DOM-ready
$(document).ready(function() {
	if ( $('div#content form[name="form_useraccount"]').is('*') ) {
		sbSearchSettings.init();
	}
});