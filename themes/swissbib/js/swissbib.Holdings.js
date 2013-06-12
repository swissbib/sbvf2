swissbib.Holdings = {

	initRecord: function(idRecord) {
		this.getInstitutionHeaders().click($.proxy(this.onInstitutionClick, this, idRecord));
	},

	getInstitutionHeaders: function() {
		return $('.institutionToggler');
	},

	onInstitutionClick: function(idRecord, event) {
		var isLoaded = !!$.data(event.target, 'loaded'),
			idParts, groupCode, institutionCode;

		if( !isLoaded ) {
			idParts 		= event.target.id.split('_');
			groupCode		= idParts[2];
			institutionCode	= idParts[3];

			this.loadHoldingTable(idRecord, groupCode, institutionCode);
			$.data(event.target, 'loaded', true)
		}
	},

	loadHoldingTable: function(idRecord, groupCode, institutionCode) {
		console.log('load');

		var url	= window.path + '/Holdings/' + idRecord + '/' + institutionCode;

		$('#holdings-data-' + groupCode + '-' + institutionCode).load(url, '', $.proxy(this.onHoldingTableLoaded, this, idRecord, groupCode, institutionCode));
	},

	onHoldingTableLoaded: function(idRecord, groupCode, institutionCode, response) {
		console.log('loaded');
	}

};