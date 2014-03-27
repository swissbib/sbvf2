<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view script for formatting publication dates from Summon control field 008 format
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @author   Oliver Schihin oliver.schihin@unibas.ch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class YearFormatterSummon extends AbstractHelper
{
    /**
     * @param   Array   $publicationDate
     * @return  String
     */
    public function __invoke($publicationDate)
    {
        if(is_array($publicationDate)) {
            if( empty($publicationDate) ) {
                return '-';
            } else {
                return implode(', ', $publicationDate);
            }
        }
        return (string) $publicationDate;
    }
}
