<?php
return array(
    'extends' => 'blueprint',
//    'extends' => 'root',
    'css' => array(
		'patches/patch_ie.css:all:IE',
		'patches/patch_ie9.css:all:IE 9',
		'patches/patch_ie8.css:all:IE 8',
		'patches/patch_ie7.css:all:IE 7',
		//'patches/patch_ie6.css:all:IE 6', (we don't support IE6 in the future)
		'blueprint-overrides.css',
		'blueprint.css',
		'swissbib.css'
    ),
    'js' => array(

        'lib/jquery-1.9.1.min.js',
        'lib/jquery-migrate-1.1.1.js',
        'lib/ui/jquery.ui.core.js', //already included in blueprint -> we have chosen only the used components not the whole stack - how is this done by VuFind?
        //'bibtip_swissbib.js', only used in fullview lazy load for this specific code?
        //'favorites.js', probably only used on one page - lazy load?
        //'swissbib.fullview.js', only used in full view lazy load?
        'lib/ui/jquery.effects.core.js',
        'lib/ui/jquery.ui.widget.js',
        'lib/ui/jquery.ui.position.js',
        'lib/ui/jquery.ui.mouse.js',
        'lib/ui/jquery.ui.slider.js',
        'lib/ui/jquery.ui.tabs.js',
        'lib/ui/jquery.ui.autocomplete.js',
        'lib/jquery.debug.js',
        'lib/jquery.sb.validate.min.js',
        'lib/jquery.cookie.js',
        'lib/jquery.easing.js',
        'lib/jquery.hoverintent.js',
        'lib/jquery.tabbed.js',
        'lib/jquery.toggler.js',
        'lib/jquery.checker.js',
        'lib/jquery.menunav.js',
        'lib/jquery.dropdown.js',
        'lib/jquery.hint.js',
        'lib/jquery.info.js',
        'lib/jquery.info.rollover.js',
		'lib/jquery.spritely.js',	// sprite animation, e.g. for ajax spinner
        'lib/colorbox/jquery.colorbox.js', //popup dialog solution
        'lib/jquery.enhancedsearch.js',
        'commonFromBluePrint.js',
        //'blueprint/record.js',
        'autocomplete.base.js', //used for current highlighting based on java servlet, probably replaced by default VuFind mechanism
        'swissbib.ajax.js', // general AJAX helpers
        'swissbib.js',
        'swissbib.AdvancedSearch.js',
        'swissbib.Holdings.js',
        'swissbib.searchsettings.js',

        'jstorage.min.js', //used for favorites - there is still some amount of JS code inline of the page -> Todo: Refactoring in upcoming Sprints

        'autocomplete.initialize.swissbib.js',

    ),
	'favicon' => 'favicon.ico'
);