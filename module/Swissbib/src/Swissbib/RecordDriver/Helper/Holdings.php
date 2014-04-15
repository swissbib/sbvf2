<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

use VuFind\Crypt\HMAC;
use VuFind\ILS\Connection as IlsConnection;
use VuFind\Auth\Manager as AuthManager;
use VuFind\Config\PluginManager as ConfigManager;

use Swissbib\VuFind\ILS\Driver\Aleph;
use Swissbib\RecordDriver\SolrMarc;
use Swissbib\RecordDriver\Helper\BibCode;
use Swissbib\Log\Logger;

/**
 * probably Holdings should be a subtype of ZF2 AbstractHelper
 *at first I need a better understanding how things are wired up in this case using means of ZF2
 */
class Holdings
{

    /** @var    IlsConnection    Receive more data from server */
    protected $ils;

    /** @var    AuthManager        Check login status and info */
    protected $authManager;

    /** @var    ConfigManager    Load configurations */
    protected $configManager;

    /** @var    \File_MARC_Record */
    protected $holdings;

    /** @var    String        Parent item */
    protected $idItem;

    /** @var    Array    HMAC keys for ILS */
    protected $hmacKeys = array();

    /**
     * @var    Array    Map of fields to named params
     */
    protected $fieldMapping = array(
        '0' => 'local_branch_expanded',
        '1' => 'location_expanded',
        '4' => 'holding_status',
        '6' => 'process_status',
        'a' => 'holding_information',
        'B' => 'network',
        'b' => 'institution',
        'C' => 'adm_code',
        'c' => 'location_code',
        'D' => 'bib_library',
        'E' => 'bibsysnumber',
        'F' => 'institution_chb',
        'j' => 'signature',
        'o' => 'staff_note',
        'p' => 'barcode',
        'q' => 'localid',
        'r' => 'sequencenumber',
        's' => 'signature2',
        'u' => 'holding_url',
        'y' => 'opac_note',
        'z' => 'public_note',
    );

    /** @var Array      Map fieldId => delimiter, fields listed here get concatenated using the delimiter */
    protected $concatenationMapping = array(
        'z' => ' '
    );

    /**
     * @var    Array[]|Boolean
     */
    protected $holdingData = false;

    /** @var    Array[]|Boolean        Holding structure without data */
    protected $holdingStructure = false;

    /**
     * @var    Array[]    List of availabilities per item and barcode
     */
    protected $availabilities = array();

    /**
     * @var    Array[]    List of network domains and libs
     */
    protected $networks = array();

    /**
     * @var    Config
     */
    protected $configHoldings;

    /**
     * @var    HMAC
     */
    protected $hmac;

    /**
     * @var    Array        Mapping from institutions to groups
     */
    protected $institution2group = array();

    /**
     * @var        Array
     */
    protected $groupSorting = array();

    /**
     * @var        Translator
     */
    protected $translator;

    /** @var  LocationMap */
    protected $locationMap;

    /** @var  EbooksOnDemand */
    protected $ebooksOnDemand;

    /** @var  Availability */
    protected $availability;
    /** @var BibCode */
    protected $bibCodeHelper;
    /** @var  Logger */
    protected $swissbibLogger;


    /**
     * Initialize helper with dependencies
     *
     * @param    IlsConnection $ilsConnection
     * @param    HMAC $hmac
     * @param    AuthManager $authManager
     * @param    ConfigManager $configManager
     * @param    Translator $translator
     * @param     LocationMap $locationMap
     * @param     BibCode $bibCodeHelper
     * @param     Logger $swissbibLogger
     * @throws    \Exception
     */
    public function __construct(
        IlsConnection $ilsConnection,
        HMAC $hmac,
        AuthManager $authManager,
        ConfigManager $configManager,
        Translator $translator,
        LocationMap $locationMap,
        EbooksOnDemand $ebooksOnDemand,
        Availability $availability,
        BibCode $bibCodeHelper,
        Logger $swissbibLogger
    )
    {
        $this->ils = $ilsConnection;
        $this->configManager = $configManager;
        $this->configHoldings = $configManager->get('Holdings');
        $this->hmac = $hmac;
        $this->authManager = $authManager;
        $this->translator = $translator;
        $this->locationMap = $locationMap;
        $this->ebooksOnDemand = $ebooksOnDemand;
        $this->availability = $availability;
        $this->bibCodeHelper = $bibCodeHelper;
        $this->swissbibLogger = $swissbibLogger;

        /** @var Config $relationConfig */
        $relationConfig = $configManager->get('libadmin-groups');

        // Just ignore missing config to prevent a crashing frontend
        if ($relationConfig->count() !== null) {
            $this->institution2group = $relationConfig->institutions->toArray();
            $this->groupSorting = $relationConfig->groups->toArray();
        } elseif (APPLICATION_ENV == 'development') {
            throw new \Exception('Missing config file libadmin-groups.ini. Run libadmin sync to solve this problem');
        }

        $holdsIlsConfig = $this->ils->checkFunction('Holds');
        $this->hmacKeys = $holdsIlsConfig['HMACKeys'];

        $this->initNetworks();
    }


