<?php
namespace Swissbib\Favorites;

use Zend\Cache\Storage\StorageInterface;
use Zend\Config\Config;

use VuFind\Config\PluginManager as ConfigManager;

/**
 * Helper for favorite institutions
 *
 */
class DataSource
{

    /** @var StorageInterface  */
    protected $cache;
    /** @var  ConfigManager */
    protected $configManager;

    const CACHE_KEY = 'favorite-institutions';



    /**
     * Initialize with cache and options data source
     *
     * @param StorageInterface $cache
     * @param ConfigManager    $configManager
     */
    public function __construct(StorageInterface $cache, ConfigManager $configManager)
    {
        $this->cache        = $cache;
        $this->configManager= $configManager;
    }



    /**
     * Get favorite institutions
     *
     * @return    Array
     */
    public function getFavoriteInstitutions()
    {
        if ($this->isCached()) {
            return $this->getCachedData();
        } else {
            $institutionAutocompleteData = $this->loadInstitutionFavoriteData();

            $this->setCachedData($institutionAutocompleteData);

            return $institutionAutocompleteData;
        }
    }



    /**
     * Check whether institutions are already cached
     *
     * @return    Boolean
     */
    protected function isCached()
    {
        return $this->cache->hasItem(self::CACHE_KEY);
    }



    /**
     * Load data from cache
     *
     * @return    Array
     */
    protected function getCachedData()
    {
        return $this->cache->getItem(self::CACHE_KEY);
    }



    /**
     * Write data to cache
     *
     * @param    Array    $institutionList
     * @return    Boolean
     */
    protected function setCachedData(array $institutionList)
    {
        return $this->cache->setItem(self::CACHE_KEY, $institutionList);
    }



    /**
     * Load favorites data
     * Extract from a config object
     *
     * @return    Array
     */
    protected function loadInstitutionFavoriteData()
    {
        /** @var Config $config */
        $config    = $this->configManager->get('favorite-institutions');

        return $config->toArray();
    }
}
