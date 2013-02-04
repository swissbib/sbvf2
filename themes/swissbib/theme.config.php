<?php
return array(
    'extends' => 'blueprint',
//    'extends' => 'root',
    'css' => array(
		'patches/patch_ie.css:all:IE'
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
		'swissbib.js',
		'nose_prototype.js',
//		'sb.extensions.singletarget.js',
//		'sb.extensions.singletarget.js',
//		'lib/colorbox/jquery.colorbox.js',

    ),
//    'favicon' => 'vufind-favicon.ico',
//    'helpers' => array(
//        'invokables' => array(
//            'layoutclass' => 'VuFind\View\Helper\Blueprint\LayoutClass',
//            'search' => 'VuFind\View\Helper\Blueprint\Search',
//        )
//    )
);