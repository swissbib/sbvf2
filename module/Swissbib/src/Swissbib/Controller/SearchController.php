<?php
/**
 * Default Controller
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
 * @link     http://vufind.org   Main Site
 */

/**
 * swissbib enhancements for Default Search Controller
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 1/1/13
 * Time: 1:23 PM
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
 * @category swissbib_VuFind2
 * @package  Controller
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
namespace Swissbib\Controller;

use VuFind\Controller\SearchController as VFSearchController,
    VuFind\Db\Table\Search as SearchTable, VuFind\Record, VuFind\Search\Memory,
    VuFind\Search\Options as SearchOptions, VuFind\Search\ResultScroller,
    Zend\Stdlib\Parameters;

/**
 * Redirects the user to the appropriate default VuFind action.
 *
 * @category swissbib_VuFind2
 * @package  Controller
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
class SearchController extends VFSearchController
{

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

        $paramsClass = $this->getParamsClass();
        $params = new $paramsClass();
        $params->recommendationsEnabled(true);

        // Send both GET and POST variables to search class:
        $params->initFromRequest(
            new Parameters(
                $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray()
            )
        );

        // Attempt to perform the search; if there is a problem, inspect any Solr
        // exceptions to see if we should communicate to the user about them.
        try {
            $resultsClass = $this->getResultsClass();
            $results = new $resultsClass($params);

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
            if ($this->rememberSearch) {
                $searchUrl = $this->url()->fromRoute($results->getSearchAction())
                    . $results->getUrl()->getParams(false);
                Memory::rememberSearch($searchUrl);
            }

            // Add to search history:
            if ($this->saveToHistory) {
                $user = $this->getUser();
                $sessId = $this->getServiceLocator()->get('SessionManager')->getId();
                $history = new SearchTable();
                $history->saveSearch(
                    $results, $sessId, $history->getSearches(
                        $sessId, isset($user->id) ? $user->id : null
                    )
                );
            }

            // Set up results scroller:
            if ($this->useResultScroller) {
                $this->resultScroller()->init($results);
            }
        } catch (\Exception $e) {
            // If it's a parse error or the user specified an invalid field, we
            // should display an appropriate message:
            if (method_exists($e, 'isParseError') && $e->isParseError()) {
                $view->parseError = true;

                // We need to create and process an "empty results" object to
                // ensure that recommendation modules and templates behave
                // properly when displaying the error message.
                $view->results = new \VuFind\Search\EmptySet\Results($params);
                $view->results->performAndProcessSearch();
            } else {
                // Unexpected error -- let's throw this up to the next level.
                throw $e;
            }
        }
        /* TODO
        // Save statistics:
        if ($this->logStatistics) {
            $statController = new VF_Statistics_Search();
            $statController->log($results, $this->getRequest());
        }
         */

        // Special case: If we're in RSS view, we need to render differently:
        if ($view->results->getView() == 'rss') {
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-type', 'text/xml');
            $feed = $this->getViewRenderer()->plugin('resultfeed');
            $response->setContent($feed($view->results)->export('rss'));
            return $response;
        }

        return $view;
    }

    protected function getResultsClass()
    {
        return 'Swissbib\Search\\' . $this->searchClassId . '\Results';
    }


}
