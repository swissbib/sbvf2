<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Render facet item
 */
class FacetItem extends AbstractHelper {

	/**
	 * Render facet item
	 *
	 * @param   Array        $facetData
	 * @return  String
	 */
	public function __invoke(array $facetData) {
		$facetData	= array(
		    'facet' => $facetData
		);

		return $this->getView()->render('global/sidebar/search/facet.item.phtml', $facetData);
	}

}