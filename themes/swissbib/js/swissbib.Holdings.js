swissbib.Holdings = {


	/**
	 * Initialize for record
	 *
	 * @param	{Number}	idRecord
	 */
	initRecord: function(idRecord) {
		this.getInstitutionHeaders().click($.proxy(this.onInstitutionClick, this, idRecord));
	},



	/**
	 * Get all institution headers
	 *
	 * @returns	{*|jQuery|HTMLElement}
	 */
	getInstitutionHeaders: function() {
		return $('.institutionToggler');
	},



	/**
	 * Handle institution heading click
	 * Click will toggle the container, update content with ajax
	 * Existing ajax spinner will be replaced
	 *
	 * @param	{Number}	idRecord
	 * @param	{Event}		event
	 */
	onInstitutionClick: function(idRecord, event) {
		var isLoaded = !!$.data(event.target, 'loaded'),
			idParts, groupCode, institutionCode;

		if( !isLoaded ) {
			idParts 		= event.target.id.split('_');
			groupCode		= idParts[2];
			institutionCode	= idParts[3];

				// Start ajax spinner
			this.startSpinner(institutionCode);
				// Load table
			this.loadHoldingTable(idRecord, groupCode, institutionCode);
				// Mark institution as loaded
			$.data(event.target, 'loaded', true)
		}
	},



	/**
	 * Load holdings table for institution
	 *
	 * @param	{Number}	idRecord
	 * @param	{String}	groupCode
	 * @param	{String}	institutionCode
	 */
	loadHoldingTable: function(idRecord, groupCode, institutionCode) {
		var url 		= window.path + '/Holdings/' + idRecord + '/' + institutionCode,
			callback	= $.proxy(this.onHoldingTableLoaded, this, idRecord, groupCode, institutionCode),
			container	= $('#holdings-data-' + groupCode + '-' + institutionCode);

		container.load(url, '', callback);
	},



	/**
	 * Handle container loaded
	 *
	 * @param	{Number}	idRecord
	 * @param	{String}	groupCode
	 * @param	{String}	institutionCode
	 * @param	{String}	responseText
	 * @param	{String}	status
	 * @param	{Object}	response
	 */
	onHoldingTableLoaded: function(idRecord, groupCode, institutionCode, responseText, status, response) {
		if( status === 'error' ) {
			$('#holdings-data-' + groupCode + '-' + institutionCode).html('Request failed. Information is currently not available');
		}
//		console.log('Table for institution was loaded');
	},



	/**
	 * Start ajax spinner
	 *
	 * @param	{String}	institutionCode
	 */
	startSpinner: function(institutionCode) {
		$('#holdings-ajax-spinner-' + institutionCode).sprite({fps: 10, no_of_frames: 12}).spStart();
	},


	/**
	 * Open EOD link in popup
	 *
	 * @param	{String}	url
	 */
	openEODPopup: function(url) {
		window.open(url, 'eod-popup', 'dependent=yes,height=450,width=550,toolbar=no,status=no,menubar=no,location=no');
	}

};