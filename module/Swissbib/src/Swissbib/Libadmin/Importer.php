<?php
namespace Swissbib\Libadmin;

use Zend\Cache\Storage\StorageInterface;
use Zend\Config\Config;
use Zend\Di\ServiceLocator;
use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\Storage\Adapter\Filesystem as FileSystemCache;
use Zend\Http\Client\Adapter\Exception\RuntimeException as HttpException;

use Swissbib\Libadmin\Exception as Exceptions;
use Swissbib\Libadmin\Writer as LibadminWriter;

/**
 * Libadmin data importer
 * Fetch data from libadmin api and store in local files
 *
 */
class Importer implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @var    String        Cache directory for data file download
     */
    protected $cacheDir;

    /** @var Result */
    protected $result;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FileSystemCache
     */
    protected $languageCache;

    /**
     * @var bool
     */
    protected $downloadAllInstitutions = false;


    /**
     * @var String - overwrites default path
     */
    protected $configPath = null;



    /**
     * Initialize importer with import config and language cache
     *
     * @param Config           $config
     * @param StorageInterface $languageCache
     */
    public function __construct(Config $config, StorageInterface $languageCache)
    {
        $this->config        = $config;
        $this->languageCache = $languageCache;
        $this->cacheDir      = realpath(APPLICATION_PATH . '/data/cache');
        $this->result        = new Result();
    }



    /**
     * Import data from libadmin api
     *
     * @param    Boolean        $dryRun
     * @return    Result
     */
    public function import($dryRun = false)
    {
        $this->result->reset();
        $this->result->addInfo('Start import at ' . date('r'));

        try {
            $importData = $this->getData();
            $this->downloadAndStoreAllInstitutionData(); //libadmin_all.json

            $this->result->addSuccess('Data fetched from libadmin');
        } catch (Exceptions\Exception $e) {
            return $this->result->addError($e->getMessage());
        } catch (HttpException $e) {
            $this->result->addError('Unable to connect to the server! Stopped sync');
            return $this->result->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->result->addError('Unexpected error type during import data fetching');
            return $this->result->addError($e->getMessage());
        }

        if (!$dryRun) {
            try {
                $this->result->addInfo('Store data on local system');

                $storeStatus = $this->storeData($importData['data']);

                if ($storeStatus) {
                    $this->result->addSuccess('All data files were stored successfully');

                    $this->clearLanguageCache();
                } else {
                    $this->result->addError('Not all data was imported successfully');
                }
            } catch (Exceptions\Store $e) {
                return $this->result->addError($e->getMessage());
            }
        } else {
            $this->result->addInfo('Skipped storing of data on local system (dry run)');
        }

        if ($this->result->isSuccess()) {
            $this->result->addSuccess('Import successfully completed at ' . date('r'));
        } else {
            $this->result->addError('Import was NOT successful. Finished at ' . date('r'));
        }

        return $this->result;
    }



    public function importMapPortalData($path = null)
    {
        $this->result->reset();
        $this->result->addInfo('Start import at ' . date('r'));

        if (isset($path)) {
            $this->configPath = $path;
        }

        try {
            $this->getData();
            //$this->downloadAndStoreAllInstitutionData(); //libadmin_all.json

            $this->result->addSuccess('Data fetched from libadmin for MapPortal');
        } catch (Exceptions\Exception $e) {
            return $this->result->addError($e->getMessage());
        } catch (HttpException $e) {
            $this->result->addError('Unable to connect to the server! Stopped sync');
            return $this->result->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->result->addError('Unexpected error type during import data fetching');
            return $this->result->addError($e->getMessage());
        }


        if ($this->result->isSuccess()) {
            $this->result->addSuccess('Import for MapPortal successfully completed at ' . date('r'));
        } else {
            $this->result->addError('Import for MapPortal was NOT successful. Finished at ' . date('r'));
        }

        return $this->result;
    }



    /**
     * Flush language cache
     */
    protected function clearLanguageCache()
    {
        $this->result->addInfo('Clear language cache');
        try {
            $this->languageCache->flush();

            $this->result->addSuccess('Cache cleared');
        } catch (\Exception $e) {
            $this->result->addError('Clearing language cache failed');
            $this->result->addError($e->getMessage());
        }
    }



    /**
     * Store received data in different formats
     *
     * @param    Array[]        $data
     * @return    Boolean
     */
    protected function storeData(array $data)
    {
        if (sizeof($data) === 0) {
            $this->result->addInfo('No data received from libadmin server. Are any institutions linked for this view?');
        }

        $this->result->addInfo('Store institution labels');
        $statusInstitution = $this->storeInstitutionLabels($data);

        $this->result->addInfo('Store bibinfo links');
        $statusInfoLinks = $this->storeLibraryInfoLinks($data);

        $this->result->addInfo('Store group labels');
        $statusGroups = $this->storeGroupLabels($data);

        $this->result->addInfo('Store group -> institution relations');
        $statusRelations = $this->storeGroupInstitutionRelations($data);

        $this->result->addInfo('Store favorite institutions');
        $statusFavorites = $this->storeFavorites($data);

        return $statusInstitution && $statusInfoLinks && $statusGroups && $statusRelations && $statusFavorites;
    }



    /**
     * Store translated labels in local/languages/institution/xx.ini files
     *
     * @param    Array    $data
     * @return    Boolean
     */
    protected function storeInstitutionLabels(array $data)
    {
        return $this->storeInstitutionField($data, 'institution', 'label', 'bib_code');
    }



    /**
     * Store bib info links as language file
     *
     * @param    Array $data
     * @return    Boolean
     */
    protected function storeLibraryInfoLinks(array $data)
    {
        return $this->storeInstitutionField($data, 'bibinfo', 'url', 'bib_code');
    }



    /**
     * Store group labels
     *
     * @param    Array $data
     * @return    Boolean
     */
    protected function storeGroupLabels(array $data)
    {
        $translations = array();
        $writer       = new LibadminWriter();
        $status       = true;

        foreach ($data as $group) {
            if (isset($group['group'])) {
                $key = $group['group']['code'];
                foreach ($group['group']['label'] as $locale => $label) {
                    $translations[$locale][$key] = $label;
                }
            }
        }

        foreach ($translations as $locale => $labels) {
            try {
                $storageFile = $writer->saveLanguageFile($labels, 'group', $locale);

                $this->result->addSuccess('Saved [' . $locale . '] group label file to ' . $storageFile);
            } catch (\Exception $e) {
                $this->result->addError('Failed saving [' . $locale . '] group label file');
                $this->result->addError($e->getMessage());
                $status = false;
            }
        }

        return $status;
    }



    /**
     * Store institution group mapping
     * Store relation of each institution to a group as flat list
     * Config file: local/config/vufind/groups.ini
     *
     * @param    Array        $data
     * @return    Boolean
     */
    protected function storeGroupInstitutionRelations(array $data)
    {
        $writer    = new LibadminWriter(LOCAL_OVERRIDE_DIR . '/config/vufind');
        $relations = array(
            'institutions' => array(),
            'groups'       => array()
        );
        $institutionRaw    = array();
        $status    = true;

        foreach ($data as $group) {
                // Add group in order of appearance for sorting
            $relations['groups'][] = $group['group']['code'];

                // Add a mapping to a group for each institution
            foreach ($group['institutions'] as $institution) {
                    // Build a sort key but prevent duplications when invalid position values are provided
                $sortKey                  = $institution['position'] . '_' . $institution['id'];
                $institutionRaw[$sortKey] = array(
                    'institution' => $institution['bib_code'],
                    'group'       => $group['group']['code']
                );
            }
        }

            // Sort and extract institution-group relation
        uksort($institutionRaw, 'strnatcmp');
        foreach ($institutionRaw as $sortKey => $relation) {
            $relations['institutions'][$relation['institution']] = $relation['group'];
        }

            // Write config file
        try {
            $storageFile     = $writer->saveConfigFile($relations, 'libadmin-groups');
            $numInstitutions = sizeof($relations['institutions']);
            $numGroups       = sizeof($relations['groups']);
            $message        = 'Saved group->institution relation (I' . $numInstitutions . '/g:' . $numGroups . ')'
                            . ' config file to ' . $storageFile;

            $this->result->addSuccess($message);
        } catch (\Exception $e) {
            $this->result->addError('Failed saving group->institution relation config');
            $this->result->addError($e->getMessage());
            $status = false;
        }

        return $status;
    }



    /**
     * Store favorite institutions as config file
     *
     * @param    Array    $data
     * @return    Boolean
     */
    protected function storeFavorites(array $data)
    {
        $writer       = new LibadminWriter(LOCAL_OVERRIDE_DIR . '/config/vufind');
        $status        = true;
        $favorites    = array();

        foreach ($data as $group) {
            foreach ($group['institutions'] as $institution) {
                if ($institution['favorite']) {
                    $institutionCode    = $institution['bib_code'];

                    $favorites[$institutionCode] = trim('(' . $institution['bib_code'] . ') '
                                                            . $institution['address']['address']
                                                            . ' ' . $institution['address']['zip']
                                                            . ' ' . $institution['address']['city']);
                }
            }
        }

            // Write config file
        try {
            $storageFile     = $writer->saveConfigFile($favorites, 'favorite-institutions');
            $numInstitutions = sizeof($favorites);
            $message        = 'Saved favorite institutions (' . $numInstitutions . ')' . ' config file to ' . $storageFile;

            $this->result->addSuccess($message);
        } catch (\Exception $e) {
            $this->result->addError('Failed saving institution favorites relation config');
            $this->result->addError($e->getMessage());
            $status = false;
        }

        return $status;
    }



    /**
     * Store localized institution fields in local language files
     *
     * @param    Array     $data
     * @param    String    $type
     * @param    String    $fieldName
     * @param    String    $fieldKey
     * @return    Boolean
     */
    protected function storeInstitutionField(array $data, $type, $fieldName, $fieldKey = 'bib_code')
    {
        $translations = array();
        $writer       = new LibadminWriter();
        $status       = true;

        foreach ($data as $group) {
            if (isset($group['institutions']) && is_array($group['institutions'])) {
                foreach ($group['institutions'] as $institution) {
                    foreach ($institution[$fieldName] as $locale => $label) {
                        $key                         = $institution[$fieldKey];
                        $translations[$locale][$key] = $label;
                    }
                }
            }
        }

        foreach ($translations as $locale => $labels) {
            try {
                $storageFile = $writer->saveLanguageFile($labels, $type, $locale);
                $numLabels   = sizeof($labels);

                $this->result->addSuccess('Saved ' . $numLabels . ' [' . $locale . '] ' . $type . ' label file to ' . $storageFile);
            } catch (\Exception $e) {
                $this->result->addError('Failed saving [' . $locale . '] ' . $type . ' label file');
                $this->result->addError($e->getMessage());
                $status = false;
            }
        }

        return $status;
    }



    /**
     * @return void
     */
    protected function downloadAndStoreAllInstitutionData() {
        $this->downloadAllInstitutions = true;
        $this->getData();
        $this->downloadAllInstitutions = false;
    }



    /**
     * Download data from server
     *
     * @return    String
     * @throws    Exceptions\Fetch
     * @throws    \Exception
     */
    protected function download()
    {
        try {
            $url = $this->getApiEndpointUrl();
        } catch (\Exception $e) {
            $this->result->addError($e->getMessage());

            throw new Exceptions\Fetch('Stopped sync. Cannot start synchronization because API URL is invalid');
        }

        $client = new HttpClient($url);
        if (!empty($this->config->user) && !empty($this->config->password)) {
            $client->setAuth($this->config->user, $this->config->password);
        }

        $this->result->addInfo('Send request to: ' . $url);

        /** @var Response $response */
        $response = $client->send();

        if ($response->isSuccess()) {
            $responseBody = $response->getBody();

            if (!$this->storeDownloadedData($responseBody)) {
                throw new Exceptions\Fetch('Was not able to store downloaded data in a local cache (data/cache/libadmin.json)');
            }

            return $responseBody;
        } else {
            throw new Exceptions\Fetch('Request failed: ' . $response->getReasonPhrase());
        }
    }



    /**
     * Save downloaded response
     * Just for history and debugging
     *
     * @param    String        $responseBody
     * @return    Boolean
     */
    protected function storeDownloadedData($responseBody)
    {
        if ($this->cacheDir && is_writable($this->cacheDir)) {

            $filenamePrefix = "libadmin";
            if (isset($this->configPath)) {
                $partsOfConfigPath = explode('/',$this->configPath);
                $filenamePrefix = $partsOfConfigPath[0];
            }

            $fileName = $this->downloadAllInstitutions ? $filenamePrefix . '_all.json' : $filenamePrefix . '.json';
            $cacheFile = $this->cacheDir . '/' . $fileName;

            return file_put_contents($cacheFile, $responseBody) !== false;
        }

        return false;
    }



    /**
     * Get/download and verify data from server
     *
     * @return    Array
     * @throws Exception\Data
     * @throws Exception\Fetch
     */
    protected function getData()
    {
        $jsonString = $this->download();
        $data       = json_decode($jsonString, true);

        if (is_null($data) || !is_array($data)) {
            throw new Exceptions\Fetch('Received data is invalid');
        }

        if (!isset($data['success']) || !isset($data['data'])) {
            throw new Exceptions\Data('Unknown data format');
        }

        if ($data['success'] !== true) {
            throw new Exceptions\Data('Server reported failed request');
        }

        return $data;
    }



    /**
     * Get full API url from config
     *
     * @return    String
     * @throws    Exceptions\Fetch
     */
    protected function getApiEndpointUrl()
    {

        $path = isset($this->configPath) ? $this->configPath : $this->config->path;

        $apiUrl = $this->config->host . '/' . $this->config->api . '/' . $path;
        if ($this->downloadAllInstitutions) $apiUrl .= '?option[all]=true';

        if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            throw new Exceptions\Fetch('Invalid api url, please check config in Libadmin.ini. Current url "' . $apiUrl . '"');
        }

        return $apiUrl;
    }



    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }



    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
