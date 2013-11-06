<?php

namespace Swissbib\RecordTab;

use VuFind\RecordTab\Description as VFDescription;

class Description extends VFDescription
{
    /**
     * Is this tab active?
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getRecordDriver()->hasDescription();
    }
}