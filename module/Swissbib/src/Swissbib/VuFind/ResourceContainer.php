<?php
namespace Swissbib\VuFind;

use VuFindTheme\ResourceContainer as VfResourceContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

class ResourceContainer extends VfResourceContainer implements ServiceLocatorAwareInterface
{

    /**
     * @var    ServiceLocatorInterface
     */
    protected $serviceLocator;


    /**
     * @var    String[]        List of ignore patterns
     */
    protected $ignoredCssFiles;


    /**
     * @var    String[]        List of ignore patterns
     */
    protected $ignoredJsFiles;



    /**
     * Inject service locator
     *
     * @param    ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $config               = new Config($serviceLocator->get('Config'));
        //$this->ignoredFiles   = $config->swissbib->ignore_assets->toArray();

        $this->ignoredCssFiles = $config->swissbib->ignore_css_assets->toArray();
        $this->ignoredJsFiles  = $config->swissbib->ignore_js_assets->toArray();
    }



    /**
     * Get service locator
     *
     * @return    ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }



    /**
     * Remove ignored file before they're added to the resources
     *
     * @param    Array|String $css
     */
    public function addCss($css)
    {
        $css = $this->removeIgnoredFiles($css, $this->ignoredCssFiles);

        parent::addCss($css);
    }



    /**
     * Remove ignored js file before they're added to the resources
     *
     * @param array|string $js Javascript file (or array of files) to add (possibly
     *                         with extra settings from theme config appended to each filename string).
     *
     * @return void
     */
    public function addJs($js)
    {
        $js = $this->removeIgnoredFiles($js, $this->ignoredJsFiles);

        parent::addJs($js);
    }



    /**
     * Remove files which are on the ignore list
     *
     * @param    String[]    $resourcesToInspect
     * @param    String[]    $resourcesToIgnore
     * @return    String[]
     */
    protected function removeIgnoredFiles($resourcesToInspect, $resourcesToIgnore)
    {
        if (!is_array($resourcesToInspect) && !is_a($resourcesToInspect, 'Traversable')) {
            $resourcesToInspect = array($resourcesToInspect);
        }


        foreach ($resourcesToIgnore as $ignorePattern) {
            foreach ($resourcesToInspect as $index => $file) {
                if (preg_match($ignorePattern, $file) /*stristr($file, $ignorePattern) !== false*/) {
                    // File matches ignore pattern
                    unset($resourcesToInspect[$index]);
                }
            }
        }

        return $resourcesToInspect;
    }
}
