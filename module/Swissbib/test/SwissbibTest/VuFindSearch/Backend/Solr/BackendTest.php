<?php
namespace SwissbibTest\VuFindSearch\Backend\Solr;

use \PHPUnit_Framework_TestCase;

use \VuFindSearch\Backend\Solr\Connector;
use \VuFindSearch\Backend\Solr\HandlerMap;
use \VuFindSearch\ParamBag;
use \VuFindSearch\Query\Query;
use \VuFindSearch\Backend\Solr\Response\Json\RecordCollection;

use \Zend\Config\Config;
use \Zend\Config\Reader\Ini;

use \Swissbib\VuFindSearch\Backend\Solr\Backend;

class BackendTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var string
     */
    protected $url;



    /**
     * @var string
     */
    protected $urlAdmin;



    /**
     * @return void
     */
    public function setUp()
    {
        $iniReader  = new Ini();
        $config     = new Config($iniReader->fromFile('../../../local/config/vufind/config.ini'));

        $this->url      = $config->get('Index')->get('url') . '/' . $config->get('Index')->get('default_core');
        $this->urlAdmin = $config->get('Index')->get('url') . '/admin';
    }



    /**
     * @return void
     */
    public function testConnection()
    {
        $connector  = $this->getConnector('admin');
        $paramBag   = new ParamBag();
        $paramBag->set('action',array('status'));
        $paramBag->set('wt',array('json'));

        $response       = $connector->search($paramBag);
        $responseArray  = json_decode($response,true);

        $this->assertTrue(array_key_exists('sb-biblio', $responseArray['status']), 'Connection to Solr-Core sb-biblio failed.');
    }



    /**
     * @return void
     */
    public function testResponseDataAmountMoreThanZero()
    {
        $backend    = new Backend($this->getConnector('select'));
        $result     = $backend->search(new Query(),0,100,$this->getParamBag());

        $this->assertTrue(0 < count($result->getRecords()),'Number of found Records is more than zero.');
    }



    /**
     * @return void
     */
    public function testResponseDataAmountBelowOrEqualToLimit()
    {
        $backend    = new Backend($this->getConnector('select'));
        $limit      = 5;
        $result     = $backend->search(new Query(),0,$limit,$this->getParamBag());

        $this->lessThanOrEqual(count($result->getRecords()),$limit,'Number of found Records is less or equal to Limit.');
    }



    /**
     * @return void
     */
    public function testResponseDataFormat()
    {
        $backend    = new Backend($this->getConnector('select'));
        $result     = $backend->search(new Query(),0,100,$this->getParamBag());

        $this->assertTrue($result instanceof RecordCollection, 'Response is of Type Json\RecordCollection.');
    }



    /**
     * @param string $name
     * @return Connector
     */
    protected function getConnector($name = 'select')
    {
        if ($name==='admin') {
            $url        = $this->urlAdmin;
            $handlerMap = new HandlerMap(array('cores' => array('fallback' => true)));
        } else {
            $url        = $this->url;
            $handlerMap = new HandlerMap(array('select' => array('fallback' => true)));
        }

        return new Connector($url,$handlerMap);
    }



    /**
     * @return ParamBag
     */
    protected function getParamBag()
    {
        $paramBag     = new ParamBag();

        $paramBag->set('q',array('a'));
        $paramBag->set('qf',array('title_short title_sub author series journals topic fulltext'));
        $paramBag->set('qt',array('edismax'));

        return $paramBag;
    }

} 