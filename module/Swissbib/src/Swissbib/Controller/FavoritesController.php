<?php
namespace Swissbib\Controller;


use Zend\View\Model\ViewModel;

use VuFind\Controller\AbstractBase as BaseController;

use Swissbib\Favorites\DataSource as FavoriteDataSource;
use Swissbib\Favorites\Manager as FavoriteManager;

/**
 * Serve holdings data (items and holdings) for solr records over ajax
 *
 */
class FavoritesController extends BaseController
{
	/**
	 * show list of already defined favorites
	 *
	 * @return ViewModel
	 */
	public function displayAction()
	{
		$availableInstitutions	= $this->getAvailableInstitutions();
		$userInstitutions		= $this->getUserInstitutions();

		$data = array(
			'availableInstitutions' => $availableInstitutions,
			'userInstitutions'		=> $userInstitutions
		);


        //todo: besseres sessionhandling
        //ich verwende hier den object cache, der im filesystem gespeichert wird,
        //wir brauchen aber den user cache
        //wie bekomme ich den? Ich benötge gerade zviel Zeit dies nachzuschauen. - Merc!



        //facetquery   ->>> facet.query=institution:z01


        return new ViewModel($data);
	}


	public function addAction()
	{
		$institutionCode	= $this->params()->fromQuery('institution');




	}


	public function selectionListAction()
	{
		return new ViewModel();
	}



	/**
	 *
	 * @return	Array
	 */
	protected function getAvailableInstitutions()
	{
		/** @var FavoriteDataSource $favoriteDataSource */
		$favoriteDataSource = $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\DataSource');

		return $favoriteDataSource->getFavoriteInstitutions();
	}


	protected function getUserInstitutions()
	{
		/** @var FavoriteManager $favoriteManager */
		$favoriteManager	= $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');

		return $favoriteManager->getUserFavorites();
	}


	protected function addFavoriteInstitution($institutionCode)
	{

	}


	protected function removeFavoriteInstitution()
	{

	}





}
