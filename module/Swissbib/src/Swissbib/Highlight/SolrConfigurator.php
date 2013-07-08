<?php
namespace Swissbib\Highlight;

use Zend\Config\Config;
use Zend\EventManager\EventInterface;
use Zend\EventManager\SharedEventManagerInterface;

use VuFindSearch\Backend\Solr\Backend;

/**
 * Allow configuration of solr highlighting mechanism
 *
 */
class SolrConfigurator
{

	/**
	 * @var    Backend
	 */
	protected $backend;

	/** @var Config */
	protected $config;

	/** @var SharedEventManagerInterface */
	protected $eventsManager;



	public function __construct(SharedEventManagerInterface $eventsManager, Config $config)
	{
		$this->eventsManager = $eventsManager;
		$this->config        = $config;
	}



	/**
	 * Attach event for backend
	 *
	 * @param    Backend $backend
	 */
	public function attach(Backend $backend)
	{
		$this->backend = $backend;

		$this->eventsManager->attach('VuFind\Search', 'pre', array($this, 'onSearchPre'), -100);
	}



	/**
	 * Handle event. Add config values
	 *
	 * @param	EventInterface	$event
	 * @return	EventInterface
	 */
	public function onSearchPre(EventInterface $event)
	{
		$backend = $event->getTarget();

		if ($backend === $this->backend) {
			$params = $event->getParam('params');
			if ($params) {
				// Set highlighting parameters unless explicitly disabled:
				$hl = $params->get('hl');
				if (!isset($hl[0]) || $hl[0] != 'false') {
					foreach ($this->config as $key => $value) {
						$params->set('hl.' . $key, $value);
					}
				}
			}
		}
		return $event;
	}
}