    /**
     * Initialize for item
     *
     * @param    String $idItem
     * @param    String $holdingsXml
     */
    public function setData($idItem, $holdingsXml = '')
    {
        $this->idItem = $idItem;

        $this->setHoldingsContent($holdingsXml);
    }


    /**
     * Get ils driver (aleph)
     *
     * @return    Aleph
     */
    protected function getIlsDriver()
    {
        return $this->ils->getDriver();
    }


    /**
     * Get holdings data
     *
     * @param        String $institutionCode
     * @param        SolrMarc $recordDriver
     * @return    Array[]|Boolean            Contains lists for items and holdings {items=>[],holdings=>[]}
     */
    public function getHoldings(SolrMarc $recordDriver, $institutionCode, $extend = true)
    {
        if ($this->holdingData === false) {
            $this->holdingData = array();

            if ($this->hasItems()) {
                $this->holdingData['items'] = $this->getItemsData($recordDriver, $institutionCode, $extend);
            }
            if ($this->hasHoldings()) {
                $this->holdingData['holdings'] = $this->getHoldingData($recordDriver, $institutionCode, $extend);
            }
        }

        return $this->holdingData;
    }


    /**
     * Get holdings structure grouped by group and institution
     *
     * @return Array|\Array[]|bool
     */
    public function getHoldingsStructure()
    {
        if ($this->holdingStructure === false) {
            $structure949 = array();
            $structure852 = array();
            $holdingStructure = array();


            if ($this->hasItems()) {
                //$structure949 = $this->getStructuredHoldingsStructure(949);
                $holdingStructure = $this->getStructuredHoldingsStructure(949, $holdingStructure);
            }
            if ($this->hasHoldings()) {
                //$structure852 = $this->getStructuredHoldingsStructure(852);
                $holdingStructure = $this->getStructuredHoldingsStructure(852, $holdingStructure);
            }
            //using this method imposes problems in the context of institutions with holdings and items at the same time
            //the final data structure $this->holdingsStructure doubles entries in the array (for example label) which causes problems e.g. in conjunction
            //with the translation mechanism
            //and such datastructure is simply a mess
            //example id:299403270 (SNL)

            //$holdingStructure = array_merge_recursive($structure852, $structure949);

            $this->holdingStructure = $this->sortHoldings($holdingStructure);
        }

        return $this->holdingStructure;
    }


    /**
     * Sort holdings by group based on position in $this->groupSorting
     * Sort institutions based on position in institution2group
     *
     * @param    Array $holdings
     * @return    Array
     */
    protected function sortHoldings(array $holdings)
    {
        $sortedHoldings = array();

        // Add holdings in sorted order
        foreach ($this->groupSorting as $groupCode) {
            if (isset($holdings[$groupCode])) {
                if (sizeof($this->institution2group)) {
                    $sortedInstitutions = array();

                    foreach ($this->institution2group as $institutionCode => $groupCodeAgain) {
                        if (isset($holdings[$groupCode]['institutions'][$institutionCode])) {
                            $sortedInstitutions[$institutionCode] = $holdings[$groupCode]['institutions'][$institutionCode];
                        }
                    }
                } else {
                    // No sorting available, just use available data
                    $sortedInstitutions = $holdings[$groupCode]['institutions'];
                }


                // Add group to sorted list
                $sortedHoldings[$groupCode] = $holdings[$groupCode];
                // Add sorted institution list
                $sortedHoldings[$groupCode]['institutions'] = $sortedInstitutions;

                // Remove group
                unset($holdings[$groupCode]);
            }
        }

        // Add all the others (missing data because of misconfiguration?)
        foreach ($holdings as $groupCode => $group) {
            $sortedHoldings[$groupCode] = $group;
        }

        return $sortedHoldings;
    }


