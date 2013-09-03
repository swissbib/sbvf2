<?php




namespace Swissbib\VuFind\Auth;


use VuFind\Auth\Shibboleth as VuFindShib;



class Shibboleth extends  VuFindShib{


    /**
     * VuFind Standard is looking for Server variables delivered by Shibboleth
     * IMHO this is impossible because the variables are only available after authentication
     * @return bool
     */
    public function isExpired()
    {
        //return parent::isExpired();
        return false;
    }

}