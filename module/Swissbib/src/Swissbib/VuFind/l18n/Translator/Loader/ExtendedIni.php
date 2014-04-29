<?php
/**
 * VuFind Translate Adapter ExtendedIni
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @category VuFind2
 * @package  Translator
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace Swissbib\VuFind\l18n\Translator\Loader;
use Zend\I18n\Exception\InvalidArgumentException,
    Zend\I18n\Translator\Loader\FileLoaderInterface,
    Zend\I18n\Translator\TextDomain,
    VuFind\I18n\Translator\Loader\ExtendedIni as VFExtendedIni;



/**
 * Handles the language loading and language file parsing
 *
 * @category VuFind2
 * @package  Translator
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class ExtendedIni extends VFExtendedIni
{
    /**
     * List of files loaded during the current run -- avoids infinite loops and
     * duplicate loading.
     *
     * @var array
     */
    protected $loadedFiles = array();

    /**
     * Constructor
     *
     * @param array  $pathStack      List of directories to search for language
     * files.
     * @param string $fallbackLocale Fallback locale to use for language strings
     * missing from selected file.
     */
    public function __construct($pathStack = array(), $fallbackLocale = null)
    {

        parent::__construct($pathStack,$fallbackLocale);

    }

    /**
     * load(): defined by LoaderInterface.
     *
     * @param string $locale   Locale to read from language file
     * @param string $filename Language file to read (not used)
     *
     * @return TextDomain
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($locale, $filename)
    {
        // Reset the loaded files list:
        $this->resetLoadedFiles();

        // Load base data:
        //VuFind itself doesn't use at all the filename information itself
        //we are running into problems with domain entities having the same name but being part of different domains
        //specialized domains are registered in Swissbib\Bootstraper->initSpecialTranslations
        //todo: discuss this with VuFind list!

        $data =  !isset($filename) ?    $this->loadLanguageFile($locale . '.ini') : $this->loadLanguageFile($filename );
        //$data =  $this->loadLanguageFile($locale . '.ini');

        // Load fallback data, if any:
        if (!empty($this->fallbackLocale)) {
            $newData = $this->loadLanguageFile($this->fallbackLocale . '.ini');
            $newData->merge($data);
            $data = $newData;
        }

        return $data;
    }



    /**
     * Search the path stack for language files and merge them together.
     *
     * @param string $filename Name of file to search path stack for.
     *
     * @return TextDomain
     */
    protected function loadLanguageFile($filename)
    {
        // Don't load a file that has already been loaded:
        if ($this->checkAndMarkLoadedFile($filename)) {
            return new TextDomain();
        }

        if (file_exists($filename)) {
            $data = $this->languageFileToTextDomain($filename);
        } else {


            $data = false;
            foreach ($this->pathStack as $path) {
                if (file_exists($path . '/' . $filename)) {
                    $current = $this->languageFileToTextDomain($path . '/' . $filename);
                    if ($data === false) {
                        $data = $current;
                    } else {
                        $data->merge($current);
                    }
                }
            }
        }
        if ($data === false) {
            throw new InvalidArgumentException("Ini file '{$filename}' not found");
        }

        // Load parent data, if necessary:
        return $this->loadParentData($data);
    }

}
