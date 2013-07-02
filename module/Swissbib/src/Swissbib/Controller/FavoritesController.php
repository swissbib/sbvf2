<?php
namespace Swissbib\Controller;


use Zend\View\Model\ViewModel;

use Swissbib\Controller\BaseController;
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
		$favoriteManager = $this->getFavoriteManager();

			// Are institutions already in browser cache?
		if ($favoriteManager->hasInstitutionsDownloaded()) {
			$availableInstitutions	= false;
		} else {
			$availableInstitutions	= $this->getAvailableInstitutions();

				// mark as downloaded
			$favoriteManager->setInstitutionsDownloaded();
		}

		$userInstitutions		= $this->getUserInstitutions();
		$userInstitutionList	= $favoriteManager->extendUserInstitutionsForListing($userInstitutions);

		$data = array(
			'availableInstitutions' => $availableInstitutions,
			'userInstitutions'		=> $userInstitutions,
			'userInstitutionsList'	=> $userInstitutionList
		);

//		$this->addFavoriteInstitution('a274');
//		$this->addFavoriteInstitution('a276');
//		$this->addFavoriteInstitution('a278');
//		$this->addFavoriteInstitution('a281');
//		$this->addFavoriteInstitution('a282');


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
		$userInstitutions	= $this->getUserInstitutions();

//		return $this->getA

//		return new ViewModel();

		return $this->getAjaxViewModel(array(
											'userInstitutions'	=> $userInstitutions
									   ));
	}




	protected function addFavoriteInstitution($institutionCode)
	{
		$userInstitutions = $this->getUserInstitutions();

		if (!in_array($institutionCode, $userInstitutions)) {
			$userInstitutions[] = $institutionCode;

			$this->getFavoriteManager()->saveUserInstitutions($userInstitutions);
		}
	}


	protected function removeFavoriteInstitution($institutionCode)
	{
		$userInstitutions = $this->getUserInstitutions();

		if ($pos = array_search($institutionCode, $userInstitutions)) {
			unset($userInstitutions[$pos]);

			$this->getFavoriteManager()->saveUserInstitutions($userInstitutions);
		}
	}


	/**
	 *
	 * @return	Array
	 */
	protected function getAvailableInstitutions()
	{
		return $this->getFavoriteDataSource()->getFavoriteInstitutions();
	}


	protected function getUserInstitutions()
	{
		return $this->getFavoriteManager()->getUserInstitutions();
	}


	/**
	 *
	 *
	 * @return	FavoriteManager
	 */
	protected function getFavoriteManager()
	{
		return $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
	}



	/**
	 *
	 *
	 * @return	FavoriteDataSource
	 */
	protected function getFavoriteDataSource()
	{
		return $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\DataSource');
	}

}
