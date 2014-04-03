<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Format integers
 *
 */
class Number extends AbstractHelper
{

    /**
     * Format integer with thousand separator
     *
     * @param    Integer        $number
     * @return    String
     */
    public function __invoke($number)
    {
        return number_format($number, 0, '', '\'');
    }
}
