<?php

/**
 * Prefix rendered output with HTML comment stating filename of the rendered template
 */

namespace Swissbib\Filter;

use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Filter\AbstractFilter;

/**
 * Class SbTemplateFilenameFilter
 *
 * Content filter to wrap rendered template content with HTML comments,
 * indicating which template file implemented a section of output.
 * Every template section is wrapped by comments to mark the 1) beginning and 2) ending
 * of a template file.
 *
 * Activating the filter: call SbTemplateFilenameFilter::onBootstrap() from within the
 * onBootstap() method of the module class (\path\to\module\Namespace\Module.php)
 *
 * @package    Swissbib\Filter
 */
class TemplateFilenameFilter extends AbstractFilter implements ServiceLocatorAwareInterface
{

    protected $serviceLocator;


    /**
     * @param    Mixed    $content
     * @return    Mixed|String
     */
    public function filter($content)
    {
        $sm = $this->getServiceLocator();
        /** @var $phpRenderer \Zend\View\Renderer\PhpRenderer */
        $phpRenderer = $sm->get('Zend\View\Renderer\PhpRenderer');

        // Fetch private property PhpRenderer::__file via reflection
        $rendererReflection = new \ReflectionObject($phpRenderer);

        $fileProperty = $rendererReflection->getProperty('__file');
        $fileProperty->setAccessible(true);
        $templateFilename = $fileProperty->getValue($phpRenderer);

        // Don't wrap export stuff
        if ( (stristr($templateFilename, 'export-') !== false) ||
            (stristr($templateFilename, '/email/') !== false) ||
            (stristr($templateFilename, '/link') !== false)
        ) {
            return $content;
        }

        // Remove possibly confidential server details from path
        $directoryDelimiter = 'themes' . DIRECTORY_SEPARATOR;
        $templateFilename   = substr($templateFilename, strpos($templateFilename, $directoryDelimiter)+7);

        return $this->wrapContentWithComment($content, $templateFilename, '');
    }



    /**
     * @param    String    $content
     * @param    String    $templateFilename
     * @return    String
     */
    private function wrapContentWithComment($content, $templateFilename)
    {
        $templateFilename    = str_replace('\\', '/', $templateFilename);
        $isStartOfHtml         = strstr($content, '<html') !== false || strstr($content, '<xml') !== false;

        return $isStartOfHtml ? $content :
                "\n" . '<!-- Begin' . (!empty($type) ? ' ' . $type : '') . ': ' . $templateFilename . ' -->'
                . "\n" . $content
                . "\n" . '<!-- End: ' . $templateFilename . ' -->'
                . "\n";
    }



    /**
     * @param    ServiceLocatorInterface    $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }



    /**
     * @return    ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