    /**
     * Merge two arrays. Extend sub arrays or add missing elements,
     * but don't extend existing scalar values (as array_merge_recursive() does)
     *
     * @param    Array $resultData
     * @param    Array $newData
     * @return    Array
     */
    protected function mergeHoldings(array $resultData, array $newData)
    {
        foreach ($newData as $newKey => $newValue) {
            if (!isset($resultData[$newKey])) {
                $resultData[$newKey] = $newValue;
            } elseif (is_array($resultData[$newKey])) {
                $resultData[$newKey] = $this->mergeHoldings($resultData[$newKey], $newValue);
            }
            // else = Already existing scalar value => ignore (keep first items data)
        }

        return $resultData;
    }


    /**
     * Initialize networks from config
     *
     */
    protected function initNetworks()
    {
        $networkNames = array('Aleph', 'Virtua');

        foreach ($networkNames as $networkName) {
            $configName = ucfirst($networkName) . 'Networks';

            /** @var Config $networkConfigs */
            $networkConfigs = $this->configHoldings->get($configName);

            foreach ($networkConfigs as $networkCode => $networkConfig) {
                list($domain, $library) = explode(',', $networkConfig, 2);

                $this->networks[$networkCode] = array(
                    'domain' => $domain,
                    'library' => $library,
                    'type' => $networkName
                );
            }
        }
    }


    /**
     * Set holdings structure
     *
     * @param    String $holdingsXml
     * @throws    \File_MARC_Exception
     */
    protected function setHoldingsContent($holdingsXml)
    {
        if (is_string($holdingsXml) && strlen($holdingsXml) > 30) {
            $holdingsMarcXml = new \File_MARCXML($holdingsXml, \File_MARCXML::SOURCE_STRING);
            $marcData = $holdingsMarcXml->next();

            if (!$marcData) {
                throw new \File_MARC_Exception('Cannot Process Holdings Structure');
            }

            $this->holdings = $marcData;
        } else {
            // Invalid input data. Currently just ignore it
            $this->holdings = false;
            $this->holdingData = array();
        }
    }


    /**
     * Get holding items for an institution
     *
     * @param    SolrMarc $recordDriver
     * @param    String $institutionCode
     * @param    Boolean $extend
     * @return    Array        Institution Items
     */
    protected function getItemsData(SolrMarc $recordDriver, $institutionCode, $extend = true)
    {
        $fieldName = 949; // Field code for item information in holdings xml
        $institutionItems = $this->getHoldingsData($fieldName, $this->fieldMapping, $institutionCode);

        if ($extend) {
            foreach ($institutionItems as $index => $item) {
                // Add extra information for item
                $institutionItems[$index] = $this->extendItem($item, $recordDriver);
            }
        }

        return $institutionItems;
    }


    /**
     * Check whether network is supported
     *
     * @param    String $networkCode
     * @return    Boolean
     */
    protected function isRestfulNetwork($networkCode)
    {
        return isset($this->configHoldings->Restful->{$networkCode});
    }


    /**
     * Extend item with additional informations
     *
     * @param    Array $item
     * @param    SolrMarc $recordDriver
     * @param    Array $extendingOptions
     * @return    Array
     */
    public function extendItem(array $item, SolrMarc $recordDriver, array $extendingOptions = array())
    {
        $item = $this->extendItemBasic($item, $recordDriver, $extendingOptions);
        $item = $this->extendItemIlsActions($item, $recordDriver, $extendingOptions);

        return $item;
    }


    /**
     * Extend item with basic infos
     * - Ebooks on Demand Link
     * - Location map
     *
     * @param    Array $item
     * @param    SolrMarc $recordDriver
     * @param    Array $extendingOptions
     * @return    Array
     */
    protected function extendItemBasic(array $item, SolrMarc $recordDriver = null, array $extendingOptions = array())
    {
        // EOD LINK
        if (!isset($extendingOptions['eod']) || $extendingOptions['eod']) {
            $item['eodlink'] = $this->getEODLink($item, $recordDriver);
        }

        // Location Map Link
        if (!isset($extendingOptions['map']) || $extendingOptions['map']) {
            $item['locationMap'] = $this->getLocationMapLink($item);
        }

        // Location label
        if (!isset($extendingOptions['location']) || $extendingOptions['location']) {
            $item['locationLabel'] = $this->getLocationLabel($item);
        }
        // Defaults to false, maybe ils will add more info
        $item['availability'] = false;

        if (!isset($extendingOptions['backlink']) || $extendingOptions['backlink']) {
            // @todo dies ist ein dreckiger Hack, um die Exemplardarstellung für E-Books aus dem ERM korrekt zu halten
            // sollte auf Ebene der Daten gelöst werden
            if (!empty($item['holding_url']) && $item['network'] === 'IDSBB') {
                $item['network'] = 'SSERM';
            }
            if (isset($item['network']) && !$this->isRestfulNetwork($item['network'])) {
                $item['backlink'] = $this->getBackLink($item['network'], strtoupper($item['institution']), $item);
            }
        }

        if (!isset($extendingOptions['institutionUrl']) || $extendingOptions['institutionUrl']) {
            $bibInfoLink = $this->getBibInfoLink($item['institution']);
            $item['institutionUrl'] = $bibInfoLink['url'];
        }

        return $item;
    }


