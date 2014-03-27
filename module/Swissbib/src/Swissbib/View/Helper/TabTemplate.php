<?php
namespace Swissbib\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Resolver\ResolverInterface;

/**
 * Search for templates with current tab postfix
 *
 * Example:
 * Base:   /path/to/template.phtml
 * Custom: /path/to/template.summon.phtml
 */
class TabTemplate extends AbstractHelper implements ServiceLocatorAwareInterface
{

    /** @var  ServiceLocatorInterface */
    protected $serviceLocator;

    /** @var ResolverInterface $resolver */
    protected $resolver;



    /**
     * Get tab specific template path if present
     *
     * @param    String        $baseTemplate
     * @param    String        $tab
     * @return    String
     */
    public function __invoke($baseTemplate, $tab = null)
    {
        if ($tab === null) {
            return $baseTemplate;
        }
        $tab               = strtolower($tab);
        $customTemplate       = str_replace('.phtml', '', $baseTemplate) . '-' . $tab;

        return $this->resolver->resolve($customTemplate) !== false ? $customTemplate : $baseTemplate;
    }



    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->resolver = $this->serviceLocator->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer')->resolver();
    }



    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

}
