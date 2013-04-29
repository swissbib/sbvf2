<?php
namespace Swissbib\View\Helper;

use Swissbib\RecordDriver\SolrMarc;
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
	 * @param	SolrMarc		$driver
	 * @param    Boolean        $offset
	 * @return    String
	 */
	public function __invoke($driver, $offset = true)
	{
		$data = array(
			'boxClass' => $offset ? 'offset' : '',
			'driver'	=> $driver
		);

		return $this->getView()->render('global/pagefunctions.phtml', $data);
	}
}
