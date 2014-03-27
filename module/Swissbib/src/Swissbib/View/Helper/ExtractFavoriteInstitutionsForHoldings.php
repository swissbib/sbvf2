<?php
namespace Swissbib\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;

/**
 * Search favorite institutions in holding list and add as a new group as first group
 *
 */
class ExtractFavoriteInstitutionsForHoldings extends AbstractTranslatorHelper
{
    /** @var    Array  */
    protected $userInstitutionCodes;



    /**
     *
     * @param    String[]    $userInstitutionCodes
     */
    public function __construct(array $userInstitutionCodes)
    {
        $this->userInstitutionCodes = array_flip($userInstitutionCodes);
    }



    /**
     * Convert holdings list. Copy favorite institutions
     *
     * @param    Array[]        $holdings
     * @return    Array[]
     */
    public function __invoke(array $holdings)
    {
        $favoriteInstitutions = array();

        foreach ($holdings as $group => $groupData) {
            foreach ($groupData['institutions'] as $institutionCode => $institution) {
                if (isset($this->userInstitutionCodes[$institutionCode])) {
                    $favoriteInstitutions[$institutionCode] = $institution;
                        // Mark as favorite in favorite group and original group
                    $favoriteInstitutions[$institutionCode]['favorite'] = true;
                    $holdings[$group]['institutions'][$institutionCode]['favorite'] = true;
                }
            }
        }

        if ($favoriteInstitutions) {
            $favoriteHoldings = array(
                'label'            => 'mylibraries',
                'institutions'    => $favoriteInstitutions
            );

            $holdings = array('favorite' => $favoriteHoldings) + $holdings;
        }

        return $holdings;
    }
}
