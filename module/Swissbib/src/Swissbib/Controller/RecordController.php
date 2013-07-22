<?php
namespace Swissbib\Controller;

use Zend\View\Model\ViewModel;

use VuFind\Controller\RecordController as VuFindRecordController;
use VuFind\Exception\RecordMissing as RecordMissingException;

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
}
