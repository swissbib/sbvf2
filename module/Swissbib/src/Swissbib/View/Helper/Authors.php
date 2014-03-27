<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Prepare authors link list
 *
 */
class Authors extends AbstractHelper
{

    /**
     * Merge author fields to a single list with urls
     *
     * @param    Array    $authors
     * @return    Array[]
     */
    public function __invoke(array $authors = array())
    {
        $recordPlugin = $this->getView()->plugin('record');

        $mainAuthor       = isset($authors['main']) && !empty($authors['main']) ? $authors['main'] : false;
        $corporateAuthor  = isset($authors['corporate']) && !empty($authors['corporate']) ? $authors['corporate'] : false;
        $secondaryAuthors = isset($authors['secondary']) ? $authors['secondary'] : false;

        $authorsData = array();

        if ($mainAuthor) {
            $authorsData[] = array(
                'author' => $mainAuthor,
                'url'    => $recordPlugin->getLink('author', $mainAuthor),
                'type'   => 'main'
            );
        }
        if ($corporateAuthor) {
            $authorsData[] = array(
                'author' => $corporateAuthor,
                'url'    => $recordPlugin->getLink('author', $corporateAuthor),
                'type'   => 'corporate'
            );
        }
        if ($secondaryAuthors) {
            foreach ($secondaryAuthors as $secondaryAuthor) {
                $authorsData[] = array(
                    'author' => $corporateAuthor,
                    'url'    => $recordPlugin->getLink('author', $secondaryAuthor),
                    'type'   => 'secondary'
                );
            }
        }

        return $authorsData;
    }
}
