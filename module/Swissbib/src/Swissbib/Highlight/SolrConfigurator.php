<?php
namespace Swissbib\Highlight;

use Zend\Config\Config;
use Zend\EventManager\EventInterface;
use Zend\EventManager\SharedEventManagerInterface;

use VuFindSearch\Backend\Solr\Backend;
use VuFind\Search\Memory as VFMemory;

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


    protected $memory;



    /**
     * Initialize with event manager and highlight config
     *
     * @param SharedEventManagerInterface $eventsManager
     * @param Config                      $config
     */
    public function __construct(SharedEventManagerInterface $eventsManager, Config $config,
                                VFMemory $memory)
    {
        $this->eventsManager = $eventsManager;
        $this->config        = $config;
        $this->memory        = $memory;
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
     * @param    EventInterface    $event
     * @return    EventInterface
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

                        // Add hl.q for non query events
                    if (!$event->getParam('query', false)) {
                        $lastSearch = $this->memory->retrieve();
                        if ($lastSearch) {
                            $urlParams = parse_url($lastSearch);
                            parse_str($urlParams['query'], $queryParams);

                            if (isset($queryParams['lookfor'])) {
                                $params->set('hl.q', '*:"' . addslashes($queryParams['lookfor']) . '"');
                            }
                        }
                    }

                        // All all highlight config fields
                    foreach ($this->config as $key => $value) {
                        $params->set('hl.' . $key, $value);
                    }
                }
            }
        }

        return $event;
    }
}
