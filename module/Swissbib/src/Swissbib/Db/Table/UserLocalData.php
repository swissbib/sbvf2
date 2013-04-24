<?php

namespace Swissbib\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Db\Sql\Expression;

/**
 * Table Definition for user_localdata
 *
 * @category Swissbib
 * @package  Db_Table
 */
class UserLocalData extends Gateway
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('user_localdata', 'Swissbib\Db\Row\UserLocalData');
	}



	/**
	 * @param   String  $language
	 * @param   Integer $user_id
	 */
	public function createOrUpdateLanguage($language, $user_id)
	{
		$this->createOrUpdateValue('language', $language, $user_id);
	}



	/**
	 * Get given user's language preference
	 *
	 * @param   Integer     $user_id
	 * @return  Boolean|String
	 */
	public function getLanguage($user_id)
	{
		return $this->getValue('language', $user_id);
	}



	/**
	 * Get given user's max_hits preference
	 *
	 * @param   Integer     $user_id
	 * @return  Boolean|Integer
	 */
	public function getAmountMaxHits($user_id)
	{
		return $this->getValue('max_hits', $user_id, true);
	}



	/**
	 * Get given value of given user, optionally cast to integer
	 *
	 * @param   String          $column
	 * @param   Integer         $user_id
	 * @param   Boolean         $intVal
	 * @return  String|Boolean
	 */
	public function getValue($column, $user_id, $intVal = false)
	{
		$user_id = intval($user_id);
		$userLocalData = $this->getUserLocalData($user_id);

		if (!is_array($userLocalData)) {
			return false;
		}

		return $intVal ? intval($userLocalData[$column]) : $userLocalData[$column];
	}



	/**
	 * Get full local data record of given user as array
	 *
	 * @param   Integer         $user_id
	 * @return  Array|Boolean
	 */
	private function getUserLocalData($user_id)
	{
		$params = array(
			'user_id' => $user_id
		);
		$result = $this->select($params)->current();

		return !$result ? false : $result->toArray();
	}



	/**
	 * @param   String  $maxHits
	 * @param   Integer $user_id
	 */
	public function createOrUpdateMaxHits($maxHits, $user_id)
	{
		$this->createOrUpdateValue('max_hits', intval($maxHits), $user_id);
	}



	/**
	 * @param   string    $column
	 * @param   string    $value
	 * @param   string    $user_id     ID of user creating link
	 */
	public function createOrUpdateValue($column, $value, $user_id)
	{
		$params = array('user_id' => $user_id,);
		$result = $this->select($params)->current();

		// Only create row if it does not already exist:
		if (empty($result)) {
			$result = $this->createRow();
			$result->user_id = $user_id;

		}

		$result->$column = $value;

		$result->save();
	}

}
