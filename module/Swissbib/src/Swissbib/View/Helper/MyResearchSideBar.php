<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Render myresearch (account) sidebar
 * Currenty only sets the active menu item
 *
 */
class MyResearchSideBar extends AbstractHelper
{

    /**
     * Render myresearch sidebar with active element
     *
     * @param    String        $active        Active item
     * @param    String        $location
     * @return    String
     */
    public function __invoke($active, $location = '')
    {
        return $this->getView()->render('myresearch/sidebar/wrap.phtml', array(
                                                                              'active'   => $active,
                                                                              'location' => $location
                                                                         ));
    }
}
