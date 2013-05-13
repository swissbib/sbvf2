<?php
/**
 * swissbib / VuFind enhancements to extend the VuFind Options type for the Solr target
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 11/05/13
 * Time: 4:09 PM
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
 * @package  Swissbib\Search\Options
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

namespace Swissbib\Search\Options;

use VuFind\Search\Options\PluginFactory as VFOptionsPluginFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Swissbib\Search\Helper\ExtendedSolrFactoryHelper;

class PluginFactory extends VFOptionsPluginFactory{


    /**
     * @var \Swissbib\Search\Helper\ExtendedSolrFactoryHelper utility class
     */
    protected $factoryHelper;




    /**
     * Constructor
     */
    public function __construct()
    {

        parent::__construct();
        $this->factoryHelper  = new ExtendedSolrFactoryHelper();


    }


    /**
     * Can we create a service for the specified name?
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     * @param string                  $name           Name of service
     * @param string                  $requestedName  Unfiltered name of service
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator,
                                             $name, $requestedName) {

        $this->defaultNamespace = $this->factoryHelper->getNamespace($serviceLocator,$name, $requestedName);
        return parent::canCreateServiceWithName($serviceLocator,$name,$requestedName);

    }


    /**
     * Create a service for the specified name.
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     * @param string                  $name           Name of service
     * @param string                  $requestedName  Unfiltered name of service
     *
     * @return object
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator,
                                          $name, $requestedName) {

        $class = $this->getClassName($name, $requestedName);
        return new $class(
            $serviceLocator->getServiceLocator()->get('VuFind\Config')
        );
    }


}