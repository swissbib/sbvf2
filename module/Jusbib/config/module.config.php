<?php
namespace Jusbib\Module\Config;

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
use Swissbib\View\Helper\QrCode as QrCodeViewHelper;
use Swissbib\Highlight\SolrConfigurator as HighlightSolrConfigurator;
use Swissbib\VuFind\Hierarchy\TreeDataSource\Solr as TreeDataSourceSolr;
use Swissbib\Log\Logger as SwissbibLogger;
use Swissbib\View\Helper\DomainURL;

return array(

    'swissbib' => array(
		// Search result tabs
		'resultTabs' => array(
				// Active tabs for a theme
			'themes' => array(
                'jusbib' => array(
                    'swissbib'
                )


			)
		)
	)
);
