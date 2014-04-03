<?php
namespace Swissbib\View\Helper;

use VuFind\View\Helper\Root\RecordLink as VfRecordLink;

/**
 * Build record links
 * Override related method to support ctrlnum type
 *
 */
class RecordLink extends VfRecordLink
{

    /**
     * @inheritDoc
     */
    public function related($link, $escape = true)
    {
        if ($link['type'] === 'ctrlnum') {
            return $this->buildCtrlNumRelatedLink($link, $escape);
        } else {
            return parent::related($link, $escape);
        }
    }



    /**
     * Build link for ctrlnum
     *
     * @param      $link
     * @param bool $escape
     * @return string
     */
    protected function buildCtrlNumRelatedLink($link, $escape = true)
    {
        $urlHelper    = $this->getView()->plugin('url');
        $escapeHelper = $this->getView()->plugin('escapeHtml');

        $url = $urlHelper('search-results')
                . '?lookfor=' . urlencode($link['value'])
                . '&type=ctrlnum&jumpto=1';

        return $escape ? $escapeHelper($url) : $url;
    }
}
