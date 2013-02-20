<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view script for formatting publication dates from SolrMarc control field 008 format
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @author   Oliver Schihin oliver.schihin@unibas.ch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class YearFormatterMarc extends AbstractHelper {

    public function __invoke($publicationDate) {
        if( !is_array($publicationDate) || sizeof($publicationDate) == 0 ) {
            return '';
        }

        $dateType   = $publicationDate[0];
        $year1      = $publicationDate[1];
        $year2      = $publicationDate[2];

        switch ($dateType)
        {
            case 's':
            case 'n':
            case 'e':
                $retVal = str_replace('u', '?', $year1);
                return $retVal;
            break;

            case 'c':
            case 'u':
                $retVal = str_replace('u', '?', $year1) . '-';
                return $retVal;
            break;

            case 'd':
                $retVal = str_replace('u', '?', $year1) . '-' . str_replace('u', '?', $year2);
                return $retVal;
            break;

            case 'p':
            case 'r':
                $retVal = str_replace('u', '?', $year1) . ' [' . str_replace('u', '?', $year2) . ']';
                return $retVal;
            break;

            case 'q':
                if ($year2 === '9999'):
                    $retVal = str_replace('u', '?', $year1);
                elseif ($year2 != '9999'):
                    $retVal = str_replace('u', '?', $year1) . ' / ' . str_replace('u', '?', $year2);
                endif;
                return $retVal;
            break;

            case 'm':
                if ($year2 === '9999'):
                    $retVal = str_replace('u', '?', $year1) . '-';
                elseif ($year2 != '9999'):
                    $retVal = str_replace('u', '?', $year1) . '-' . str_replace('u', '?', $year2);
                endif;
                return $retVal;
            break;
        }
    }
}
