<?php
namespace Swissbib\Controller;

use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;

use VuFind\Controller\MyResearchController as VuFindMyResearchController;
use VuFind\Db\Row\User;

use Swissbib\VuFind\ILS\Driver\Aleph;

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
			return parent::mylistAction();
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
}