    /**
     * Extend item with action links based on ILS
     *
     * @param    Array $item
     * @param    SolrMarc $recordDriver
     * @param    Array $extendingOptions
     * @return  Array
     */
    protected function extendItemIlsActions(array $item, SolrMarc $recordDriver = null, array $extendingOptions = array())
    {
        $networkCode = isset($item['network']) ? $item['network'] : '';

        // Only add links for supported networks
        if ($this->isAlephNetwork($networkCode)) {
            if ($this->isRestfulNetwork($networkCode)) {
                // Add hold link for item
                if (!isset($extendingOptions['hold']) || $extendingOptions['hold']) {
                    $item['holdLink'] = $this->getHoldLink($item);
                }

                if (!isset($extendingOptions['actions']) || $extendingOptions['actions']) {
                    if ($this->isLoggedIn()) {
                        $item['userActions'] = $this->getAllowedUserActions($item);
                    } // if a user is not logged in
                    elseif (!$this->isLoggedIn()) {
                        $item['userActions'] = array(
                            'login' => 'true',
                        );
                    }
                }
            }

            // Add availability
            if (!isset($extendingOptions['availability']) || $extendingOptions['availability']) {
                $item['availability'] = $this->getAvailabilityInfos($item['bibsysnumber'], $item['barcode'], $item['bib_library']);
            }
        }

        return $item;
    }


    /**
     * Extend holding with additional informations
     *
     * @param    Array $holding
     * @param    SolrMarc $recordDriver
     * @return    Array
     */
    protected function extendHolding(array $holding, SolrMarc $recordDriver = null)
    {
        $holding = $this->extendHoldingBasic($holding, $recordDriver);
        $holding = $this->extendHoldingIlsActions($holding, $recordDriver);

        return $holding;
    }


    /**
     *  Extend holding with basic infos
     * - Location map
     *
     * @param    Array $holding
     * @param    SolrMarc $recordDriver
     * @return    Array
     * @todo    Enable restful check after full features are implemented for holdings
     */
    protected function extendHoldingBasic(array $holding, SolrMarc $recordDriver = null)
    {
        // Location Map Link
        $holding['locationMap'] = $this->getLocationMapLink($holding);
        // Location label
        $holding['locationLabel'] = $this->getLocationLabel($holding);

        // Add backlink for not restful networks
        // @note Disabled check until the
//        if (!$this->isRestfulNetwork($holding['network'])) {
        $holding['backlink'] = $this->getBackLink($holding['network'], strtoupper($holding['institution']), $holding);
//        }

        $bibInfoLink = $this->getBibInfoLink($holding['institution']);
        $holding['institutionUrl'] = $bibInfoLink['url'];

        return $holding;
    }


    /**
     * Add action links to holding item
     *
     * @param    Array $holding
     * @param    SolrMarc $recordDriver
     * @return    Array
     */
    protected function extendHoldingIlsActions(array $holding, SolrMarc $recordDriver = null)
    {
        $networkCode = $holding['network'];

        if ($this->isAlephNetwork($networkCode)) {
            if ($this->isRestfulNetwork($networkCode)) {
                // no backlink for Restful enabled networks, either actions are possible, or nothing
                unset($holding['backlink']);
                $bib = $holding['bib_library'];
                $resourceId = $bib . $holding['bibsysnumber'];
                $itemsCount = $this->getIlsDriver()->getHoldingItemCount($resourceId, $holding['institution']);

                $holding['itemsLink'] = array(
                    'count' => $itemsCount,
                    'resource' => $resourceId,
                    'institution' => $holding['institution'],
                    'url' => array(
                        'record' => $this->idItem,
                        'institution' => $holding['institution'],
                        'resource' => $resourceId
                    )
                );
            }
        }

        return $holding;
    }


