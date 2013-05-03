<?php

namespace Swissbib\Controller\Helper;

use Zend\Session\Container as SessionContainer;

/**
 * Search controller helpers - Outsourced shared methods used by multiple swissbib controllers
 * used e.g. in Swissbib SearchController and SummonController
 *
 * @package Swissbib\Controller\Helper
 */
class Search {

	const COOKIENAME_SELECTED_TAB = 'tabbed_catalog';

	/**
	 * Store selected tab's search query Uri to session container
	 *
	 * @param   String  $idTab
	 * @param   String  $requestUri
	 */
	public static function rememberTabbedSearchURI($idTab, $requestUri)
	{
		self::storeInSessionContainer('SbTabbedSearch_' . $idTab, 'last', $requestUri);
	}

	/**
	 * Store given parameter to given key of given session container
	 *
	 * @param   String  $containerName
	 * @param   String  $parameterKey
	 * @param   String  $value
	 */
	public static function storeInSessionContainer($containerName, $parameterKey, $value)
	{
		$session                = new SessionContainer($containerName);
		$session->$parameterKey = $value;
	}



	/**
	 * Retrieve value of given parameter from given session container
	 *
	 * @param   String      $containerName
	 * @param   String      $parameterKey
	 * @return  Mixed|null
	 */
	private function retrieveFromSessionContainer($containerName, $parameterKey)
	{
		$session = new SessionContainer($containerName);

		return isset($session->$parameterKey) ? $session->$parameterKey : null;
	}

}

?>