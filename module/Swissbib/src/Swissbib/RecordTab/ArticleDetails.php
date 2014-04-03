<?php
/**
 *  details tab for target 'Articles & more'
 */

namespace Swissbib\RecordTab;

use \VuFind\RecordTab\AbstractBase;

class ArticleDetails extends AbstractBase
{
    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Description';
    }
}