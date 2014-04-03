<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view script for rendering main titles in all cases, even if marc-record has empty title field
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @author   Oliver Schihin oliver.schihin@unibas.ch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class MainTitle extends AbstractHelper
{

    public function __invoke($title, $resultItem = null)
    {
        if ($title != '@') {
            return $title;
        } elseif (!isset($title)) {
            return '[ohne Titel]';
        } elseif ($title == '@') {
            // 'hier müsste 490 av rein';
        }
    }
}
