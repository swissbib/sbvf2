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
            $autocompleterData    = false;
        } else {
            $autocompleterData    = $this->getAutocompleterData();

                // mark as downloaded
            $favoriteManager->setInstitutionsDownloaded();
        }

        $data = array(
            'autocompleterData'     => $autocompleterData,
            'userInstitutionsList'    => $this->getUserInstitutionsList()
        );

        //facetquery   ->>> facet.query=institution:z01

        return new ViewModel($data);
    }



    /**
     * Add an institution to users favorite list
     * Return view for selection
     *
     * @return    ViewModel
     */
    public function addAction()
    {
        $institutionCode= $this->params()->fromPost('institution');
        $sendList        = !!$this->params()->fromPost('list');

        if ($institutionCode) {
            $this->addUserInstitution($institutionCode);
        }

        if ($sendList) {
            return $this->getSelectionList();
        } else {
            return $this->getResponse();
        }
    }



    /**
     * Delete a user institution
     *
     * @return    ViewModel
     */
    public function deleteAction()
    {
        $institutionCode    = $this->params()->fromPost('institution');
        $sendList        = !!$this->params()->fromPost('list');

        if ($institutionCode) {
            $this->removeUserInstitution($institutionCode);
        }

        if ($sendList) {
            return $this->getSelectionList();
        } else {
            return $this->getResponse();
        }
    }



    /**
     * Get select list view model
     *
     * @return    ViewModel
     */
    public function getSelectionList()
    {
        return $this->getAjaxViewModel(array(
                                            'userInstitutionsList'    => $this->getUserInstitutionsList()
                                       ), 'favorites/selectionList');
    }



    /**
     * Get data for user institution list
     *
     * @return    Array[]
     */
    protected function getUserInstitutionsList()
    {
        return $this->getFavoriteManager()->getUserInstitutionsListingData();
    }



    /**
     * Add an institution to users favorite list
     *
     * @param    String        $institutionCode
     */
    protected function addUserInstitution($institutionCode)
    {
        $userInstitutions = $this->getUserInstitutions();

        if (!in_array($institutionCode, $userInstitutions)) {
            $userInstitutions[] = $institutionCode;

            $this->getFavoriteManager()->saveUserInstitutions($userInstitutions);
        }
    }



    /**
     * Remove an institution from users favorite list
     *
     * @param    String        $institutionCode
     */
    protected function removeUserInstitution($institutionCode)
    {
        $userInstitutions = $this->getUserInstitutions();

        if (($pos = array_search($institutionCode, $userInstitutions)) !== false) {
            unset($userInstitutions[$pos]);

            $this->getFavoriteManager()->saveUserInstitutions($userInstitutions);
        }
    }



    /**
     * Get autocompleter user institutions data
     * Fetch the translated institution name from label files and append general info (not translated)
     *
     * @return    Array
     */
    protected function getAutocompleterData()
    {
        $availableInstitutions    = $this->getAvailableInstitutions();
        $data                    = array();
        $translator                = $this->getServiceLocator()->get('VuFind\Translator');

        foreach ($availableInstitutions as $institutionCode => $additionalInfo) {
            $data[$institutionCode]    = $translator->translate($institutionCode, 'institution') . ' ' . $additionalInfo;
        }

        return $data;
    }



    /**
     * Get all available institutions
     *
     * @return    Array
     */
    protected function getAvailableInstitutions()
    {
        return $this->getFavoriteDataSource()->getFavoriteInstitutions();
    }



    /**
     * Get institutions which are users favorite
     *
     * @return    String[]
     */
    protected function getUserInstitutions()
    {
        return $this->getFavoriteManager()->getUserInstitutions();
    }



    /**
     *
     *
     * @return    FavoriteManager
     */
    protected function getFavoriteManager()
    {
        return $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
    }



    /**
     *
     *
     * @return    FavoriteDataSource
     */
    protected function getFavoriteDataSource()
    {
        return $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\DataSource');
    }
}