    /**
     * Build an EOD link if possible
     * Return false if item does not support EOD links
     *
     * @param    Array $item
     * @param    SolrMarc $recordDriver
     * @return    String|Boolean
     */
    protected function getEODLink(array $item, SolrMarc $recordDriver = null)
    {
        return $this->ebooksOnDemand ? $this->ebooksOnDemand->getEbooksOnDemandLink($item, $recordDriver, $this) : false;
    }

    /**
     * Get back link for IDSSG (self-developed-non-aleph-request)
     * Currently only a wrapper for Aleph
     *
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @param    Array $data
     * @return    String
     */
    protected function getBackLinkIDSSG($networkCode, $institutionCode, array $item, array $data)
    {
        return $this->getBackLinkAleph($networkCode, $institutionCode, $item, $data);
    }

    /**
     * Build location map link
     * Return false in case institution is not enable for mapping
     *
     * @param    Array $item
     * @return    String|Boolean
     */
    protected function getLocationMapLink(array $item)
    {
        return $this->locationMap ? $this->locationMap->getLinkForItem($this, $item) : false;
    }


    /**
     * Get location label
     * Try to translate. Fallback to index data
     *
     * @param    Array $item
     * @return    String
     */
    protected function getLocationLabel(array $item)
    {
        $label = '';

        // Has informations with translation?
        if (isset($item['location_code']) && isset($item['institution_chb']) && isset($item['network'])) {
            // @todo keep strtolower or fix in tab40.sync
            $labelKey = strtolower($item['institution_chb'] . '_' . $item['location_code']);
            $textDomain = 'location-' . strtolower($item['network']);
            $translated = $this->translator->translate($labelKey, $textDomain);

            if ($translated !== $labelKey) {
                $label = $translated;
            }
        }

        // Use expanded label or code as fallback
        if (empty($label)) {
            if (isset($item['location_expanded'])) {
                $label = trim($item['location_expanded']);
            } elseif (isset($item['location_code'])) {
                $label = trim($item['location_code']);
            }
        }

        return $label;
    }


    /**
     * Get list of allowed actions for the current user
     *
     * @param    Array $item
     * @return    Array
     */
    protected function getAllowedUserActions($item)
    {
        /** @var Aleph $ilsDriver */
        $ilsDriver = $this->ils->getDriver();
        $patron = $this->getPatron();

        $itemId = $item['bibsysnumber'] . $item['sequencenumber'];
        $bib = $item['bib_library'];
        $groupId = $this->buildItemId($item);

        $allowedActions = $ilsDriver->getAllowedActionsForItem($patron['id'], $itemId, $groupId, $bib);
        $host = $ilsDriver->host; // @todo make dev port dynamic

        if ($allowedActions['photocopyRequest']) {
            $allowedActions['photocopyRequestLink'] = $this->getPhotoCopyRequestLink($host, $item);
        }

        if ($allowedActions['bookingRequest']) {
            $allowedActions['bookingRequestLink'] = $this->getBookingRequestLink($host, $item);
        }

        return $allowedActions;
    }


    /**
     * Get link for external photocopy request
     *
     * @param    String $host
     * @param    Array $item
     * @return    String
     */
    protected function getPhotoCopyRequestLink($host, array $item)
    {
        $queryParams = array(
            'func' => 'item-photo-request',
            'doc_library' => $item['adm_code'],
            'adm_doc_number' => $item['localid'],
            'item_sequence' => $item['sequencenumber'],
            'bib_doc_num' => $item['bibsysnumber'],
            'bib_library' => $item['bib_library'],
        );

        return 'http://' . $host . '/F/?' . http_build_query($queryParams);
    }

    /**
     * Get link for external booking request
     *
     * @param       $host
     * @param array $item
     *
     * @return string
     */

    protected function getBookingRequestLink($host, array $item)
    {
        $queryParams = array(
            'func' => 'booking-req-form-itm',
            'adm_library' => $item['adm_code'],
            'adm_doc_number' => $item['localid'],
            'adm_item_sequence' => $item['sequencenumber'],
        );

        return 'http://' . $host . '/F/?' . http_build_query($queryParams);
    }


