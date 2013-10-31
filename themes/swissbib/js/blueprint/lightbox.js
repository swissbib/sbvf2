function getLightbox(module, action, id, lookfor, message, followupModule, followupAction, followupId, postParams) {
	// Optional parameters
	if (followupModule === undefined) {followupModule = '';}
	if (followupAction === undefined) {followupAction = '';}
	if (followupId     === undefined) {followupId     = '';}

	var params = {
		method: 'getLightbox',
		lightbox: 'true',
		submodule: module,
		subaction: action,
		id: id,
		lookfor: lookfor,
		message: message,
		followupModule: followupModule,
		followupAction: followupAction,
		followupId: followupId
	};

	// create a new modal dialog
	var $dialog = $('<div id="modalDialog"><div class="dialogLoading">&nbsp;</div></div>')
		.load(path + '/AJAX/JSON?' + $.param(params), postParams)
		.dialog({
			modal: true,
			autoOpen: false,
			closeOnEscape: true,
			width: "auto",
			height: "auto",
			position: { my: "center", at: "center", of: window },
			close: function () {
				// check if the dialog was successful, if so, load the followup action
				if (__dialogHandle.processFollowup && __dialogHandle.followupModule
					&& __dialogHandle.followupAction) {
					$(this).remove();
					getLightbox(__dialogHandle.followupModule, __dialogHandle.followupAction,
						__dialogHandle.recordId, null, message, null, null, null, postParams);
				}
				$(this).remove();
			}
		});

	// save information about this dialog so we can get it later for followup processing
	__dialogHandle.dialog = $dialog;
	__dialogHandle.processFollowup = false;
	__dialogHandle.followupModule = followupModule;
	__dialogHandle.followupAction = followupAction;
	__dialogHandle.recordId = followupId == '' ? id : followupId;
	__dialogHandle.postParams = postParams;

	// done
	return $dialog.dialog('open');
}

function registerCloseButton() {
	__dialogHandle.dialog.find("input.close").click(function(event){
		event.preventDefault();
		hideLightbox();
	});
}

function repositionDialog() {
	__dialogHandle.dialog.dialog("option", "position", { my: "center", at: "center", of: window });
}

/**
 * This is called by the lightbox when it
 * finished loading the dialog content from the server
 * to register the form in the dialog for ajax submission.
 */
function lightboxDocumentReady() {
	registerAjaxLogin();
	registerAjaxCart();
	registerAjaxCartExport();
	registerAjaxSaveRecord();
	registerAjaxListEdit();
	registerAjaxEmailRecord();
	registerAjaxSMSRecord();
	registerAjaxTagRecord();
	registerAjaxEmailSearch();
	registerAjaxBulkSave();
	registerAjaxBulkEmail();
	registerAjaxBulkExport();
	registerAjaxBulkDelete();
	registerCloseButton();
	repositionDialog();
	$('.mainFocus').focus();
}