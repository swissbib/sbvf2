<?php
namespace Swissbib\Controller;

use Zend\View\Model\ViewModel,
    Zend\Http\Response as HttpResponse,
    VuFind\Controller\MyResearchController as VuFindMyResearchController,
    VuFind\Db\Row\User,
    Swissbib\VuFind\ILS\Driver\Aleph,
    Zend\Session\Container as SessionContainer;

class MyResearchController extends VuFindMyResearchController
{

	/**
	 * Show photo copy requests
	 *
	 * @return    ViewModel
	 */
	public function photocopiesAction()
	{
		// Stop now if the user does not have valid catalog credentials available:
		if (!is_array($patron = $this->catalogLogin())) {
			return $patron;
		}

		/** @var Aleph $catalog */
		$catalog = $this->getILS();

		// Get photo copies details:
		$photoCopies = $catalog->getPhotocopies($patron['id']);

		return $this->createViewModel(array('photoCopies' => $photoCopies));
	}



	/**
	 * Get bookings
	 *
	 * @return    ViewModel
	 */
	public function bookingsAction()
	{
		// Stop now if the user does not have valid catalog credentials available:
		if (!is_array($patron = $this->catalogLogin())) {
			return $patron;
		}

		/** @var Aleph $catalog */
		$catalog = $this->getILS();

		// Get photo copies details:
		$bookings = $catalog->getBookings($patron['id']);

		return $this->createViewModel(array('bookings' => $bookings));
	}



	/**
	 * Get location parameter from route
	 *
	 * @return    String|Boolean
	 */
	protected function getLocationFromRoute()
	{
		return $this->params()->fromRoute('location', false);
	}



	/**
	 * Inject location from route
	 *
	 * @inheritDoc
	 */
	protected function createViewModel($params = null)
	{
		$viewModel           = parent::createViewModel($params);
		$viewModel->location = $this->getLocationFromRoute() ? : 'baselbern';

		return $viewModel;
	}



	/**
	 * (local) Search User Settings
	 *
	 * @return mixed
	 */
	public function settingsAction()
	{
		$account = $this->getAuthManager();
		if ($account->isLoggedIn() == false) {
			return $this->forceLogin();
		}

		/** @var User $user */
		$user = $this->getUser();

		if ($this->getRequest()->isPost()) {
			$language	= $this->params()->fromPost('language');
			$maxHits	= $this->params()->fromPost('max_hits');

			$user->language = trim($language);
			$user->max_hits = intval($maxHits);

			$user->save();

			$this->flashMessenger()->setNamespace('info')->addMessage('save_settings_success');

			setcookie('language', $language, time()*3600*24*100, '/');

			return $this->redirect()->toRoute('myresearch-settings');
		}

		$language	= $user->language;
		$maxHits	= $user->max_hits;

		return new ViewModel(array(
								 'max_hits'		=> $maxHits,
								 'language'		=> $language,
								 'optsLanguage'	=> array(
									'de' => 'Deutsch',
									'en' => 'English',
									'fr' => 'Francais',
									'it' => 'Italiano'
								),
								 'optsMaxHits'	=> array(
									 10, 20, 40, 60, 80, 100
								 )
							));
	}



	/**
	 * Wrapper for parent
	 *
	 * @return mixed|HttpResponse|ViewModel
	 */
	public function confirmAction()
	{
		$viewModel = parent::confirmAction();

		return $this->wrapWithContentLayout($viewModel, 'myresearch/confirm');
	}



	/**
	 * Wrapper for parent
	 *
	 * @return mixed|HttpResponse|ViewModel
	 */
	public function editAction()
	{
		$viewModel = parent::editAction();

		return $this->wrapWithContentLayout($viewModel, 'myresearch/edit');
	}


    /**
     *
     * creates View snippet to provide users more information about the multi accounts in swissbib
     *
     * @return ViewModel
     */
    public function backgroundaccountsAction()
    {

        return $this->createViewModel();


    }



	/**
	 * Wrapper for parent
	 *
	 * @return mixed|HttpResponse|ViewModel
	 */
	public function editlistAction()
	{
		$viewModel = parent::editlistAction();

		return $this->wrapWithContentLayout($viewModel, 'myresearch/editlist');
	}



	/**
	 * Catch error for not allowed list view
	 * Redirect list own lists with message
	 *
	 * @return	HttpResponse
	 */
	public function mylistAction()
	{
		try {
			$viewModel = parent::mylistAction();
            //GH fromRoute only for base URL -> do we need more?
            //$currentURL = $this->url()->fromRoute();
            //aim: accomplish navigation between 'merkliste' and full view
            $currentURL = $this->getRequest()->getRequestUri();
            $this->getSearchMemory()->rememberSearch($currentURL);
            return $viewModel;
		} catch (\Exception $e) {
			$this->flashMessenger()->setNamespace('error')->addMessage($e->getMessage());

			$target = $this->url()->fromRoute('userList');

			return $this->redirect()->toUrl($target);
		}
	}



