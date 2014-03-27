<?php
namespace Swissbib\Controller;

use Zend\Config\Config;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\ResolverInterface;

use VuFind\Controller\AbstractBase as BaseController;

/**
 * Display help pages located in templates/HelpPages/LOCALE/*
 *
 */
class HelpPageController extends BaseController
{

    /** @var  ResolverInterface */
    protected $resolver;



    /**
     * Main entry for help pages
     *
     * @return    ViewModel
     * @throws    \Exception
     */
    public function indexAction()
    {
        $topic    = $this->params()->fromRoute('topic');
        $template = $this->getTemplate($topic);

        if (!$template['template']) {
            throw new \Exception('Can\'t find matching help page');
        }

        $helpContent = $this->createViewModel();
        $helpContent->setTemplate($template['template']);

        $helpLayout = $this->createViewModel(array(
                                              'pages'    => $this->getPages(),
                                              'first'    => !!$template['first'],
                                              'topic'    => strtolower($template['topic'])
                                         ));
        $helpLayout->setTemplate('HelpPage/layout');
        $helpLayout->addChild($helpContent, 'helpContent');

            // Set Solr search for help pages
        $this->layout()->setVariable('searchClassId', 'Solr');
        $this->layout()->setVariable('pageClass', 'template_page');

        return $helpLayout;
    }



    /**
     * Find matching template
     * Fall back to search topic and english if not available
     *
     * @param    String|null        $topic
     * @return    Array    [template,first]
     */
    protected function getTemplate($topic)
    {
        /** @var ResolverInterface $resolver */
        $resolver    = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer')->resolver();
        $language    = $this->serviceLocator->get('VuFind\Translator')->getLocale();
        $template    = null;
        $activeTopic = null;
        $firstMatch  = true;
        $topic         = $topic ? strtolower($topic) : $this->getDefaultTopic();

        $languages = array($language, 'en');
        $topics    = array($topic, 'search');

        foreach ($languages as $language) {
            foreach ($topics as $topic) {
                $path = 'HelpPage/' . $language . '/' . $topic;

                if ($resolver->resolve($path) !== false) {
                    $template    = $path;
                    $activeTopic = $topic;
                    break 2;
                }

                $firstMatch = false;
            }
        }

        return array(
            'template' => $template,
            'first'    => $firstMatch,
            'topic'    => $activeTopic
        );
    }



    /**
     * Get default topic
     * Get first item of pages
     *
     * @return    String
     */
    protected function getDefaultTopic()
    {
        $pages    = $this->getPages();

        return isset($pages[0]) ? $pages[0] : 'about';
    }



    /**
     * Get current active language
     *
     * @return    String
     */
    protected function getLanguage()
    {
        return $this->serviceLocator->get('VuFind\Translator')->getLocale();
    }



    /**
     * Get available pages
     *
     * @return    String[]
     */
    protected function getPages()
    {
        $config = $this->serviceLocator->get('VuFind/Config')->get('config');
        $pages    = array();

        if ($config) {
            if ($config->HelpPages && $config->HelpPages->pages) {
                $pages = $config->HelpPages->pages->toArray();
            }
        }

        return $pages;
    }
}
