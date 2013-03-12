<?php
namespace Swissbib\Controller;

use VuFind\Controller\MyResearchController as VFMyResearchController;
use Swissbib\VuFind\ILS\Driver\Aleph;
use Zend\View\Model\ViewModel;


class MyResearchController extends VFMyResearchController {

	/**
	 * Show photo copy requests
	 *
	 * @return	ViewModel
	 */
	public function photocopiesAction() {
		// Stop now if the user does not have valid catalog credentials available:
		if( !is_array($patron = $this->catalogLogin()) ) {
			return $patron;
		}

		/** @var Aleph $catalog  */
		$catalog = $this->getILS();

		// Get photo copies details:
		$photoCopies = $catalog->getPhotocopies($patron['id']);

		return $this->createViewModel(array('photoCopies' => $photoCopies));
	}



	/**
	 * Get bookings
	 *
	 * @return	ViewModel
	 */
	public function bookingsAction() {
				// Stop now if the user does not have valid catalog credentials available:
		if( !is_array($patron = $this->catalogLogin()) ) {
			return $patron;
		}

		/** @var Aleph $catalog  */
		$catalog = $this->getILS();

		// Get photo copies details:
		$bookings = $catalog->getBookings($patron['id']);

		return $this->createViewModel(array('bookings' => $bookings));
	}



	/**
	 * Get location parameter from route
	 *
	 * @return	String|Boolean
	 */
	protected function getLocationFromRoute() {
		return $this->params()->fromRoute('location', false);
	}



	/**
	 * Inject location from route
	 *
	 * @inheritDoc
	 */
	protected function createViewModel($params = null) {
		$viewModel	= parent::createViewModel($params);
		$viewModel->location = $this->getLocationFromRoute() ?: 'baselbern';

		return $viewModel;
	}



    /**
     * (local) Search User Settings
     *
     * @return mixed
     */
    public function searchsettingsAction()
    {
        $view   = parent::profileAction();

        /** @var $user  \VuFind\Db\Row\User */
        $user       = $this->getUser();

        if( ! is_object($user) ) {
            return $this->forwardTo('MyResearch', 'Login');
        }

        $userData   = $user->toArray();
        $idUser     = intval($userData['id']);

        $userLanguage = $this->getUserLanguage($idUser);
        $view->language = $userLanguage;
        setcookie('language', $userLanguage, null, '/');

        $view->maxHits  = $this->getUserAmountMaxHits($idUser);

        $view->optsLanguage = $this->getOptionsLanguage();
        $view->optsMaxHits  = $this->getOptionsMaximumHits();

        return $view;
    }



    /**
     * EXPLORATION (prove of concept)
     * Store user data sb_nickname to local VF database
     *
     * @return  mixed
     */
    protected function saveaccountlocalAction() {
        $view = $this->createViewModel();

        /** @var $user  \VuFind\Db\Row\User */
        $user       = $this->getUser();

        if( ! is_object($user) ) {
            return $this->forwardTo('MyResearch', 'Login');
        }

        $view->setTerminal(true);
        $userData   = $user->toArray();
        $idUser     = intval($userData['id']);

            // Store received value to database
        /** @var $userLocalData \Swissbib\Db\Table\UserLocalData */
        $userLocalData  = $this->getServiceLocator()->get('Swissbib\DbTablePluginManager')->get('userlocaldata');

            // Init language from received value/default, save to database
        if( array_key_exists('language', $_GET) ) {
            $language   = $_GET['language'];
            $userLocalData->createOrUpdateLanguage($language, 1);
            setcookie('language', $language, null, '/');
        } else {
            $language    = $this->getUserLanguage($idUser);
        }
            // Init max_hits from received value/default, save to database
        if( array_key_exists('max_hits', $_GET) ) {
            $maxHits   = $_GET['max_hits'];
            $userLocalData->createOrUpdateMaxHits($maxHits, 1);
        } else {
            $maxHits    = $this->getUserAmountMaxHits($idUser);
        }
            // Setup layout / view params
        $this->layout()->setTemplate('myresearch/searchsettings');

        $this->layout()->language       = $language;
        $this->layout()->maxHits        = $maxHits;
        $this->layout()->optsLanguage   = $this->getOptionsLanguage();
        $this->layout()->optsMaxHits    = $this->getOptionsMaximumHits();

        //@todo implement flash messages parts: text, CSS, JS
//        $this->flashMessenger()->setNamespace('info')
//                ->addMessage('save_usersettings_success');

        return parent::profileAction();
    }



    /**
     * Get key-label tupels of languages.
     * Labels are each in the resp. language, not to be localized.
     *
     * @return  Array
     */
    public function getOptionsLanguage() {
        return array(
            'de'    => 'Deutsch',
            'en'    => 'English',
            'fr'    => 'Francais',
            'it'    => 'Italiano'
        );
    }



    /**
     * @return  Integer[]
     */
    public function getOptionsMaximumHits() {
        return array(10, 20, 40, 60, 80, 100);
    }



    /**
     * Get user pref: language
     * Or default language if none stored.
     *
     * @param   Integer     $idUser
     * @return  String      Language key
     */
    private function getUserLanguage($idUser) {
        $languageKey    = $this->getUserLocalLanguage($idUser);

        return is_null($languageKey) || !$languageKey ? 'de' : $languageKey;
    }



    /**
     * Get user pref: amount of shown results
     * Or default amount if none stored.
     *
     * @param   Integer     $idUser
     * @return  String  Language key
     */
    public function getUserAmountMaxHits($idUser) {
        $idUser = intval($idUser);

        /** @var $userLocalData \Swissbib\Db\Table\UserLocalData */
        $userLocalData  = $this->getServiceLocator()->get('Swissbib\DbTablePluginManager')->get('userlocaldata');

        $amount    = $userLocalData->getAmountMaxHits($idUser);
        if( is_null($amount) || !$amount) {
                // No pref found? set to default
            $amount    = '50';
        }

        return $amount;
    }



    /**
     * Get user preference: language key
     * Or default if none stored.
     *
     * @param   Integer     $idUser
     * @return  String
     */
    public function getUserLocalLanguage($idUser) {
        $idUser = intval($idUser);

        /** @var $userLocalData \Swissbib\Db\Table\UserLocalData */
        $userLocalData  = $this->getServiceLocator()->get('Swissbib\DbTablePluginManager')->get('userlocaldata');

        $key    = $userLocalData->getLanguage($idUser);
        if( is_null($key) || !$key ) {
                // No pref found? set to default
            $key    = 'de';
        }

        return $key;
    }

}