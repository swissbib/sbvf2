<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Check whether an institution code is a user favorite
 *
 */
class IsFavoriteInstitution extends AbstractHelper
{

    /** @var    Array  */
    protected $userInstitutionCodes;


    /**
     * Initialize with user favorites
     *
     * @param    String[]    $userInstitutionCodes
     */
    public function __construct(array $userInstitutionCodes)
    {
        $this->userInstitutionCodes = $userInstitutionCodes;
    }



    /**
     * Check whether one of the item institutions matches with one of the user institutions
     *
     * @param    String[]        $institutionCodes
     * @return    Boolean
     */
    public function __invoke(array $institutionCodes)
    {
        return sizeof(array_intersect($institutionCodes, $this->userInstitutionCodes)) > 0;
    }
}
