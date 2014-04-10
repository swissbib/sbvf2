<?php
namespace Swissbib\RecordDriver;

use \VuFind\RecordDriver\Missing as VFMissing;

class Missing extends VFMissing
{

    /**
     * Get short title
     * Override base method to assure a string and not an array
     *
     * @return    String
     */
    public function getTitle()
    {
        $title = parent::getTitle();

        if (is_array($title)) {
            $title = reset($title);
        }

        return $title;
    }



    /**
     * Get short title
     * Override base method to assure a string and not an array
     *
     * @return    String
     */
    public function getShortTitle()
    {
        $shortTitle = parent::getShortTitle();

        if (is_array($shortTitle)) {
            $shortTitle = reset($shortTitle);
        }

        return $shortTitle;
    }

    //GH
    //Missing Typ wird bei der Tag - Suche aus verschiedensten Kontexten aufgerufen (vor allem Helper)
    //@Oliver
    //moegliche Varianten
    //a) gib sinnvollere Wert zurück wie die von mir schnell hingeshriebenen
    //b) Erweiterung zu a) baue z.B. eine Loesung mit Interfaces die fuer von uns erstellten Treiber festlegen,
    //dass ein Minimum an Verhalten erforderlich ist
    //c) muss man mal nachdenken....

    public function getCorporationNames($asString = true)
    {
        return "";

    }

    public function getSecondaryAuthors($asString = true)
    {
        return "";

    }

    public function getPrimaryAuthor($asString = true)
    {
        return "";

    }

    public function getHostItemEntry()
    {
        return array();
    }

    public function getGroup()
    {
        return "";
    }

    public function getOnlineStatus()
    {
        return false;
    }

    public function getUnions()
    {
        return array();
    }

    public function getFormatsTranslated()
    {
        return "";
    }

    public function getFormatsRaw()
    {
        return parent::getFormats();
    }

}
