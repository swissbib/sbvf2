<?php

namespace Swissbib\Module\Config;

return array(
    'router' => array(
        'routes' => array(
            // ILS location, e.g. baselbern
            'accountWithLocation' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'       => '/MyResearch/:action/:location',
                    'defaults'    => array(
                        'controller' => 'my-research',
                        'action'     => 'Profile',
                        'location'   => 'baselbern'
                    ),
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'location' => '[a-z]+',
                    ),
                )
            ),
            // Search results with tab
            'search-results' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/Search/Results[/:tab]',
                    'defaults' => array(
                        'controller' => 'Search',
                        'action'     => 'results'
                    )
                )
            ),
            // (local) Search User Settings
            'myresearch-settings' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/MyResearch/Settings',
                    'defaults' => array(
                        'controller' => 'my-research',
                        'action'     => 'settings'
                    )
                )
            ),
            'help-page' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/HelpPage[/:topic]',
                    'defaults' => array(
                        'controller' => 'helppage',
                        'action'     => 'index'
                    )
                )
            ),
            'holdings-ajax' => array( // load holdings details for record with ajax
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/Holdings/:record/:institution',
                    'defaults' => array(
                        'controller' => 'holdings',
                        'action'     => 'list'
                    )
                )
            ),
            'holdings-holding-items' => array( // load holding holdings details for record with ajax
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/Holdings/:record/:institution/items/:resource',
                    'defaults' => array(
                        'controller' => 'holdings',
                        'action'     => 'holdingItems'
                    )
                )
            ),
            'myresearch-favorite-institutions' => array( // display defined favorite institutions
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/MyResearch/Favorites[/:action]',
                    'defaults' => array(
                        'controller' => 'institutionFavorites',
                        'action'     => 'display'
                    )
                )
            ),
            'myresearch-favorites' => array( // Override vufind favorites route. Rename to Lists
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/MyResearch/Lists',
                    'defaults' => array(
                        'controller' => 'my-research',
                        'action'     => 'favorites'
                    )
                )
            ),
        )
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'libadmin-sync' => array(
                    'options' => array(
                        'route'    => 'libadmin sync [--verbose|-v] [--dry|-d] [--result|-r]',
                        'defaults' => array(
                            'controller' => 'libadminsync',
                            'action'     => 'sync'
                        )
                    )
                ),
                'libadmin-sync-mapportal' => array(
                    'options' => array(
                        'route'    => 'libadmin syncMapPortal [--verbose|-v] [--result|-r] [<path>] ',
                        'defaults' => array(
                            'controller' => 'libadminsync',
                            'action'     => 'syncMapPortal'
                        )
                    )
                ),
                'tab40-import' => array( // Importer for aleph tab40 files
                    'options' => array(
                        'route'    => 'tab40import <network> <locale> <source>',
                        'defaults' => array(
                            'controller' => 'tab40import',
                            'action'     => 'import'
                        )
                    )
                ),
                'hierarchy' => array(
                    'options' => array(
                        'route'    => 'hierarchy [<limit>] [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'hierarchycache',
                            'action'     => 'buildCache'
                        )
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'helppage'             => 'Swissbib\Controller\HelpPageController',
            'libadminsync'         => 'Swissbib\Controller\LibadminSyncController',
            'my-research'          => 'Swissbib\Controller\MyResearchController',
            'search'               => 'Swissbib\Controller\SearchController',
            'summon'               => 'Swissbib\Controller\SummonController',
            'holdings'             => 'Swissbib\Controller\HoldingsController',
            'tab40import'          => 'Swissbib\Controller\Tab40ImportController',
            'institutionFavorites' => 'Swissbib\Controller\FavoritesController',
            'hierarchycache'       => 'Swissbib\Controller\HierarchyCacheController',
            'cart'                 => 'Swissbib\Controller\CartController',
            'shibtest'             => 'Swissbib\Controller\ShibtestController',
            'ajax'                 => 'Swissbib\Controller\AjaxController',


        ),
        'factories' => array(
            'record' => 'Swissbib\Controller\Factory::getRecordController',

        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'VuFindTheme\ResourceContainer'       => 'Swissbib\VuFind\ResourceContainer',
            //'Swissbib\RecordDriverHoldingsHelper' => 'Swissbib\RecordDriver\Helper\Holdings',
            'Swissbib\QRCode'                     => 'Swissbib\CRCode\QrCodeService',
            'MarcFormatter'                     => 'Swissbib\XSLT\MARCFormatter'


        ),
        'factories' => array(
            'Swissbib\HoldingsHelper'                       =>  'Swissbib\RecordDriver\Helper\Factory::getHoldingsHelper',
            'Swissbib\Services\RedirectProtocolWrapper'     =>  'Swissbib\Services\Factory::getProtocolWrapper',
            'Swissbib\TargetsProxy\TargetsProxy'            =>  'Swissbib\TargetsProxy\Factory::getTargetsProxy',
            'Swissbib\TargetsProxy\IpMatcher'               =>  'Swissbib\TargetsProxy\Factory::getIpMatcher',
            'Swissbib\TargetsProxy\UrlMatcher'              =>  'Swissbib\TargetsProxy\Factory::getURLMatcher',

            'Swissbib\Theme\Theme'                          =>  'Swissbib\Services\Factory::getThemeConfigs',
            'Swissbib\Libadmin\Importer'                    =>  'Swissbib\Libadmin\Factory::getLibadminImporter',
            'Swissbib\Tab40Importer'                        =>  'Swissbib\Tab40Import\Factory::getTab40Importer',
            'Swissbib\LocationMap'                          =>  'Swissbib\RecordDriver\Helper\Factory::getLocationMap',
            'Swissbib\EbooksOnDemand'                       =>  'Swissbib\RecordDriver\Helper\Factory::getEbooksOnDemand',
            'Swissbib\Availability'                         =>  'Swissbib\RecordDriver\Helper\Factory::getAvailabiltyHelper',
            'Swissbib\BibCodeHelper'                        =>  'Swissbib\RecordDriver\Helper\Factory::getBibCodeHelper',

            'Swissbib\FavoriteInstitutions\DataSource'      =>  'Swissbib\Favorites\Factory::getFavoritesDataSource',
            'Swissbib\FavoriteInstitutions\Manager'         =>   'Swissbib\Favorites\Factory::getFavoritesManager',
            'Swissbib\ExtendedSolrFactoryHelper'            =>  'Swissbib\VuFind\Search\Helper\Factory::getExtendedSolrFactoryHelper',
            'Swissbib\TypeLabelMappingHelper'               =>  'Swissbib\VuFind\Search\Helper\Factory::getTypeLabelMappingHelper',

            'Swissbib\Highlight\SolrConfigurator'           =>  'Swissbib\Services\Factory::getSOLRHighlightingConfigurator',
            'Swissbib\Logger'                               =>  'Swissbib\Services\Factory::getSwissbibLogger',
            'Swissbib\RecordDriver\SolrDefaultAdapter'      =>  'Swissbib\RecordDriver\Factory::getSolrDefaultAdapter',
            'VuFind\Translator'                             =>  'Swissbib\Services\Factory::getTranslator',

            'Swissbib\Hierarchy\SimpleTreeGenerator'        =>  'Swissbib\Hierarchy\Factory::getSimpleTreeGenerator',
            'Swissbib\Hierarchy\MultiTreeGenerator'         =>  'Swissbib\Hierarchy\Factory::getMultiTreeGenerator'
        )
    ),
    'view_helpers'    => array(
        'invokables' => array(
            'Authors'                        => 'Swissbib\View\Helper\Authors',
            'facetItem'                      => 'Swissbib\View\Helper\FacetItem',
            'facetItemLabel'                 => 'Swissbib\View\Helper\FacetItemLabel',
            'lastSearchWord'                 => 'Swissbib\View\Helper\LastSearchWord',
            'lastTabbedSearchUri'            => 'Swissbib\View\Helper\LastTabbedSearchUri',
            'mainTitle'                      => 'Swissbib\View\Helper\MainTitle',
            'myResearchSideBar'              => 'Swissbib\View\Helper\MyResearchSideBar',
            'urlDisplay'                     => 'Swissbib\View\Helper\URLDisplay',
            'number'                         => 'Swissbib\View\Helper\Number',
            'physicalDescription'            => 'Swissbib\View\Helper\PhysicalDescriptions',
            'publicationDateMarc'            => 'Swissbib\View\Helper\YearFormatterMarc',
            'publicationDateSummon'          => 'Swissbib\View\Helper\YearFormatterSummon',
            'publicationDateWorldCat'        => 'Swissbib\View\Helper\YearFormatterWorldCat',
            'removeHighlight'                => 'Swissbib\View\Helper\RemoveHighlight',
            'subjectHeadingFormatter'        => 'Swissbib\View\Helper\SubjectHeadings',
            'SortAndPrepareFacetList'        => 'Swissbib\View\Helper\SortAndPrepareFacetList',
            'tabTemplate'                    => 'Swissbib\View\Helper\TabTemplate',
            'zendTranslate'                  => 'Zend\I18n\View\Helper\Translate',
            'getVersion'                     => 'Swissbib\View\Helper\GetVersion',
            'holdingActions'                 => 'Swissbib\View\Helper\HoldingActions',
            'availabilityInfo'               => 'Swissbib\View\Helper\AvailabilityInfo',
            'transLocation'                  => 'Swissbib\View\Helper\TranslateLocation',
            'qrCodeHolding'                  => 'Swissbib\View\Helper\QrCodeHolding',
            'holdingItemsPaging'             => 'Swissbib\View\Helper\HoldingItemsPaging',
            'filterUntranslatedInstitutions' => 'Swissbib\View\Helper\FilterUntranslatedInstitutions',
            'configAccess'                   => 'Swissbib\View\Helper\Config'


        ),
        'factories'  => array(
            'institutionSorter'                         =>  'Swissbib\View\Helper\Factory::getInstitutionSorter',
            'extractFavoriteInstitutionsForHoldings'    =>  'Swissbib\View\Helper\Factory::getFavoriteInstitutionsExtractor',
            'institutionDefinedAsFavorite'              =>  'Swissbib\View\Helper\Factory::getInstitutionsAsDefinedFavorites',
            'qrCode'                                    =>  'Swissbib\View\Helper\Factory::getQRCodeHelper',
            'isFavoriteInstitution'                     =>  'Swissbib\View\Helper\Factory::isFavoriteInstitutionHelper',
            'domainURL'                                 =>  'Swissbib\View\Helper\Factory::getDomainURLHelper',
            'redirectProtocolWrapper'                   =>  'Swissbib\View\Helper\Factory::getRedirectProtocolWrapperHelper'
        )
    ),
    'vufind' => array(
        'recorddriver_tabs' => array(
            'VuFind\RecordDriver\Summon'   => array(
                'tabs' => array(
                    'Description'  => 'articledetails',
                    'TOC'          => null, // Disable TOC tab
                )
            )
        ),
        // This section contains service manager configurations for all VuFind
        // pluggable components:
        'plugin_managers' => array(
            'search_backend'           => array(
                'factories' => array(
                    'Solr'   => 'Swissbib\VuFind\Search\Factory\SolrDefaultBackendFactory',
                    'Summon' => 'Swissbib\VuFind\Search\Factory\SummonBackendFactory',
//                  'WorldCat' => 'VuFind\Search\Factory\WorldCatBackendFactory',
                )
            ),

            'auth'                     => array(
                'invokables' => array(
                    'shibboleth'    => 'Swissbib\VuFind\Auth\Shibboleth',
                ),
            ),
            'autocomplete' => array(
                'factories' => array(
                    'solr'          =>  'Swissbib\VuFind\Autocomplete\Factory::getSolr',
                ),
            ),
            'recommend' => array(
                'factories' => array(
                    'favoritefacets' => 'Swissbib\Services\Factory::getFavoriteFacets',
                ),
            ),


            'recorddriver'             => array(
                'factories' => array(
                    'solrmarc' => 'Swissbib\RecordDriver\Factory::getSolrMarcRecordDriver',
                    'summon'   => 'Swissbib\RecordDriver\Factory::getSummonRecordDriver',
                    'worldcat' => 'Swissbib\RecordDriver\Factory::getWorldCatRecordDriver',
                    'missing'  => 'Swissbib\RecordDriver\Factory::getRecordDriverMissing',
                )
            ),
            'ils_driver'               => array(
                'factories' => array(
                    'aleph' => 'Swissbib\VuFind\ILS\Driver\Factory::getAlephDriver'
                )
            ),
            'hierarchy_driver'         => array(
                'factories' => array(
                    'series' => 'Swissbib\VuFind\Hierarchy\Factory::getHierarchyDriverSeries',
                )
            ),
            'hierarchy_treerenderer'   => array(
                'invokables' => array(
                    'jstree' => 'Swissbib\VuFind\Hierarchy\TreeRenderer\JSTree'
                )
            ),
            'hierarchy_treedatasource' =>  array(
                'factories' => array(
                    'solr' => 'Swissbib\VuFind\Hierarchy\Factory::getSolrTreeDataSource',
                )
            ),
            'recordtab'                => array(
                'invokables' => array(
                    'articledetails' => 'Swissbib\RecordTab\ArticleDetails',
                    'description'    => 'Swissbib\RecordTab\Description'
                )
            ),
        )
    ),
    //'swissbib' => array(
    //    'ignore_assets' => array(
    //        'blueprint/screen.css',
    //        'jquery-ui.css'
    //    ),

    'swissbib' => array(
        // The ignore patterns have to be valid regex!
        'ignore_css_assets' => array(
            '|blueprint/screen.css|',
            '|css/smoothness/jquery-ui\.css|'
        ),
        'ignore_js_assets'  => array(
            '|jquery\.min.js|', // jquery 1.6
            '|^jquery\.form\.js|',
            '|jquery.metadata.js|',
            '|^jquery.validate.min.js|',
            '|jquery-ui/js/jquery-ui\.js|',
            '|common\.js|',
            //has a dependency to jQuery so has to be linked after this general component
            //move it into the swissbib libs
        ),

        // This section contains service manager configurations for all Swissbib
        // pluggable components:
        'plugin_managers' => array(
            'vufind_search_options' => array(
                'abstract_factories' => array('Swissbib\VuFind\Search\Options\PluginFactory'),
            ),
            'vufind_search_params'  => array(
                'abstract_factories' => array('Swissbib\VuFind\Search\Params\PluginFactory'),
            ),
            'vufind_search_results' => array(
                'abstract_factories' => array('Swissbib\VuFind\Search\Results\PluginFactory'),
            )
        ),
        // Search result tabs
        'resultTabs' => array(
            // Active tabs for a theme
            'themes' => array(
                'swissbibmulti'  => array(
                    'swissbib',
                    'summon'
                ),
                'swissbibsingle' => array(
                    'swissbib'
                ),
                'bootstrap' => array(
                    'swissbib'
                )

            ),
            // Configuration of tabs
            'tabs'   => array(
                'swissbib' => array(
                    'searchClassId' => 'Solr', // VuFind searchClassId
                    'label'         => 'tab.swissbib', // Label
                    'type'          => 'swissbibsolr', // Key for custom templates
                    'advSearch'     => 'search-advanced'
                ),
                'summon'   => array(
                    'searchClassId' => 'Summon',
                    'label'         => 'tab.summon',
                    'type'          => 'summon',
                    'advSearch'     => 'summon-advanced'
                )
            )
        )
    )
);
