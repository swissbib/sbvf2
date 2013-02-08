<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view script for formatting publication dates based on controlfield 008 values
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @author   Oliver Schihin oliver.schihin@unibas.ch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class YearFormatter extends AbstractHelper {
    public function __invoke($publicationDate) {
		if( !is_array($publicationDate) || sizeof($publicationDate) == 0 ) {
			return '';
		}
        $datetype = $publicationDate[0];
        $year1 = $publicationDate[1];
        $year2 = $publicationDate[2];

        switch ($datetype)
        {
            case 's':
            case 'n':
            case 'e':
                $retval = str_replace('u', '?', $year1);
                return $retval;
            break;

            case 'c':
            case 'u':
                $retval = str_replace('u', '?', $year1) . '-';
                return $retval;
            break;

            case 'd':
                $retval = str_replace('u', '?', $year1) . '-' . str_replace('u', '?', $year2);
                return $retval;
            break;

            case 'p':
            case 'r':
                $retval = str_replace('u', '?', $year1) . ' [' . str_replace('u', '?', $year2) . ']';
                return $retval;
            break;

            case 'q':
                if ($year2 === '9999'):
                    $retval = str_replace('u', '?', $year1);
                elseif ($year2 != '9999'):
                    $retval = str_replace('u', '?', $year1) . ' / ' . str_replace('u', '?', $year2);
                endif;
                return $retval;
            break;

            case 'm':
                if ($year2 === '9999'):
                    $retval = str_replace('u', '?', $year1) . '-';
                elseif ($year2 != '9999'):
                    $retval = str_replace('u', '?', $year1) . '-' . str_replace('u', '?', $year2);
                endif;
                return $retval;
            break;
        }
    }
}
