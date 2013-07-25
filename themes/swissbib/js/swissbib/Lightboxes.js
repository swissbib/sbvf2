swissbib.Lightboxes = {

	init: function() {
		console.log('Lightboxes');


		this.initSendListPerMail();
	},


	initSendListPerMail: function() {
		$('#pagefunction_send').find('a').click(function(event){
			var href		= this.href,
				urlParts	= href.split('/'),
				idRecord	= urlParts[urlParts.length-2],
				dialog;

			dialog = getLightbox('Record', 'Email', idRecord, '', this.title, 'Record', 'Email', idRecord); //, null, this.title, controller, 'Save', idRecord);

			dialog.dialog({
				height: 450
			});

			return false;
		});
	}

};