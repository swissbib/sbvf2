<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Render page functions
 * Options for offset (normal pages)
 *
 */
class PageFunctions extends AbstractHelper
{

	/**
	 * Render page functions
	 *
	 * @param    Boolean        $offset
	 * @return    String
	 */
	public function __invoke($offset = true)
	{
		$data = array(
			'boxClass' => $offset ? 'offset' : ''
		);

		return $this->getView()->render('global/pagefunctions.phtml', $data);
	}
}
