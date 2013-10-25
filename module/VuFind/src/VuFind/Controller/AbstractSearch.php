<?php
/**
 * VuFind Search Controller
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
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
namespace VuFind\Controller;
use Zend\Stdlib\Parameters;

/**
 * VuFind Search Controller
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
class AbstractSearch extends AbstractBase
{
    /**
     * Search class family to use.
     *
     * @var string
     */
    protected $searchClassId = 'Solr';

    /**
     * Should we save searches to history?
     *
     * @var bool
     */
    protected $saveToHistory = true;

    /**
     * Should we log search statistics?
     *
     * @var bool
     */
    protected $logStatistics = true;

    /**
     * Should we remember the search for breadcrumb purposes?
     *
     * @var bool
     */
    protected $rememberSearch = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Placeholder so child classes can call parent::__construct() in case
        // of future global behavior.
    }

    /**
     * Create a new ViewModel.
     *
     * @param array $params Parameters to pass to ViewModel constructor.
     *
     * @return ViewModel
     */
    protected function createViewModel($params = null)
    {
        $view = parent::createViewModel($params);
        $view->searchClassId = $this->searchClassId;
        return $view;
    }

    /**
     * Handle an advanced search
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function advancedAction()
    {
        $view = $this->createViewModel();
        $view->options = $this->getServiceLocator()
            ->get('VuFind\SearchOptionsPluginManager')->get($this->searchClassId);
        if ($view->options->getAdvancedSearchAction() === false) {
            throw new \Exception('Advanced search not supported.');
        }

        // Handle request to edit existing saved search:
        $view->saved = false;
        $searchId = $this->params()->fromQuery('edit', false);
        if ($searchId !== false) {
            $view->saved = $this->restoreAdvancedSearch($searchId);
        }

        return $view;
    }

    /**
     * Given a saved search ID, redirect the user to the appropriate place.
     *
     * @param int $id ID from search history
     *
     * @return mixed
     */
    protected function redirectToSavedSearch($id)
    {
        $table = $this->getTable('Search');
        $search = $table->getRowById($id);

        // Found, make sure the user has the rights to view this search
        $sessId = $this->getServiceLocator()->get('VuFind\SessionManager')->getId();
        $user = $this->getUser();
        $userId = $user ? $user->id : false;
        if ($search->session_id == $sessId || $search->user_id == $userId) {
            // They do, deminify it to a new object.
            $minSO = $search->getSearchObject();
            $savedSearch = $minSO->deminify($this->getResultsManager());

            // Now redirect to the URL associated with the saved search; this
            // simplifies problems caused by mixing different classes of search
            // object, and it also prevents the user from ever landing on a
            // "?saved=xxxx" URL, which may not persist beyond the current session.
            // (We want all searches to be persistent and bookmarkable).
            $details = $savedSearch->getOptions()->getSearchAction();
            $url = $this->url()->fromRoute($details);
            $url .= $savedSearch->getUrlQuery()->getParams(false);
            return $this->redirect()->toUrl($url);
        } else {
            // They don't
            // TODO : Error handling -
            //    User is trying to view a saved search from another session
            //    (deliberate or expired) or associated with another user.
            throw new \Exception("Attempt to access invalid search ID");
        }
    }

    /**
     * Is the result scroller active?
     *
     * @return bool
     */
    protected function resultScrollerActive()
    {
        // Disabled by default:
        return false;
    }

    /**
     * Store the URL of the provided search (if appropriate).
     *
     * @param \VuFind\Search\Base\Results $results Search results object
     *
     * @return void
     */
    protected function rememberSearch($results)
    {
        if ($this->rememberSearch) {
            $searchUrl = $this->url()->fromRoute(
                $results->getOptions()->getSearchAction()
            ) . $results->getUrlQuery()->getParams(false);
            $this->getSearchMemory()->rememberSearch($searchUrl);
        }
    }

    /**
     * Send search results to results view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function resultsAction()
    {
        $view = $this->createViewModel();

        // Handle saved search requests:
        $savedId = $this->params()->fromQuery('saved', false);
        if ($savedId !== false) {
            return $this->redirectToSavedSearch($savedId);
        }

        $results = $this->getResultsManager()->get($this->searchClassId);
        $params = $results->getParams();
        $params->recommendationsEnabled(true);

        // Send both GET and POST variables to search class:
        $params->initFromRequest(
            new Parameters(
                $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray()
            )
        );

        // Make parameters available to the view:
        $view->params = $params;

        // Attempt to perform the search; if there is a problem, inspect any Solr
        // exceptions to see if we should communicate to the user about them.
        try {
            // Explicitly execute search within controller -- this allows us to
            // catch exceptions more reliably:
            $results->performAndProcessSearch();

            // If a "jumpto" parameter is set, deal with that now:
            if ($jump = $this->processJumpTo($results)) {
                return $jump;
            }

            // Send results to the view and remember the current URL as the last
            // search.
            $view->results = $results;
            $this->rememberSearch($results);

            // Add to search history:
            if ($this->saveToHistory) {
                $user = $this->getUser();
                $sessId = $this->getServiceLocator()->get('VuFind\SessionManager')
                    ->getId();
                $history = $this->getTable('Search');
                $history->saveSearch(
                    $this->getResultsManager(), $results, $sessId,
                    $history->getSearches(
                        $sessId, isset($user->id) ? $user->id : null
                    )
                );
            }

            // Set up results scroller:
            if ($this->resultScrollerActive()) {
                $this->resultScroller()->init($results);
            }
        } catch (\VuFindSearch\Backend\Exception\BackendException $e) {
            if ($e->hasTag('VuFind\Search\ParserError')) {
                // If it's a parse error or the user specified an invalid field, we
                // should display an appropriate message:
                $view->parseError = true;

                // We need to create and process an "empty results" object to
                // ensure that recommendation modules and templates behave
                // properly when displaying the error message.
                $view->results = $this->getResultsManager()->get('EmptySet');
                $view->results->setParams($params);
                $view->results->performAndProcessSearch();
            } else {
                throw $e;
            }
        }
        // Save statistics:
        if ($this->logStatistics) {
            $this->getServiceLocator()->get('VuFind\SearchStats')
                ->log($results, $this->getRequest());
        }

        // Special case: If we're in RSS view, we need to render differently:
        if (isset($view->results)
            && $view->results->getParams()->getView() == 'rss'
        ) {
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-type', 'text/xml');
            $feed = $this->getViewRenderer()->plugin('resultfeed');
            $response->setContent($feed($view->results)->export('rss'));
            return $response;
        }

        return $view;
    }

    /**
     * Process the jumpto parameter -- either redirect to a specific record and
     * return view model, or ignore the parameter and return false.
     *
     * @param \VuFind\Search\Base\Results $results Search results object.
     *
     * @return bool|\Zend\View\Model\ViewModel
     */
    protected function processJumpTo($results)
    {
        // Missing/invalid parameter?  Ignore it:
        $jumpto = $this->params()->fromQuery('jumpto');
        if (empty($jumpto) || !is_numeric($jumpto)) {
            return false;
        }

        // Parameter out of range?  Ignore it:
        $recordList = $results->getResults();
        if (!isset($recordList[$jumpto - 1])) {
            return false;
        }

        // If we got this far, we have a valid parameter so we should redirect
        // and report success:
        $details = $this->getRecordRouter()
            ->getTabRouteDetails($recordList[$jumpto - 1]);
        return $this->redirect()->toRoute($details['route'], $details['params']);
    }

    /**
     * Either assign the requested search object to the view or display a flash
     * message indicating why the operation failed.
     *
     * @param string $searchId ID value of a saved advanced search.
     *
     * @return bool|object     Restored search object if found, false otherwise.
     */
    protected function restoreAdvancedSearch($searchId)
    {
        // Look up search in database and fail if it is not found:
        $searchTable = $this->getTable('Search');
        $search = $searchTable->select(array('id' => $searchId))->current();
        if (empty($search)) {
            $this->flashMessenger()->setNamespace('error')
                ->addMessage('advSearchError_notFound');
            return false;
        }

        // Fail if user has no permission to view this search:
        $user = $this->getUser();
        $sessId = $this->getServiceLocator()->get('VuFind\SessionManager')->getId();
        if ($search->session_id != $sessId && $search->user_id != $user->id) {
            $this->flashMessenger()->setNamespace('error')
                ->addMessage('advSearchError_noRights');
            return false;
        }

        // Restore the full search object:
        $minSO = $search->getSearchObject();
        $savedSearch = $minSO->deminify($this->getResultsManager());

        // Fail if this is not the right type of search:
        if ($savedSearch->getParams()->getSearchType() != 'advanced') {
            $this->flashMessenger()->setNamespace('error')
                ->addMessage('advSearchError_notAdvanced');
            return false;
        }

        // Activate facets so we get appropriate descriptions in the filter list:
        $savedSearch->getParams()->activateAllFacets('Advanced');

        // Make the object available to the view:
        return $savedSearch;
    }

    /**
     * Convenience method for accessing results
     *
     * @return \VuFind\Search\Results\PluginManager
     */
    protected function getResultsManager()
    {
        return $this->getServiceLocator()->get('VuFind\SearchResultsPluginManager');
    }
}