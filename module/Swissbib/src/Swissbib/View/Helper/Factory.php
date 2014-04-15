<?php
/**
 * Factory for view helpers.
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
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
 * @category swissbib VuFind2
 * @package  Swissbib\View\Helper
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */



namespace Swissbib\View\Helper;
use Zend\ServiceManager\ServiceManager;
use Swissbib\View\Helper\RedirectProtocolWrapper as RedirectProtocolWrapperHelper;


/**
 * Factory for swissbib specific view helpers.
 *
 * @category swissbib VuFind2
 * @package  Controller
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory
{

    /**
     * @param ServiceManager $sm
     * @return InstitutionSorter
     */
    public static function getInstitutionSorter(ServiceManager $sm)
    {
        /** @var Config $relationConfig */
        $relationConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('libadmin-groups');
        $institutionList = array();

        if ($relationConfig->count() !== null) {
            $institutionList = array_keys($relationConfig->institutions->toArray());
        }

        return new InstitutionSorter($institutionList);
    }

    /**
     * @param ServiceManager $sm
     * @return ExtractFavoriteInstitutionsForHoldings
     */
    public static function getFavoriteInstitutionsExtractor (ServiceManager $sm)
    {
        /** @var \Swissbib\Favorites\Manager $favoriteManager */
        $favoriteManager = $sm->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
        $userInstitutionCodes = $favoriteManager->getUserInstitutions();

        return new ExtractFavoriteInstitutionsForHoldings($userInstitutionCodes);

    }


    /**
     * @param ServiceManager $sm
     * @return InstitutionDefinedAsFavorite
     */
    public static function getInstitutionsAsDefinedFavorites(ServiceManager $sm)
    {
        $dataSource = $sm->getServiceLocator()->get('Swissbib\FavoriteInstitutions\DataSource');
        $tInstitutions = $dataSource->getFavoriteInstitutions();
        return new InstitutionDefinedAsFavorite($tInstitutions);

    }


    /**
     * @param ServiceManager $sm
     * @return QrCode
     */
    public static function getQRCodeHelper(ServiceManager $sm)
    {
        $qrCodeService = $sm->getServiceLocator()->get('Swissbib\QRCode');
        return new QrCode($qrCodeService);

    }


    /**
     * @param ServiceManager $sm
     * @return IsFavoriteInstitution
     */
    public static function isFavoriteInstitutionHelper(ServiceManager $sm)
    {
        /** @var \Swissbib\Favorites\Manager $favoriteManager */
        $favoriteManager = $sm->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
        $userInstitutionCodes = $favoriteManager->getUserInstitutions();

        return new IsFavoriteInstitution($userInstitutionCodes);

    }


    /**
     * @param ServiceManager $sm
     * @return DomainURL
     */
    public static function getDomainURLHelper(ServiceManager $sm)
    {
        $locator = $sm->getServiceLocator();
        return new DomainURL($locator->get('Request'));

    }


    public static function getRedirectProtocolWrapperHelper(ServiceManager $sm)
    {
        $locator = $sm->getServiceLocator();
        return new  RedirectProtocolWrapperHelper($locator->get('Swissbib\Services\RedirectProtocolWrapper'));

    }


}