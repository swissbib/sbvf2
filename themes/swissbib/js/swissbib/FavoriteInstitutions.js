swissbib.FavoriteInstitutions = {

//	sourceUrl: '/MyResearch/FavoriteInstitutions/autocomplete',

	init: function(availableInstitutions) {
//		this.sourceUrl = window.path + this.sourceUrl;

//		console.log(availableInstitutions);

		this.installAutocomplete(availableInstitutions);
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