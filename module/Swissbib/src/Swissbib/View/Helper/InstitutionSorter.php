<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Sort institutions based on position in list
 *
 */
class InstitutionSorter extends AbstractHelper
{

    /**
     * @var    Array    List of institutions. BibCode is the key, position the value
     */
    protected $institutions = array();



    /**
     * Initialize with institution list
     *
     * @param    Array    $institutions
     */
    public function __construct(array $institutions)
    {
        $this->institutions = array_flip($institutions);
    }



    /**
     * Sort list of institution
     *
     * @param    Array    $institutions
     * @return    Array
     */
    public function sortInstitutions(array $institutions)
    {
        $sorted         = array();
        $missingCounter = 2000;

            // No sorting for single institution
        if (sizeof($institutions) === 1) {
            return $institutions;
        }

        foreach ($institutions as $institution) {
            $institutionKey = $institution;
            $pos    = isset($this->institutions[$institutionKey]) ? $this->institutions[$institutionKey] : $missingCounter++;
            $sorted[$pos] = $institution;
        }

        ksort($sorted);

        return array_values($sorted);
    }

}
