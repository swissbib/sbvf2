<?php
return array(
    'extends' => 'root',
    'js' => array(
        'lib/jquery.js',
        'lib/jquery.debug.js',
        'lib/jquery.cookie.js',
        'lib/jquery.bgiframe.js',
        'lib/jquery.easing.js',
        'lib/jquery.hoverintent.js',
        'lib/jquery.ui.js',
        'lib/jquery.tabbed.js',
        'lib/jquery.toggler.js',
        'lib/jquery.checker.js',
        'lib/jquery.menu.js',
        'lib/jquery.dropdown.js',
        'lib/jquery.autocomplete.js',
        'lib/jquery.hint.js',
        'lib/jquery.enhancedsearch.jss',
        'lib/jquery.info.js',
        'lib/jquery.info.rollover.js',
        'swissbib.js',
        'sb.extensions.singletarget.js',
        'sb.extensions.singletarget.js',
        'lib/colorbox/jquery.colorbox.js',
    ),
    'favicon' => 'vufind-favicon.ico',
    'helpers' => array(
        'invokables' => array(
            'layoutclass' => 'VuFind\View\Helper\Blueprint\LayoutClass',
            'search' => 'VuFind\View\Helper\Blueprint\Search',
            'ScriptMarker' => 'Swissbib\View\Helper\Sbdefaultbase\ScriptMarker',
        )
    )
);