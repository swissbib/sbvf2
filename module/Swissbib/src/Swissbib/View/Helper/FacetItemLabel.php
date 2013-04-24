<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Renders a facet item label
 */
class FacetItemLabel extends AbstractHelper
{

	/**
	 * @param   Array  $facet
	 * @return  String
	 */
	public function __invoke(array $facet)
	{
		$displayText = trim($facet['displayText']);
		$count       = intval($facet['count']);

		return $this->view->escapeHtml($displayText) . '&nbsp;(' . $count . ')';
	}
}
