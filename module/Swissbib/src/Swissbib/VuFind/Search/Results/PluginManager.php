<?php
namespace Swissbib\VuFind\Search\Results;

use VuFind\Search\Results\PluginManager as VuFindSearchResultsPluginManager;

/**
 * swissbib (service) Manager responsible for a factory to create an extended Solr Results type
 * customized ResultsPluginManger has to extend Vufind\Search\Results\PluginManger and not directly
 * Vufind\ServiceManager\AbstractPluginManger because type ResultsPluginManger is expected in (some ?) other methods e.g.
 * Vufind\Db\Table\Search->saveSearch()
 *
 */
class PluginManager extends VuFindSearchResultsPluginManager
{
}
