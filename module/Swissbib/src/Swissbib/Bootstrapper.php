<?php


namespace Swissbib;

use VuFind\Config\Reader as ConfigReader,
    Zend\Console\Console, Zend\Mvc\MvcEvent, Zend\Mvc\Router\Http\RouteMatch;


/**
 * swissbib / VuFind <<full descriptive name of the class>>
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 1/30/13
 * Time: 10:30 PM
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category swissbib_VuFind2
 * @package  <<name of package>>
 * @author   << name of author  <mail of author> >>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     << link to further documentation related to this resource type (Wiki, tracker ...)
 */


class Bootstrapper
{

    protected $config;
    protected $event;
    protected $events;

    public function __construct(MvcEvent $event) {

        $this->config = ConfigReader::getConfig();
        $this->event = $event;
        $this->events = $event->getApplication()->getEventManager();

    }


    public function bootstrap() {

        $methods = get_class_methods($this);

        foreach ($methods as $method) {
            if (substr($method,0,4) == "init") {
                $this->$method();
            }
        }


    }


    public function initSwissbibDBs(){
        //echo "halo";
    }

    /**
     * Set up plugin managers.
     *
     * @return void
     */
    protected function initPluginManagers()
    {
        $app = $this->event->getApplication();
        $serviceManager = $app->getServiceManager();
        $config = $app->getConfig();

        // Use naming conventions to set up a bunch of services based on namespace:
        $namespaces = array(
            'Db\Table'
        );
        foreach ($namespaces as $ns) {
            $serviceName = 'Swissbib\\' . str_replace('\\', '', $ns) . 'PluginManager';
            $factory = function ($sm) use ($config, $ns) {
                $className = 'Swissbib\\' . $ns . '\PluginManager';
                $configKey = strtolower(str_replace('\\', '_', $ns));
                return new $className(
                    new \Zend\ServiceManager\Config(
                        $config['swissbib']['plugin_managers'][$configKey]
                    )
                );
            };
            $serviceManager->setFactory($serviceName, $factory);
        }

    }



}
