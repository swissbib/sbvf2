<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view script for formatting Summon record short titles
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class RemoveHighlight extends AbstractHelper
{
    /**
     * @param   String   $shortTitle
     * @return  String
     */
    public function __invoke($shortTitle)
    {
        return str_replace(
            array('{{{{START_HILITE}}}}', '{{{{END_HILITE}}}}'),
            array('', ''),
            $shortTitle
        );
    }
}