    /**
     * Check whether user is logged in
     *
     * @return    Boolean
     */
    protected function isLoggedIn()
    {
        return $this->authManager->isLoggedIn() !== false;
    }


    /**
     * Get patron (catalog login data)
     *
     * @return    Array
     */
    protected function getPatron()
    {
        return $this->authManager->storedCatalogLogin();
    }


    /**
     * Check whether network uses aleph system
     *
     * @param    String $network
     * @return    Boolean
     */
    protected function isAlephNetwork($network)
    {
        return isset($this->networks[$network]) ? $this->networks[$network]['type'] === 'Aleph' : false;
    }


    /**
     * Get a back link
     * Check first if a custom type is defined for this network
     * Fallback to network default
     *
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @return    Boolean
     */
    protected function getBackLink($networkCode, $institutionCode, array $item)
    {
        //item has url (subfield u)
        if (!empty($item['holding_url'])) return $item['holding_url'];

        $method = false;
        $data = array();

        if (isset($this->configHoldings->Backlink->{$networkCode})) { // Has the network its own backlink type
            $method = 'getBackLink' . ucfirst($networkCode);
            $data = array(
                'pattern' => $this->configHoldings->Backlink->{$networkCode}
            );
        } else { // no custom type
            if (isset($this->networks[$networkCode])) { // is network even configured?
                $networkType = strtoupper($this->networks[$networkCode]['type']);
                $method = 'getBackLink' . ucfirst($networkType);

                // Has the network type (aleph, virtua, etc) a general link?
                if (isset($this->configHoldings->Backlink->$networkType)) {
                    $data = array(
                        'pattern' => $this->configHoldings->Backlink->$networkType
                    );
                }
            }
        }

        // Merge in network data if available
        if (isset($this->networks[$networkCode])) {
            $data = array_merge($this->networks[$networkCode], $data);
        }

        // Is a matching method available?
        if ($method && method_exists($this, $method)) {
            return $this->{$method}($networkCode, $institutionCode, $item, $data);
        }

        return false;
    }


    /**
     * Get back link for aleph
     *
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @param    Array $data
     * @return    String
     */
    protected function getBackLinkAleph($networkCode, $institutionCode, $item, array $data)
    {
        $values = array(
            'server' => $data['domain'],
            'bib-library-code' => $data['library'],
            'bib-system-number' => $item['bibsysnumber'],
            'aleph-sublibrary-code' => $institutionCode
        );

        return $this->compileString($data['pattern'], $values);
    }


    /**
     * Get back link for virtua
     *
     * @todo    Get user language
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @param    Array $data
     * @return    String
     */
    protected function getBackLinkVirtua($networkCode, $institutionCode, $item, array $data)
    {
        $values = array(
            'server' => $data['domain'],
            'language-code' => 'de', // @todo fetch from user
            'bib-system-number' => $this->getNumericString($item['bibsysnumber']) // remove characters from number string
        );

        return $this->compileString($data['pattern'], $values);
    }


    /**
     * Get back link for alexandria
     * Currently only a wrapper for virtua
     *
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @param    Array $data
     * @return    String
     */
    protected function getBackLinkAlex($networkCode, $institutionCode, array $item, array $data)
    {
        return $this->getBackLinkVirtua($networkCode, $institutionCode, $item, $data);
    }


    /**
     * Get back link for SNL
     * Currently only a wrapper for virtua
     *
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @param    Array $data
     * @return    String
     */
    protected function getBackLinkSNL($networkCode, $institutionCode, $item, array $data)
    {
        return $this->getBackLinkVirtua($networkCode, $institutionCode, $item, $data);
    }

    /**
     * Get back link for CCSA (poster collection)
     * Currently only a wrapper for virtua
     *
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @param    Array $data
     * @return    String
     */

    protected function getBackLinkCCSA($networkCode, $institutionCode, $item, array $data)
    {
        return $this->getBackLinkVirtua($networkCode, $institutionCode, $item, $data);
    }

    /**
     * Build rero backlink
     *
     * @param       $networkCode
     * @param       $institutionCode
     * @param       $item
     * @param array $data
     * @return mixed
     */
    protected function getBackLinkRERO($networkCode, $institutionCode, $item, array $data)
    {
        $values = array(
            'server' => $data['domain'],
            'language-code' => 'de', // @todo fetch from user,
            'RERO-network-code' => (int)substr($institutionCode, 2, 2), // third and fourth character
            'bib-system-number' => $item['bibsysnumber'], // replaces the incorrect version: 'bib-system-number' => $this->getNumericString($item['bibsysnumber']), // remove characters from number string
            'sub-library-code' => $this->getNumericString($institutionCode) //removes the RE-characters from the number string
        );

        return $this->compileString($data['pattern'], $values);
    }

