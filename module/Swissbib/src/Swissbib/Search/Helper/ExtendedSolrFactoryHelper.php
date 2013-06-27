<?php
 
 /**
 * helper type to provide functionality for factories reponsible for the creation of the extended Solr targets
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 5/11/13
 * Time: 1:53 PM
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
 * @package  Swissbib\Search\Helper
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */


namespace Swissbib\Search\Helper;


use Zend\ServiceManager\ServiceLocatorInterface;


class ExtendedSolrFactoryHelper {



    /**
     * @var array contains the configuration list for targets which should be extended by swissbib
     *
     */
    protected $extendedTargets = array();




    /**
     *
     * helper function to decide if the results type to be created should be extended by swissbib
     * note: guess it isn't necessary to make this evaluation here again because it is already done in the extended swissbib controller
     * but for the sake of better security and stability
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function isExtendedTarget (ServiceLocatorInterface $serviceLocator,
                                         $name, $requestedName) {


        //speed it up
        //I guess reading the config every time the factory and the helper is requested slows down the performance
        if (empty ($this->extendedTargets)) {
            $tExtended = $serviceLocator->getServiceLocator()->get('Vufind\Config')->get('config')->SwissbibSearchExtensions->extendedTargets;

            if (!empty($tExtended)) {
                $this->extendedTargets = explode(",", $tExtended);

                array_walk($this->extendedTargets, function(&$v) {
                    $v = strtolower($v);
                });
            }

        }

        return  !empty($this->extendedTargets)  && in_array(strtolower($name),$this->extendedTargets);

    }

    public function getNamespace (ServiceLocatorInterface $serviceLocator,
                                      $name, $requestedName) {

        if ($this->isExtendedTarget($serviceLocator,$name,$requestedName)) {
            return 'Swissbib\Search';
        } else {
            return  'VuFind\Search';
        }

    }


}