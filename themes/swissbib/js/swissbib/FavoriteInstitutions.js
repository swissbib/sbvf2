swissbib.FavoriteInstitutions = {

//	sourceUrl: '/MyResearch/FavoriteInstitutions/autocomplete',

	/**
	 * Initialize favorite management
	 *
	 * @param	{Object|Boolean}	availableInstitutions		List of institutions of false of already cached
	 */
	init: function(availableInstitutions) {
//		this.sourceUrl = window.path + this.sourceUrl;
//		console.log(availableInstitutions);

			// The institutions should already be cached
		if( availableInstitutions === false ) {
			availableInstitutions = this.getInstitutionsFromStorage();
		} else {
				// New institutions downloaded, save them
			this.saveInstitutionsToStorage(availableInstitutions);
		}

		this.installAutocomplete(availableInstitutions);
	},


	getInstitutionsFromStorage: function() {
		return $.jStorage.get('favorite-institutions');
	},

	saveInstitutionsToStorage: function(institutions) {
		$.jStorage.set('favorite-institutions', institutions);
	},



	installAutocomplete: function(availableInstitutions) {
		var sourceData = [];

		$.each(availableInstitutions, function(key, value){
			sourceData.push({
				value:	key,
				label:	value
			})
		});

		$('#libraryname').autocomplete({
			source: sourceData
		});
	}

};