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

    /** @var    String */
    protected $template =  'search/tabs/base.phtml';



    /**
     * Constructor
     *
     * @param   \Zend\View\Model\ViewModel  $viewModel
     * @param   Array                       $config
     * @param   String                      $template
     * @throws  \Exception
     */
    function __construct($viewModel, array $config = array(), $template = '') {
            // Init view model and general config
        if( !is_object($viewModel) ) {
            throw new \Exception('Result tab view model is a non-object.');
        }
        $this->viewModel    = $viewModel;

            // Init view template as given / default
        $this->setTemplate($template);

            // Init ID
        if( !array_key_exists('id', $config) || empty($config['id']) ) {
          	throw new \Exception('Result tab ID is not set/empty.');
        }
        $this->id   = trim($config['id']);

            // Init label
        $label  = trim($config['label']);
        $this->label= empty($label) ? 'Label missing!' : $label;

            /**
             * Init isSelected
             * @note    When there's a key 'selected' it's considered true, w/o looking at the value
             */
        $this->isSelected   = array_key_exists('selected', $config);
    }



    /**
     * Get given property
     *
     * @param   String  $property
     */
    public function __get($property) {
        return $this->$property;
    }



    /**
     * Set given property, optional trim
     *
     * @param   String  $property
     * @param   Mixed   $value
     */
    public function __set($property, $value) {
        $this->$property = $value;
    }



    /**
     * Set tab label
     *
     * @param   String  $label
     */
    public function setLabel($label) {
        $this->__set('label', trim($label));
    }



    /**
     * Get label
     *
     * @return  String
     */
    public function getLabel() {
        return $this->__get('label');
    }



    /**
     * Set template
     *
     * @param   String  $template
     */
    public function setTemplate($template = 'search/tabs/base.phtml') {
        $this->__set('template', trim($template));
    }



    /**
     * Get template
     *
     * @return  String
     */
    public function getTemplate() {
        return $this->template;
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
            'template'  => $this->getTemplate(),
            'id'		=> $this->id,
            'label'		=> $this->getLabel(),
            'count'		=> $this->getResultTotal(),
            'selected'	=> $this->isSelected,
        );
    }

}