    /**
     * Get back link for helvetic archives
     * Currently only a wrapper for virtua
     *
     * @param    String $networkCode
     * @param    String $institutionCode
     * @param    Array $item
     * @param    Array $data
     * @return    String
     */
    protected function getBackLinkCHARCH($networkCode, $institutionCode, array $item, array $data)
    {
        return $this->getBackLinkVirtua($networkCode, $institutionCode, $item, $data);
    }

    /**
     * Compile string. Replace {varName} pattern with names and data from array
     *
     * @param    String $string
     * @param    Array $keyValues
     * @return    String
     */
    protected function compileString($string, array $keyValues)
    {
        $newKeyValues = array();

        foreach ($keyValues as $key => $value) {
            $newKeyValues['{' . $key . '}'] = $value;
        }

        return str_replace(array_keys($newKeyValues), array_values($newKeyValues), $string);
    }


    /**
     * Remove all not-numeric parts from string
     *
     * @param    String $string
     * @return    String
     */
    protected function getNumericString($string)
    {
        return preg_replace('[\D]', '', $string);
    }


    /**
     * Get bib info link
     * Get false if not found
     * Array contains url and host value
     *
     * @param    String $institutionCode
     * @return    Array|Boolean
     */
    protected function getBibInfoLink($institutionCode)
    {
        $bibInfoLink = $this->translator->translate($institutionCode, 'bibinfo');

        if ($bibInfoLink === $institutionCode) {
            $bibInfoLink = false;
        } else {
            $url = parse_url($bibInfoLink);
            $bibInfoLink = array(
                'url' => $bibInfoLink,
                'host' => $url['host']
            );
        }

        return $bibInfoLink;
    }


    /**
     * Get availability infos for item element
     *
     * @param    String $sysNumber
     * @param    String $barcode
     * @param     String $network
     * @return    Array|Boolean
     */
    protected function getAvailabilityInfos($sysNumber, $barcode, $bib)
    {
        $userLocale = $this->translator->getLocale();

        return $this->availability->getAvailability($sysNumber, $barcode, $bib, $userLocale);
    }


    /**
     * Get circulation statuses for all elements of the item
     *
     * @param    String $sysNumber
     * @return    Array[]
     */
    protected function getItemCirculationStatuses($sysNumber)
    {
        $data = array();
        try {
            $circulationStatuses = $this->ils->getDriver()->getCirculationStatus($sysNumber);


            foreach ($circulationStatuses as $circulationStatus) {
                $data[$circulationStatus['barcode']] = $circulationStatus;
            }

        } catch (\Exception $e) {
            //todo: GH get logging service
        }

        return $data;
    }


    /**
     * Get structured data for holdings
     *
     * @param    SolrMarc $recordDriver
     * @param    String $institutionCode
     * @param    Boolean $extend
     * @return    Array[]
     */
    protected function getHoldingData(SolrMarc $recordDriver, $institutionCode, $extend = true)
    {
        $fieldName = 852; // Field code for item information in holdings xml
        $institutionHoldings = $this->getHoldingsData($fieldName, $this->fieldMapping, $institutionCode);

        if ($extend) {
            foreach ($institutionHoldings as $index => $holding) {
                $institutionHoldings[$index] = $this->extendHolding($holding, $recordDriver);
            }
        }

        return $institutionHoldings;
    }


    /**
     * Check whether holding holdings are available
     *
     * @return    Boolean
     */
    protected function hasHoldings()
    {
        return $this->holdings && $this->holdings->getField(852) !== false;
    }


    /**
     * Check whether holding items are available
     *
     * @return    Boolean
     */
    protected function hasItems()
    {
        return $this->holdings && $this->holdings->getField(949) !== false;
    }


    /**
     * Get structured elements (grouped by group and institution)
     *
     * @param    String $fieldName
     * @param    Array $mapping
     * @param    String $institutionCode
     * @return    Array        Items or holdings for institution
     */
    protected function getHoldingsData($fieldName, array $mapping, $institutionCode)
    {
        $data = array();
        $fields = $this->holdings ? $this->holdings->getFields($fieldName) : false;

        if (is_array($fields)) {
            foreach ($fields as $index => $field) {
                $item = $this->extractFieldData($field, $mapping);
                $institution = $item['institution_chb'];

                if ($institution === $institutionCode) {
                    $data[] = $item;
                }
            }
        }

        return $data;
    }


