<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Render myresearch (account) sidebar
 * Currenty only sets the active menu item
 *
 */
class MyResearchSideBar extends AbstractHelper {

	/**
	 * Render myresearch sidebar with active element
	 *
	 * @param	String		$active		Active item
	 * @return	String
	 */
	public function __invoke($active) {
		return $this->getView()->render('myresearch/sidebar/base.phtml', array(
			'active'	=> $active
		));
	}

}