<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * [Description]
 *
 */
class FilterFavoriteInstitutionFacets extends AbstractHelper
{
	protected $userInstitutionCodes;

	public function __construct(array $userInstitutionCodes)
	{
		$this->userInstitutionCodes = array_flip($userInstitutionCodes);
	}

	public function __invoke(array $institutionFacets)
	{
		$favoriteInstitutionFacets = array();

		if (sizeof($this->userInstitutionCodes)) {
			foreach ($institutionFacets as $institutionFacet) {
				if (isset($this->userInstitutionCodes[$institutionFacet['value']])) {
					$favoriteInstitutionFacets[] = $institutionFacet;
				}
			}
		}

		return $favoriteInstitutionFacets;
	}
}