	/**
	 * Wrap view in basic content template
	 *
	 * @todo	Improve/generalize
	 * @param ViewModel $viewModel
	 * @param bool      $template
	 * @return ViewModel|HttpResponse;
	 */
	protected function wrapWithContentLayout($viewModel, $template = false)
	{
		if ($viewModel instanceof HttpResponse) {
			return $viewModel;
		}
		if ($viewModel->getTemplate() === 'myresearch/login') {
			return $viewModel;
		}

		$layout	= $this->createViewModel();

		if ($template) {
			$viewModel->setTemplate($template);
		}

		$layout->setTemplate('layout/content');
		$layout->addChild($viewModel, 'content');

		return $layout;
	}



    /**
     * Convenience method to get a session initiator URL. Returns false if not
     * applicable.
     * what does "not applicable" mean:
     * for me (GH) it makes no sense to create a session initiator instance in case we are within the normal workflow of the application
     * (no authentication procedure in conjunction with shibboleth authentication took place)
     * at the moment I compare the domain strings to decide if we should create a session initiator because an authentication with shibboleth tool place
     * another possibilty might be to test the Sibboleth.sso/Session response
     * at the moment we have to issues:
     * a) why redirect prefix in apache session variables?
     * b) access to the shibboleth session variables is only possible immediately after shibboleth authentication process - why?
     * question are pending at switch
     *
     * @return string|bool
     */
    protected function getSessionInitiator()
    {

        //$bag  = array();
       //foreach($this->getRequest()->getServer() as $key => $value) {
        //    $bag[$key] = $value;
        //}

        $uri = $this->getRequest()->getUri();
        $base = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        $baseEscaped =  str_replace("/","\/",$base);

        $tReferrer = $this->getRequest()->getServer()->get('HTTP_REFERER');
        if (preg_match("/$baseEscaped/",$this->getRequest()->getServer()->get('HTTP_REFERER')) == 0) {

            $url = $this->getServerUrl('myresearch-home');
            return $this->getAuthManager()->getSessionInitiator($url);

        } else {

            return false;
        }



    }



    /**
     * Login Action
     * Need to overwrite because of a special handling for Shibboleth workflow
     *
     * @return mixed
     */
    public function loginAction()
    {

        //we need to differantiate between Shibboleh and not Shibboleth authentication mechanisms
        //in case of Shibboleth we will get a problem with HTTP_Referer after successful authentication at IDP
        //because then the Referer points to the IDP address instead of a valid VuFind resource (often something like save a record in various contexts)
        //therefor this mechanisms where we store a temporary session for the latest Referer before the IDP request is executed in the next step by the user
        //at the moment it is used in Swissbib/Controller/RecordController
        $clazz =  $this->getAuthManager()->getAuthClass();
        if ($clazz == "Swissbib\\VuFind\\Auth\\Shibboleth" ) {
            //store the current referrer into a special Session
            $followup = new SessionContainer('ShibbolethSaveFollowup');
            $tURL = $this->getRequest()->getServer()->get('HTTP_REFERER');
            $followup->url = $tURL;
        }


        // If this authentication method doesn't use a VuFind-generated login
        // form, force it through:
        if ($this->getSessionInitiator()) {
            // Don't get stuck in an infinite loop -- if processLogin is already
            // set, it probably means Home action is forwarding back here to
            // report an error!
            //
            // Also don't attempt to process a login that hasn't happened yet;
            // if we've just been forced here from another page, we need the user
            // to click the session initiator link before anything can happen.
            //
            // Finally, we don't want to auto-forward if we're in a lightbox, since
            // it may cause weird behavior -- better to display an error there!
            if (!$this->params()->fromPost('processLogin', false)
                && !$this->params()->fromPost('forcingLogin', false)
                && !$this->inLightbox()
            ) {
                $this->getRequest()->getPost()->set('processLogin', true);
                return $this->forwardTo('MyResearch', 'Home');
            }
        }

        // Make request available to view for form updating:
        $view = $this->createViewModel();
        $view->request = $this->getRequest()->getPost();
        return $view;
    }

    /**
     * Store a referer (if appropriate) to keep post-login redirect pointing
     * to an appropriate location.
     *
     * @return void
     */
    protected function storeRefererForPostLoginRedirect()
    {
        // Get the referer -- if it's empty, there's nothing to store!
        $referer = $this->getRequest()->getServer()->get('HTTP_REFERER');
        if (empty($referer)) {
            return;
        }

        // Normalize the referer URL so that inconsistencies in protocol
        // and trailing slashes do not break comparisons; this same normalization
        // is applied to all URLs examined below.
        $refererNorm = trim(end(explode('://', $referer, 2)), '/');

        // If the referer lives outside of VuFind, don't store it! We only
        // want internal post-login redirects.
        $clazz =  $this->getAuthManager()->getAuthClass();
        if ($clazz === "VuFind\\Auth\\ILS" ) {
            $baseUrl = $this->url()->fromRoute('home');
            $baseUrlNorm = trim(end(explode('://', $baseUrl, 2)), '/');
            if (0 !== strpos($refererNorm, $baseUrlNorm)) {
                return;
            }
        }
        // If the referer is the MyResearch/Home action, it probably means
        // that the user is repeatedly mistyping their password. We should
        // ignore this and instead rely on any previously stored referer.
        $myResearchHomeUrl = $this->url()->fromRoute('myresearch-home');
        $mrhuNorm = trim(end(explode('://', $myResearchHomeUrl, 2)), '/');
        if ($mrhuNorm === $refererNorm) {
            return;
        }

        // If we got this far, we want to store the referer:
        $this->followup()->store(array(), $referer);
    }




}