    /**
     * Get holdings structure for holdings
     *
     * @param    Integer $fieldName
     * @return    Array[]
     */
    protected function getStructuredHoldingsStructure($fieldName, $data = array())
    {
        //$data    = array();
        $fields = $this->holdings ? $this->holdings->getFields($fieldName) : false;
        $mapping = array(
            'B' => 'network',
            'F' => 'institution_chb'
        );

        if (is_array($fields)) {
            foreach ($fields as $index => $field) {
                $item = $this->extractFieldData($field, $mapping);
                $networkCode = $item['network'];
                $institution = $item['institution_chb'];
                $groupCode = $this->getGroup($institution);

                // Prevent display of untranslated and ungrouped institutions
                $institutionLabel = $this->translator->translate($institution, 'institution');
                if ($groupCode == 'unknown' || $institutionLabel === $institution) {
                    if ($groupCode === 'unknown') {
                        $this->swissbibLogger->logUngroupedInstitution($institution);
                    }
                    continue;
                }

                // Make sure group is present
                if (!isset($data[$groupCode])) {
                    $data[$groupCode] = array(
                        'label' => $groupCode,
                        'networkCode' => $networkCode,
                        'institutions' => array()
                    );
                }

                // Make sure institution is present
                if (!isset($data[$groupCode]['institutions'][$institution])) {
                    $data[$groupCode]['institutions'][$institution] = array(
                        'label' => $institution,
                        'bibinfolink' => $this->getBibInfoLink($institution)
                    );
                }
            }
        }

        return $data;
    }


    /**
     * Get group code for institution based on mapping data
     *
     * @param    String $institutionCode
     * @return    String
     */
    protected function getGroup($institutionCode)
    {
        return isset($this->institution2group[$institutionCode]) ? $this->institution2group[$institutionCode] : 'unknown';
    }


    /**
     * Build itemId from item properties and the id of the item
     * ItemId is not the id of the item, it's a combination of sub fields
     *
     * @param    Array $holdingItem
     * @return    String
     * @todo    How to handle missing information. Throw exception, ignore?
     */
    protected function buildItemId(array $holdingItem)
    {
        if (isset($holdingItem['adm_code']) && isset($holdingItem['localid']) && isset($holdingItem['sequencenumber'])) {
            return $holdingItem['adm_code'] . $holdingItem['localid'] . $holdingItem['sequencenumber'];
        }

        return 'incompleteItemData';
    }


    /**
     * Get link for holding action
     *
     * @param    Array $holdingItem
     * @return    Array
     */
    protected function getHoldLink(array $holdingItem)
    {
        if (!isset($holdingItem['bibsysnumber'])) {
            return null;
        }

        $linkValues = array(
            'id' => $holdingItem['bib_library'] . '-' . $holdingItem['bibsysnumber'],
            'item_id' => $this->buildItemId($holdingItem),
        );

        return array(
            'action' => 'Hold',
            'record' => $this->idItem, //'id',
            'anchor' => '#tabnav',
            'query' => http_build_query($linkValues + array(
                    'hashKey' => $this->hmac->generate($this->hmacKeys, $linkValues)
                )),
        );
    }


    /**
     * Extract field data
     *
     * @param    \File_MARC_Data_Field $field
     * @param    Array $fieldMapping Field code=>name mapping
     * @return    Array
     */
    protected function extractFieldData(\File_MARC_Data_Field $field, array $fieldMapping)
    {
        $subFields = $field->getSubfields();
        $rawData = array();
        $data = array();

        // Fetch data
        foreach ($subFields as $code => $subdata) {
            if ($this->useConcatenation($code, $rawData)) {
                $rawData[$code] .= $this->concatenationMapping[$code] . $subdata->getData();
            } else {
                $rawData[$code] = $subdata->getData();
            }
        }

        foreach ($fieldMapping as $code => $name) {
            $data[$name] = isset($rawData[$code]) ? $rawData[$code] : '';
        }

        return $data;
    }



    /**
     * @param String    $code
     * @param Array     $rawData
     *
     * @return bool
     */
    protected function useConcatenation($code, array $rawData)
    {
        return array_key_exists($code, $rawData) && array_key_exists($code, $this->concatenationMapping);
    }


}
