<?php
namespace Swissbib\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;

/**
 * [Description]
 *
 */
class ExtractFavoriteInstitutionsForHoldings extends AbstractTranslatorHelper
{

	protected $userInstitutionCodes;

	public function __construct(array $userInstitutionCodes)
	{
		$this->userInstitutionCodes = array_flip($userInstitutionCodes);
	}


	public function __invoke(array $holdings)
	{
		$favoriteInstitutions = array();

		foreach ($holdings as $group => $groupData) {
			foreach ($groupData['institutions'] as $institutionCode => $institution) {
				if (isset($this->userInstitutionCodes[$institutionCode])) {
					$favoriteInstitutions[$institutionCode] = $institution;
				}
			}
		}

		if ($favoriteInstitutions) {
			$favoriteHoldings = array(
				'label'			=> $this->translator->translate('mylibraries'),
				'institutions'	=> $favoriteInstitutions
			);

			$holdings = array('favorite' => $favoriteHoldings) + $holdings;
		}

		return $holdings;
	}
}
