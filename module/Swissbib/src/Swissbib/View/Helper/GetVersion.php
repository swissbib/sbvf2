<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;


/**
 * Read-out latest git tag
 *
 * Class GitTagNumber
 * @package Swissbib\View\Helper
 */
class GetVersion extends AbstractHelper
{
    /**
     * Get tab specific template path if present
     *
     * @return    String
     */
    public function __invoke()
    {
        $pathVersionFile    = 'module/Swissbib/version.txt';

        return file_exists($pathVersionFile) ? file_get_contents($pathVersionFile) : '';
    }

}
