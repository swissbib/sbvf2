<?php
namespace Swissbib\Controller;

use VuFind\Controller\RecordController as VFRecordController;
use VuFind\Config\Reader as ConfigReader;
use Zend\Session\Container as SessionContainer;

/**
 * [Description]
 *
 * @package       Swissbib
 * @subpackage    [Subpackage]
 */
class RecordController extends VFRecordController {

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call standard record controller initialization:
        parent::__construct();
    }

}