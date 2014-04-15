<?php
return array(
    'extends' => 'blueprint',
//    'extends' => 'root',
    'css'     => array(
        'ui-lightness/jquery-ui.css',
        'patches/patch_ie.css:all:IE',
        'patches/patch_ie9.css:all:IE 9',
        'patches/patch_ie8.css:all:IE 8',
        'patches/patch_ie7.css:all:IE 7',
        //'patches/patch_ie6.css:all:IE 6', (we don't support IE6 in the future)
        'blueprint.css',
        'swissbib.css',
        'print/print.css',
        '../js/jquery/plugin/loadmask/jquery.loadmask.css',
        'colorbox/colorbox.css'
    ),
    'js'      => array(
        'jquery/jquery-1.10.1.min.js',
//        'jquery/jquery-1.10.1.js',
        'jquery/ui/jquery-ui.min.js',
//		'jquery/ui/jquery-ui.js',

        'lib/jstorage.min.js', //used for favorites - there is still some amount of JS code inline of the page -> Todo: Refactoring in upcoming Sprints

        'jquery/plugin/jquery-migrate-1.2.1.js',
        'jquery/plugin/jquery.easing.js',
        'jquery/plugin/jquery.debug.js',
        'jquery/plugin/colorbox/jquery.colorbox.js', //popup dialog solution
        'jquery/plugin/jquery.cookie.js',
        'jquery/plugin/jquery.spritely.js', // sprite animation, e.g. for ajax spinner
        'jquery/plugin/jquery.validate.min.js',
        'jquery/plugin/jquery.hoverintent.js',
        'jquery/plugin/loadmask/jquery.loadmask.js',
        'jquery/plugin/jquery.form.min.js',

        'swissbib-jq-plugins/hint.js',
        'swissbib-jq-plugins/menunav.js',
        'swissbib-jq-plugins/info.js',
        'swissbib-jq-plugins/info.rollover.js',
        'swissbib-jq-plugins/toggler.js',
        'swissbib-jq-plugins/checker.js',
        'swissbib-jq-plugins/dropdown.js',
        'swissbib-jq-plugins/tabbed.js',
        'swissbib-jq-plugins/enhancedsearch.js',
        'swissbib-jq-plugins/extended.ui.autocomplete.js',
        '../themes/blueprint/js/jsTree/jquery.jstree.js',

        'swissbib/swissbib.js',

        'swissbib/AdvancedSearch.js',
        'swissbib/Holdings.js',
        'swissbib/HoldingFavorites.js',
        'swissbib/FavoriteInstitutions.js',
        'swissbib/Account.js',
        'swissbib/Settings.js',

//		'common.js'
        'blueprint/commonFromBluePrint.js',
        'blueprint/lightbox.js',
        'blueprint/bulk_actions.js',

        //'blueprint/record.js',
    ),
    'favicon' => 'favicon.ico',

    'helpers' => array(
        'factories' => array(
            'record'                        => 'Swissbib\View\Helper\Swissbib\Factory::getRecordHelper',
            'flashmessages'                 => 'Swissbib\View\Helper\Swissbib\Factory::getFlashMessages',
            'citation'                      => 'Swissbib\View\Helper\Swissbib\Factory::getCitation',
            'recordlink'                    => 'Swissbib\View\Helper\Swissbib\Factory::getRecordLink',
            'getextendedlastsearchlink'     => 'Swissbib\View\Helper\Swissbib\Factory::getExtendedLastSearchLink'
        )
    )
);
