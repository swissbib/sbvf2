swissbib.HoldingFavorites = {

	initRecord: function() {
		var that = this,
			favoriteInstitutionCodes = this.getFavoriteInstitutionCodes();

		$.each(favoriteInstitutionCodes, function(index, institutionCode){
			$('.miniactions-' + institutionCode).find('.institutionFavorite')
					.addClass('miniaction_favorite_remove')
					.click($.proxy(that.removeFromFavorite, that))
					.data('favorised', true);
		});

		var allMiniActions = $('#tab-holdings').find('.miniactions');
		var notFavorised = $.grep(allMiniActions, function(node, index){
			return $(node).find('.institutionFavorite').data('favorised') !== true;
		});

		$(notFavorised).find('.institutionFavorite')
				.addClass('miniaction_favorite_add')
				.click($.proxy(this.addToFavorite, this));
	},

	removeFromFavorite: function(event) {
		console.log('removeFromFavorite');

	},


	addToFavorite: function(event) {
		console.log('addToFavorite');
	},



	getFavoriteInstitutionCodes: function() {
		var favoriteTogglers = $('#holdings-favorite').find('.institutionToggler');

		return this.extractInstitutionCodes(favoriteTogglers);
	},

	extractInstitutionCodes: function(items) {
		return $.map(items, function(node, index){
			return $(node).attr('id').split('-').pop();
		});
	}

};