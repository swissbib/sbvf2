<?php
namespace Swissbib\Favorites;

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

	public function __construct(UserTable $userTable, SessionStorageInterface $session)
	{
		$this->userTable	= $userTable;
		$this->session		= $session;
	}

	public function getUserFavorites()
	{
		if (!isset($this->session[$this->SESSION_DATA])) {
			$this->session[$this->SESSION_DATA] = array();
		}

		return $this->session[$this->SESSION_DATA];
	}


	public function hasInstitutionsDownloaded()
	{
		return isset($this->session[$this->SESSION_DOWNLOADED]);
	}

	public function setInstitutionsDownloaded()
	{
		$this->session[$this->SESSION_DOWNLOADED] = true;
	}


	public function saveUserFavorites(array $favoriteInstitutions)
	{
		$this->saveInSession($favoriteInstitutions);
	}


	protected function saveInSession(array $institutions)
	{
		$this->session[$this->SESSION_DATA] = $institutions;
	}


}
