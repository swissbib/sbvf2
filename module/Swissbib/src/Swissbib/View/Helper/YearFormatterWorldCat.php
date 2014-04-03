<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view script for formatting WorldCat publication dates
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class YearFormatterWorldCat extends AbstractHelper
{

    /**
     * @param   Array   $publicationDate
     * @return  String
     */
    public function __invoke($publicationDate)
    {
        if (!is_array($publicationDate)) {
            return '-';
        }
        $amountElements = count($publicationDate);

        if ($amountElements === 1) {
            $date = $publicationDate[0];
        } else {
            // Fallback
            $date = implode(' ', $publicationDate);
        }

        $date = trim($date);

        return !empty($date) ? $date : '-';
    }
}
