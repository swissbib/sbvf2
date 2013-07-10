swissbib.Account = {

	/**
	 * Initialize only for account views
	 *
	 */
	init: function() {
		if( this.isInAccountView() ) {
			this.observeMenuLinks();
			this.observeForms();
		}
	},


	/**
	 * Is in account view?
	 *
	 * @returns	{Boolean}
	 */
	isInAccountView: function() {
		return $('.accountSidebar').length === 1;
	},



	/**
	 * Observe all links in menu
	 *
	 */
	observeMenuLinks: function() {
		$('.accountSidebar').find('a').click(this.addMask);
	},



	/**
	 * Observe all forms
	 *
	 */
	observeForms: function() {
		$('#content').find('form').submit(this.addMask);
	},



	/**
	 * Add loading mask over content
	 *
	 */
	addMask: function() {
		$('#content').mask('Loading...');
	}

};