<?php
namespace Swissbib\Search\Solr;

use VuFind\Search\Solr\Params as VuFindSolrParams;
use VuFind\Config\PluginManager;
use VuFind\Search\Base\Options;
use Swissbib\Favorites\Manager as FavoriteManager;

/*
 * Class to extend the core VF2 SOLR functionality related to Parameters
 */
class Params extends VuFindSolrParams
{

	/** @var FavoriteManager  */
	protected $favoritesManager;



	/**
	 * Constructor
	 *
	 * @param	Options  			$options      	Options to use
	 * @param	PluginManager		$configLoader	Config loader
	 * @param	FavoriteManager		$favoritesManger
	 */
	public function __construct($options, PluginManager $configLoader, $favoritesManger)
	{
		parent::__construct($options, $configLoader);

		$this->favoritesManager = $favoritesManger;
	}



	/**
	 * Get user institutions
	 *
	 * @return	String[]
	 */
	public function getUserFavoritesInstitutions()
	{
		return $this->favoritesManager->getUserInstitutions();
	}
}
