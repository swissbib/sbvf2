<?php
namespace Swissbib\Controller;

use Zend\View\Model\ViewModel,
    VuFind\Controller\RecordController as VuFindRecordController,
    VuFind\Exception\RecordMissing as RecordMissingException,
    Zend\Session\Container as SessionContainer;

/**
 * [Description]
 *
 */
class RecordController extends VuFindRecordController
{

	/**
	 * Record home action
	 * Catch record not found exceptions and show error page
	 *
	 * @return	ViewModel
	 */
	public function homeAction()
	{
		try {
            //GH: this is kind of a hack but in this situation not avoidable
            //MarcFormatter and Processor are hard instantiated (not as a service) so you get no chance to set references for these types
            //because MarcFormatter is now implementing ServiceManagerAwareInterface it will get a reference to the ServiceManager to fetch the
            //new service RedirectProtocolWrapper
            //there is another caveat: MarcFormatter is used by the XSLT Template to hook into a custom PHP function using a static function (which doesn't work for PHP 5.4.24 and higher
            //another issue - should be solved by snowfake because it was implemented by them)
            //some work for a redesign
            $this->getServiceLocator()->get("MarcFormatter");

			return parent::homeAction();


		} catch (RecordMissingException $e) {
			$viewModel	= new ViewModel();

			$viewModel->setTemplate('record/not-found');
			$viewModel->setVariables(array(
										  'message'		=> $e->getMessage(),
										  'exception'	=> $e
									 ));

			return $viewModel;
		}
	}

    /**
     * Save action - Allows the save template to appear,
     *   passes containingLists & nonContainingLists
     *
     * @return mixed
     */
    public function saveAction()
    {
        // Process form submission:
        if ($this->params()->fromPost('submit')) {
            return $this->processSave();
        }

        // Retrieve user object and force login if necessary:
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }

        // If we got so far, we should save the referer for later use by the
        // ProcessSave action (to get back to where we came from after saving).
        // We shouldn't save follow-up information if it points to the Save
        // screen or the "create list" screen, as this causes confusing workflows;
        // in these cases, we will simply default to pushing the user to record view.

        $shibFollowup = new SessionContainer('ShibbolethSaveFollowup');
        $tURL = $shibFollowup->url;

        //only for Shibboleth case otherwise use the ordinary HTTP_Referer
        //(standard VuFind)
        if (!empty($tURL)) {
            $referer = $tURL;
            //clear the temporary session because we don't need it anymore
            //(the user was successfully authenticated)
            $shibFollowup->getManager()->getStorage()->clear('ShibbolethSaveFollowup');

        } else {
            $referer = $this->getRequest()->getServer()->get('HTTP_REFERER');
        }
        $followup = new SessionContainer($this->searchClassId . 'SaveFollowup');

        if (substr($referer, -5) != '/Save'
            && stripos($referer, 'MyResearch/EditList/NEW') === false
        ) {
            $followup->url = $referer;
        }

        // Retrieve the record driver:
        $driver = $this->loadRecord();

        // Find out if the item is already part of any lists; save list info/IDs
        $listIds = array();
        $resources = $user->getSavedData(
            $driver->getUniqueId(), null, $driver->getResourceSource()
        );
        foreach ($resources as $userResource) {
            $listIds[] = $userResource->list_id;
        }

        // Loop through all user lists and sort out containing/non-containing lists
        $containingLists = $nonContainingLists = array();
        foreach ($user->getLists() as $list) {
            // Assign list to appropriate array based on whether or not we found
            // it earlier in the list of lists containing the selected record.
            if (in_array($list->id, $listIds)) {
                $containingLists[] = array(
                    'id' => $list->id, 'title' => $list->title
                );
            } else {
                $nonContainingLists[] = array(
                    'id' => $list->id, 'title' => $list->title
                );
            }
        }

        $view = $this->createViewModel(
            array(
                'containingLists' => $containingLists,
                'nonContainingLists' => $nonContainingLists
            )
        );
        $view->setTemplate('record/save');
        return $view;
    }


}
