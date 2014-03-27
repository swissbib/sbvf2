<?php
namespace Swissbib\Log;

use Zend\Log\Logger as ZendLogger;

/**
 * Log special events
 *
 */
class Logger extends ZendLogger
{
    /** @var String[] */
    protected $untranslatedInstitutions = array();
    /** @var String[] */
    protected $ungroupedInstitutinos = array();



    /**
     * Log an untranslated institution
     *
     * @param    String        $institutionCode
     */
    public function logUntranslatedInstitution($institutionCode)
    {
        if (!isset($this->untranslatedInstitutions[$institutionCode])) {
            $this->info('Untranslated institution: "' . $institutionCode . '"');

            $this->untranslatedInstitutions[$institutionCode] = $institutionCode;
        }
    }



    /**
     * Log an ungrouped institution
     *
     * @param    String        $institutionCode
     */
    public function logUngroupedInstitution($institutionCode)
    {
        if (!isset($this->ungroupedInstitutinos[$institutionCode])) {
            $this->info('No group found for institution: "' . $institutionCode . '"');

            $this->ungroupedInstitutinos[$institutionCode] = $institutionCode;
        }
    }
}
