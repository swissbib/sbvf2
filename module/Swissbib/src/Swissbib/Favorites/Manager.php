<?php
namespace Swissbib\Favorites;

use Zend\Config\Config;
use Zend\Session\Storage\StorageInterface as SessionStorageInterface;
use VuFind\Auth\Manager as AuthManager;

/**
 * Manage user favorites
 * Depending on login status, save in session or database
 *
 */
class Manager
{
    protected $SESSION_DATA = 'institution-favorites';

    protected $SESSION_DOWNLOADED = 'institution-favorites-downloaded';

    /** @var SessionStorageInterface  */
    protected $session;
    /** @var  Config */
    protected $groupMapping;
    /** @var  AuthManager */
    protected $authManager;



    /**
     * Initialize
     *
     * @param    SessionStorageInterface $session
     * @param    Config                    $groupMapping
     * @param    AuthManager                $authManager
     */
    public function __construct(
                    SessionStorageInterface $session,
                    Config $groupMapping,
                    AuthManager $authManager
    ) {
        $this->session        = $session;
        $this->groupMapping    = $groupMapping;
        $this->authManager    = $authManager;
    }



    /**
     * Get user institutions
     *
     * @return    String[]
     * @todo    Do login check
     */
    public function getUserInstitutions()
    {
        return $this->authManager->isLoggedIn() ? $this->getFromDatabase() : $this->getFromSession();
    }



    /**
     * Check whether download flag is set
     *
     * @return    Boolean
     */
    public function hasInstitutionsDownloaded()
    {
        return isset($this->session[$this->SESSION_DOWNLOADED]);
    }



    /**
     * Set downloaded flag in session
     *
     */
    public function setInstitutionsDownloaded()
    {
        $this->session[$this->SESSION_DOWNLOADED] = true;
    }



    /**
     * Save user institutions
     *
     * @param    String[]    $institutionCodes
     */
    public function saveUserInstitutions(array $institutionCodes)
    {
        $test = $this->authManager->isLoggedIn();
        $this->authManager->isLoggedIn() !== false ? $this->saveInDatabase($institutionCodes) : $this->saveInSession($institutionCodes);
    }



    /**
     * Get listing data for user institutions
     *
     * @return    Array[]
     */
    public function getUserInstitutionsListingData()
    {
        $institutions= $this->getUserInstitutions();
        $listing    = array();

        foreach ($institutions as $institutionCode) {
            $groupCode    = isset($this->groupMapping[$institutionCode]) ? $this->groupMapping[$institutionCode] : 'unknown';

            $listing[$groupCode][] = $institutionCode;
        }

        return $listing;
    }



    /**
     * ave institutions in session
     *
     * @param    String[]    $institutions
     */
    protected function saveInSession(array $institutions)
    {
        $this->session[$this->SESSION_DATA] = $institutions;
    }



    /**
     * Save institutions as user setting in database
     *
     * @param    String[]    $institutionCodes
     */
    protected function saveInDatabase(array $institutionCodes)
    {
        $user = $this->authManager->isLoggedIn();

        $user->favorite_institutions = implode(',', $institutionCodes);
        $user->save();
    }



    /**
     * Get user institutions from session
     *
     * @return    String[]
     */
    protected function getFromSession()
    {
        if (!isset($this->session[$this->SESSION_DATA])) {
            $this->session[$this->SESSION_DATA] = array();
        }

        return $this->session[$this->SESSION_DATA];
    }



    /**
     * Get user institutions from database
     *
     * @return    String[]
     */
    protected function getFromDatabase()
    {
        $favoriteList    = $this->authManager->isLoggedIn()->favorite_institutions;

        return $favoriteList ? explode(',', $favoriteList) : array();
    }
}
