<?php




namespace Swissbib\VuFind\Auth;


use VuFind\Auth\Shibboleth as VuFindShib;
use VuFind\Exception\Auth as AuthException;



class Shibboleth extends  VuFindShib{


    /**
     * VuFind Standard is looking for Server variables delivered by Shibboleth
     * IMHO this is impossible because the variables are only available after authentication
     * @return bool
     */
    public function isExpired()
    {
        //return parent::isExpired();
        //todo: we still have to solve some issues:
        //-- can we solve the REDIRECT_ proefix issue in Shibboleth Server variables? If yes, isExpired
        //could be implemented by looking up server variables

        //or another strategy might be to request the Shibbiloth sessionID
        //it all depends on the next evaluation (together with SWITCH)
        return false;
    }



}