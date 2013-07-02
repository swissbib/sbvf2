<?php
namespace Swissbib\Favorites;

use Zend\Config\Config;
use Zend\Session\Storage\StorageInterface as SessionStorageInterface;
use VuFind\Db\Table\User as UserTable;

/**
 * Manage user favorites
 * Depending on login status, save in session or database
 *
 */
class Manager
{
	protected $SESSION_DATA = 'institution-favorites';

	protected $SESSION_DOWNLOADED = 'institution-favorites-downloaded';

	/** @var UserTable  */
	protected $userTable;
	/** @var SessionStorageInterface  */
	protected $session;
	/** @var  Config */
	protected $groupMapping;



	/**
	 * Initialize
	 *
	 * @param UserTable               $userTable
	 * @param SessionStorageInterface $session
	 * @param	Config					$groupMapping
	 */
	public function __construct(UserTable $userTable, SessionStorageInterface $session, Config $groupMapping)
	{
		$this->userTable	= $userTable;
		$this->session		= $session;
		$this->groupMapping	= $groupMapping;
	}



	/**
	 * Get user institutions
	 *
	 * @return	String[]
	 * @todo	Do login check
	 */
	public function getUserInstitutions()
	{
		return $this->getFromSession();
	}



	/**
	 * Check whether download flag is set
	 *
	 * @return	Boolean
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
	 * @param	String[]	$institutions
	 */
	public function saveUserInstitutions(array $institutions)
	{
		$this->saveInSession($institutions);
	}



	/**
	 *
	 *
	 * @param	String[]	$institutions
	 * @return	Array[]
	 */
	public function extendUserInstitutionsForListing(array $institutions)
	{
		$listing	= array();

		foreach ($institutions as $institutionCode) {
			$groupCode	= isset($this->groupMapping[$institutionCode]) ? $this->groupMapping[$institutionCode] : 'unknown';

			$listing[$groupCode][] = $institutionCode;
		}

		return $listing;
	}



	/**
	 * ave institutions in session
	 *
	 * @param	String[]	$institutions
	 */
	protected function saveInSession(array $institutions)
	{
		$this->session[$this->SESSION_DATA] = $institutions;
	}



	/**
	 * Save institutions as user setting in database
	 *
	 * @param	String[]	$institutions
	 */
	protected function saveInDatabase(array $institutions)
	{
		// implement
	}



	/**
	 * Get user institutions from session
	 *
	 * @return	String[]
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
	 * @return	String[]
	 */
	protected function getFromDatabase()
	{
		return array();
	}
}
