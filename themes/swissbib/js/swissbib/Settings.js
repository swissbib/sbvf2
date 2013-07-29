swissbib.Settings = {

	init: function() {
		this.observeFormChange();
	},

	observeFormChange: function() {
		$('#settings-form').find('select').change(this.onFormChange);
	},

	onFormChange: function(event) {
		console.log('changed');
		$(this).parents('form').submit();
	}
};