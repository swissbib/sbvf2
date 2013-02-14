<?php

namespace Swissbib\ResultTab;

use VuFind\Controller as VFController;
use VuFind\Search as VFSearch;
use VuFind\Search\Base as VFSearchBase;
use \Zend\View\Model\ViewModel as VFViewModel;

/**
 * [Description]
 *
 * @package       Swissbib
 * @subpackage    [Subpackage]
 */
class SbResultTab {

    /** @var    String */
    protected $id;

    /** @var    String */
    protected $label;

    /** @var    Boolean */
    protected $isSelected;

    /** @var     \Zend\View\Model\ViewModel */
    protected $viewModel;

    /** @var    Integer */
    protected $resultTotal = 0;



    /**
     * Constructor
     *
     * @param   \Zend\View\Model\ViewModel  $viewModel
     * @param   Array                       $config
     * @throws  \Exception
     */
    function __construct($viewModel, array $config = array()) {
            // Init view model and general config
        if( !is_object($viewModel) ) {
            throw new \Exception('Result tab view model is a non-object.');
        }
        $this->viewModel    = $viewModel;

            // Init ID
        if( !array_key_exists('id', $config) || empty($config['id']) ) {
          	throw new \Exception('Result tab ID is not set/empty.');
        }
        $this->id   = trim($config['id']);

            // Init label
        $label  = trim($config['label']);
        $this->label= empty($label) ? 'Label missing!' : $label;

            // Init isSelected
        $this->isSelected   = array_key_exists('selected', $config) ? (!!$config['selected']) : false;
    }



    /**
     * Set tab label
     *
     * @param   String  $label
     */
    public function setLabel($label) {
        $label  = trim($label);

        $this->label    = $label;
    }



    /**
     * Get label
     *
     * @return  String
     */
    public function getLabel() {
        return $this->label;
    }



    /**
     * Get amount of results
     *
     * @return  Integer
     */
    public function getResultTotal() {
        $this->resultTotal  = $this->viewModel->results->getResultTotal();

        return $this->resultTotal;
    }



    /**
     * Get tab config
     *
     * @return  Array
     */
    public function getConfig() {
        return array(
            'id'		=> $this->id,
            'label'		=> $this->getLabel(),
            'count'		=> $this->getResultTotal(),
            'selected'	=> $this->isSelected
        );
    }

}
