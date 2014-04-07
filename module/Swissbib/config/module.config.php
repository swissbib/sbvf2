<?php
namespace Swissbib\Module\Config;

use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

use Swissbib\TargetsProxy\TargetsProxy;
use Swissbib\TargetsProxy\IpMatcher;
use Swissbib\TargetsProxy\UrlMatcher;
use Swissbib\Theme\Theme;
use Swissbib\Libadmin\Importer as LibadminImporter;
use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;
use Swissbib\View\Helper\InstitutionSorter;
use Swissbib\Tab40Import\Importer as Tab40Importer;
use Swissbib\RecordDriver\Helper\LocationMap;
use Swissbib\RecordDriver\Missing as RecordDriverMissing;
use Swissbib\RecordDriver\Summon;
use Swissbib\RecordDriver\WorldCat;
use Swissbib\RecordDriver\Helper\EbooksOnDemand;
use Swissbib\RecordDriver\Helper\Availability;
use Swissbib\Helper\BibCode;
use Swissbib\Favorites\DataSource as FavoritesDataSource;
use Swissbib\Favorites\Manager as FavoritesManager;
use Swissbib\Favorites\Manager;
use Swissbib\View\Helper\ExtractFavoriteInstitutionsForHoldings;
use Swissbib\View\Helper\IsFavoriteInstitution;
use Swissbib\VuFind\Search\Helper\ExtendedSolrFactoryHelper;
use Swissbib\VuFind\Search\Helper\TypeLabelMappingHelper;
use Swissbib\View\Helper\QrCode as QrCodeViewHelper;
use Swissbib\Highlight\SolrConfigurator as HighlightSolrConfigurator;
use Swissbib\VuFind\Hierarchy\TreeDataSource\Solr as TreeDataSourceSolr;
use Swissbib\Log\Logger as SwissbibLogger;
use Swissbib\View\Helper\DomainURL;
use Swissbib\View\Helper\InstitutionDefinedAsFavorite as DefinedFavoriteInstitutions;
use Swissbib\RecordDriver\SolrDefaultAdapter;
use Swissbib\View\Helper\RedirectProtocolWrapper as ViewHelperRedirectProtocolWrapper;
use Swissbib\Services\RedirectProtocolWrapper as ServiceRedirectProtocolWrapper;

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

            //I had a problem on the developemnt branch -> a trailing backslash was genereated
            //this doesn't happen so far in feature/shibboleth
            //'/MyResearch/Home/' => array(
            //    'type'    => 'Zend\Mvc\Router\Http\Literal',
            //    'options' => array(
            //        'route'    => '/' . 'MyResearch/Home/',
            //        'constraints' => array(
            //            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
            //            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            //        ),
            //        'defaults' => array(
            //            'controller' => 'MyResearch',
            //            'action'     => 'Home',
            //        )
            //    )
            //),


            'shibboleth-test' => array( // make first shibboleth test
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/Shibboleth.sso/SAML2/POST',
                    'defaults' => array(
                        'controller' => 'shibtest',
                        'action'     => 'shib'
                    )
                )
            )

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
            'tag'                  => 'Swissbib\Controller\TagController',



        ),
        'factories' => array(
            'record' => function ($sm) {
                    return new \Swissbib\Controller\RecordController(
                        $sm->getServiceLocator()->get('VuFind\Config')->get('config')
                    );
                }
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'VuFindTheme\ResourceContainer'       => 'Swissbib\VuFind\ResourceContainer',
            'Swissbib\RecordDriverHoldingsHelper' => 'Swissbib\RecordDriver\Helper\Holdings',
            'Swissbib\QRCode'                     => 'Swissbib\CRCode\QrCodeService',
            'MarcFormatter'                     => 'Swissbib\XSLT\MARCFormatter'


        ),
        'factories' => array(
            'Swissbib\HoldingsHelper'                  => function ($sm) {
                    $ilsConnection = $sm->get('VuFind\ILSConnection');
                    $hmac = $sm->get('VuFind\HMAC');
                    $authManager = $sm->get('VuFind\AuthManager');
                    $config = $sm->get('VuFind\Config');
                    $translator = $sm->get('VuFind\Translator');
                    $locationMap = $sm->get('Swissbib\LocationMap');
                    $eBooksOnDemand = $sm->get('Swissbib\EbooksOnDemand');
                    $availability = $sm->get('Swissbib\Availability');
                    $bibCodeHelper = $sm->get('Swissbib\BibCodeHelper');
                    $logger = $sm->get('Swissbib\Logger');

                    return new HoldingsHelper($ilsConnection,
                        $hmac,
                        $authManager,
                        $config,
                        $translator,
                        $locationMap,
                        $eBooksOnDemand,
                        $availability,
                        $bibCodeHelper,
                        $logger
                    );
                },

            'Swissbib\Services\RedirectProtocolWrapper' => function ($sm) {
                    $config = $sm->get('VuFind\Config')->get('config');

                    return new ServiceRedirectProtocolWrapper($config);
                },

            'Swissbib\TargetsProxy\TargetsProxy'       => function ($sm) {
                    $config = $sm->get('VuFind\Config')->get('TargetsProxy');

                    return new TargetsProxy($config, $sm->get('Swissbib\Logger'), $sm->get('Request'));
                },
            'Swissbib\TargetsProxy\IpMatcher'          => function ($sm) {
                    return new IpMatcher();
                },
            'Swissbib\TargetsProxy\UrlMatcher'         => function ($sm) {
                    return new UrlMatcher();
                },
            'Swissbib\Theme\Theme'                     => function () {
                    return new Theme();
                },
            'Swissbib\Libadmin\Importer'               => function ($sm) {
                    $config = $sm->get('VuFind\Config')->get('config')->Libadmin;
                    $languageCache = $sm->get('VuFind\CacheManager')->getCache('language');

                    return new LibadminImporter($config, $languageCache);
                },
            'Swissbib\Tab40Importer'                   => function ($sm) {
                    $config = $sm->get('VuFind\Config')->get('config')->tab40import;

                    return new Tab40Importer($config);
                },
            'Swissbib\LocationMap'                     => function ($sm) {
                    $locationMapConfig = $sm->get('VuFind\Config')->get('config')->locationMap;

                    return new LocationMap($locationMapConfig);
                },
            'Swissbib\EbooksOnDemand'                  => function ($sm) {
                    $eBooksOnDemandConfig = $sm->get('VuFind\Config')->get('config')->eBooksOnDemand;
                    $translator = $sm->get('VuFind\Translator');

                    return new EbooksOnDemand($eBooksOnDemandConfig, $translator);
                },
            'Swissbib\Availability'                    => function ($sm) {
                    $bibCodeHelper = $sm->get('Swissbib\BibCodeHelper');
                    $availabilityConfig = $sm->get('VuFind\Config')->get('config')->Availability;

                    return new Availability($bibCodeHelper, $availabilityConfig);
                },
            'Swissbib\BibCodeHelper'                   => function ($sm) {
                    $alephNetworkConfig = $sm->get('VuFind\Config')->get('Holdings')->AlephNetworks;

                    return new BibCode($alephNetworkConfig);
                },
            'Swissbib\FavoriteInstitutions\DataSource' => function ($sm) {
                    $objectCache = $sm->get('VuFind\CacheManager')->getCache('object');
                    $configManager = $sm->get('VuFind\Config');

                    return new FavoritesDataSource($objectCache, $configManager);
                },
            'Swissbib\FavoriteInstitutions\Manager'    => function ($sm) {
                    $sessionStorage = $sm->get('VuFind\SessionManager')->getStorage();
                    $groupMapping = $sm->get('VuFind\Config')->get('libadmin-groups')->institutions;
                    $authManager = $sm->get('VuFind\AuthManager');

                    return new FavoritesManager($sessionStorage, $groupMapping, $authManager);
                },
            'Swissbib\ExtendedSolrFactoryHelper'       => function ($sm) {
                    $config = $sm->get('Vufind\Config')->get('config')->SwissbibSearchExtensions;
                    $extendedTargets = explode(',', $config->extendedTargets);

                    return new ExtendedSolrFactoryHelper($extendedTargets);
                },
            'Swissbib\TypeLabelMappingHelper'       => function () {
                    return new TypeLabelMappingHelper();
                },
            'Swissbib\Highlight\SolrConfigurator'      => function ($sm) {
                    $config = $sm->get('Vufind\Config')->get('config')->Highlight;
                    $eventsManager = $sm->get('SharedEventManager');
                    $memory = $sm->get('VuFind\Search\Memory');

                    return new HighlightSolrConfigurator($eventsManager, $config, $memory);
                },
            'Swissbib\Logger'                          => function ($sm) {
                    $logger = new SwissbibLogger();

                    $logger->addWriter(
                        'stream', 1, array(
                                          'stream' => 'log/swissbib.log'
                                     )
                    );

                    return $logger;
                },
            'Swissbib\RecordDriver\SolrDefaultAdapter' => function ($sm) {
                    $config = $sm->get('Vufind\Config')->get('Config');

                    return new SolrDefaultAdapter($config);
                },
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
            'institutionSorter'                      => function ($sm) {
                    /** @var Config $relationConfig */
                    $relationConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('libadmin-groups');
                    $institutionList = array();

                    if ($relationConfig->count() !== null) {
                        $institutionList = array_keys($relationConfig->institutions->toArray());
                    }

                    return new InstitutionSorter($institutionList);
                },
            'extractFavoriteInstitutionsForHoldings' => function ($sm) {
                    /** @var Manager $favoriteManager */
                    $favoriteManager = $sm->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
                    $userInstitutionCodes = $favoriteManager->getUserInstitutions();

                    return new ExtractFavoriteInstitutionsForHoldings($userInstitutionCodes);
                },

            'institutionDefinedAsFavorite'           => function ($sm) {
                    $dataSource = $sm->getServiceLocator()->get('Swissbib\FavoriteInstitutions\DataSource');

                    $tInstitutions = $dataSource->getFavoriteInstitutions();

                    return new DefinedFavoriteInstitutions($tInstitutions);
                },

            'qrCode'                                 => function ($sm) {
                    $qrCodeService = $sm->getServiceLocator()->get('Swissbib\QRCode');

                    return new QrCodeViewHelper($qrCodeService);
                },
            'isFavoriteInstitution'                  => function ($sm) {
                    /** @var Manager $favoriteManager */
                    $favoriteManager = $sm->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
                    $userInstitutionCodes = $favoriteManager->getUserInstitutions();

                    return new IsFavoriteInstitution($userInstitutionCodes);
                },
            'domainURL'                              => function ($sm) {
                    $locator = $sm->getServiceLocator();

                    return new DomainURL($locator->get('Request'));
                },
            'redirectProtocolWrapper'                              => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    return new  ViewHelperRedirectProtocolWrapper($locator->get("Swissbib\Services\RedirectProtocolWrapper"));


                }




        )
    ),
    'vufind' => array(
        'recorddriver_tabs' => array(
            'VuFind\RecordDriver\SolrMarc' => array(
                'tabs' => array(
                    'UserComments' => null
                )
            ),
            'VuFind\RecordDriver\Summon'   => array(
                'tabs' => array(
                    'UserComments' => null, // Disable user comments tab
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
                    'shibboleth' => 'Swissbib\VuFind\Auth\Shibboleth',
                ),
            ),
            'autocomplete' => array(
                'factories' => array(
                    'solr' => function ($sm) {
                            return new \Swissbib\VuFind\Autocomplete\Solr (
                                $sm->getServiceLocator()->get('VuFind\SearchResultsPluginManager')
                            );
                        }
                )
            ),


            'recorddriver'             => array(
                'factories' => array(
                    'solrmarc' => function ($sm) {
                            $driver = new \Swissbib\RecordDriver\SolrMarc(
                                $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
                                null,
                                $sm->getServiceLocator()->get('VuFind\Config')->get('searches'),
                                $sm->getServiceLocator()->get("Swissbib\Services\RedirectProtocolWrapper")
                            );
                            $driver->attachILS(
                                $sm->getServiceLocator()->get('VuFind\ILSConnection'),
                                $sm->getServiceLocator()->get('VuFind\ILSHoldLogic'),
                                $sm->getServiceLocator()->get('VuFind\ILSTitleHoldLogic')
                            );

                            return $driver;
                        },
                    'summon'   => function ($sm) {
                            $baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
                            $summonConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('Summon');

                            return new Summon(
                                $baseConfig, // main config
                                $summonConfig // record config
                            );
                        },
                    'worldcat' => function ($sm) {
                            $baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
                            $worldcatConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('WorldCat');

                            return new WorldCat(
                                $baseConfig, // main config
                                $worldcatConfig // record config
                            );
                        },
                    'missing'  => function ($sm) {
                            $baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');

                            return new RecordDriverMissing($baseConfig);
                        }
                )
            ),
            'ils_driver'               => array(
                'factories' => array(
                    'aleph' => function ($sm) {
                            return new \Swissbib\VuFind\ILS\Driver\Aleph(
                                new \Swissbib\VuFind\Date\Converter(),
                                $sm->getServiceLocator()->get('VuFind\CacheManager')
                            );
                        }
                )
            ),
            'recommend' => array(
                'factories' => array(
                    'favoritefacets' => function ($sm) {
                            return new \Swissbib\VuFind\Recommend\FavoriteFacets(
                                $sm->getServiceLocator()->get('VuFind\Config')
                            );
                    }
                )

            ),
            'hierarchy_driver'         => array(
                'factories' => array(
                    'series' => function ($sm) {
                            return \VuFind\Hierarchy\Driver\Factory::get($sm->getServiceLocator(), 'HierarchySeries');
                        },
                )
            ),
            'hierarchy_treerenderer'   => array(
                'invokables' => array(
                    'jstree' => 'Swissbib\VuFind\Hierarchy\TreeRenderer\JSTree'
                )
            ),
            'hierarchy_treedatasource' => array(
                'factories' => array(
                    'solr' => function ($sm) {
                            $cacheDir = $sm->getServiceLocator()->get('VuFind\CacheManager')->getCacheDir(false);

                            return new TreeDataSourceSolr(
                                $sm->getServiceLocator()->get('VuFind\Search'),
                                rtrim($cacheDir, '/') . '/hierarchy'
                            );
                        }
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
