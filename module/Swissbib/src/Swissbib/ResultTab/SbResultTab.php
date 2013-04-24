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
class SbResultTab
{

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

	/** @var    Array */
	protected $templates;



	/**
	 * Constructor
	 *
	 * @param   \Zend\View\Model\ViewModel  $viewModel (or null)
	 * @param   Array                       $config
	 * @param   Array                       $templates
	 * @throws  \Exception
	 */
	function __construct($viewModel, array $config = array(), array $templates = array())
	{
		$this->viewModel = $viewModel;

		// Init view template as given / default
		$this->setTemplates($templates);

		// Init ID
		if (!array_key_exists('id', $config) || empty($config['id'])) {
			throw new \Exception('Result tab ID is not set/empty.');
		}
		$this->id = trim($config['id']);

		// Init label
		$label = trim($config['label']);
		$this->label = empty($label) ? 'Label missing!' : $label;

		/**
		 * Init isSelected
		 * @note    When there's a key 'selected' it's considered true, w/o looking at the value
		 */
		$this->isSelected = array_key_exists('selected', $config);
	}



	/**
	 * Get given property
	 *
	 * @param   String  $property
	 */
	public function __get($property)
	{
		return $this->$property;
	}



	/**
	 * Set given property, optional trim
	 *
	 * @param   String  $property
	 * @param   Mixed   $value
	 */
	public function __set($property, $value)
	{
		$this->$property = $value;
	}



	/**
	 * Set tab label
	 *
	 * @param   String  $label
	 */
	public function setLabel($label)
	{
		$this->__set('label', trim($label));
	}



	/**
	 * Get label
	 *
	 * @return  String
	 */
	public function getLabel()
	{
		return $this->label;
	}



	/**
	 * Set templates (tab, sidebar partial(s))
	 *
	 * @param   Array  $templates
	 */
	public function setTemplates(array $templates = array())
	{
		// Ensure tab template
		$templates = $this->ensureTemplateSet($templates, 'tab', 'search/tabs/base.phtml');
		// Ensure sidebar partial template
		$templates['sidebar'] = $this->ensureTemplateSet(array_key_exists('sidebar', $templates) ? $templates['sidebar'] : array(), null, 'global/sidebar/search/filters.phtml');

		$this->__set('templates', $templates);
	}



	/**
	 * Ensure given template to be set, if not: set given default
	 *
	 * @param   Array   $templates
	 * @param   String  $key
	 * @param   String  $default
	 * @return  Array
	 */
	protected function ensureTemplateSet($templates, $key = 'tab', $default = 'search/tabs/base.phtml')
	{
		if (!is_null($key)) {
			if (!array_key_exists($key, $templates) || empty($templates[$key])) {
				$templates['tab'] = $default;
			}
		} else if (empty($templates)) {
			$templates[] = $default;
		}

		return $templates;
	}



	/**
	 * Get template
	 *
	 * @return  String
	 */
	public function getTemplates()
	{
		return $this->templates;
	}



	/**
	 * Get amount of results
	 *
	 * @return  Integer|Boolean
	 */
	public function getResultTotal()
	{
		if (!$this->viewModel) {
			return false;
		}

		/** @var $results \VuFind\Search\Base\Results */
		$results = $this->viewModel->results;
		$this->resultTotal = $results->getResultTotal();

		return $this->resultTotal;
	}



	/**
	 * Get tab config
	 *
	 * @return  Array
	 */
	public function getConfig()
	{
		return array(
			'id' => $this->id,
			'label' => $this->getLabel(),
			'selected' => $this->isSelected,
			'templates' => $this->getTemplates(),
			'count' => $this->getResultTotal(),
		);
	}

}
