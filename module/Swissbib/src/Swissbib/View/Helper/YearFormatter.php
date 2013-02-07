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
        if ($datetype === 's' xor 'n' xor 'e')
        {
            $retval = str_replace('u', '?', $year1);
            return $retval;
        }
        if ($datetype === 'c' || 'u')
        {
            $retval = str_replace('u', '?', $year1) . '-';
            return $retval;
        }
        if ($datetype === 'd')
        {
            $retval = str_replace('u', '?', $year1) . '-' . str_replace('u', '?', $year2);
            return $retval;
        }
        if ($datetype === 'p' || 'r')
        {
            $retval = str_replace('u', '?', $year1) . ' [' . str_replace('u', '?', $year2) . ']';
            return $retval;
        }
        if ($datetype === 'q')
        {
            if ($year2 === '9999')
            {
                $retval = str_replace('u', '?', $year1);

            }
            if ($year2 != '9999')
            {
                $retval = str_replace('u', '?', $year1) . ' / ' . str_replace('u', '?', $year2);
            }
            return $retval;
        }
        if ($datetype === 'm')
        {
            if ($year2 === '9999')
            {
                $retval = str_replace('u', '?', $year1) . '-';
            }
            if ($year2 != '9999')
            {
                $retval = str_replace('u', '?', $year1) . '-' . str_replace('u', '?', $year2);
            }
            return $retval;
        }
    }
}
