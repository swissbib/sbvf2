<?php
return array(
    'extends' => 'blueprint',
//    'extends' => 'root',
    'css' => array(
		'patches/patch_ie.css:all:IE',
		'patches/patch_ie9.css:all:IE 9',
		'patches/patch_ie8.css:all:IE 8',
		'patches/patch_ie7.css:all:IE 7',
		'patches/patch_ie6.css:all:IE 6',
		'blueprint-overrides.css'
    ),
    'js' => array(
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
		'lib/jquery.nyromodal.js',
		'lib/jquery.bgiframe.js::IE 6',

		'check_item_statuses.js',
		'check_save_statuses.js',

		'swissbib.js',
    ),
	'favicon' => 'favicon.ico'
);