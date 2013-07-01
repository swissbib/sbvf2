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
	protected $SESSION_KEY = 'institution-favorites';
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
		if (!isset($this->session[$this->SESSION_KEY])) {
			$this->session[$this->SESSION_KEY] = array();
		}

		return $this->session[$this->SESSION_KEY];
	}


	public function saveUserFavorites(array $favoriteInstitutions)
	{

	}
}
