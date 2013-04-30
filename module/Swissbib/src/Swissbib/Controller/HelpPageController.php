<?php
namespace Swissbib\Controller;

use Zend\Config\Config;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\ResolverInterface;

/**
 * Display help pages located in templates/HelpPages/LOCALE/*
 *
 */
class HelpPageController extends AbstractActionController
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

		$content = new ViewModel();
		$content->setTemplate($template['template']);

		$layout = new ViewModel();
		$layout->setTemplate('HelpPage/layout');
		$layout->setVariable('pages', $this->getPages());
		$layout->setVariable('first', !!$template['first']);
		$layout->setVariable('topic', strtolower($template['topic']));

		$layout->addChild($content, 'helpContent');

		return $layout;
	}



	/**
	 * Find matching template
	 * Fall back to search topic and english if not available
	 *
	 * @param    String        $topic
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
		return $this->serviceLocator->get('VuFind/Config')->get('config')->HelpPages->pages->toArray();
	}
}
