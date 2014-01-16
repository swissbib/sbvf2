<?php
namespace SwissbibTest\VuFindSearch\Backend\Summon;

use \PHPUnit_Framework_TestCase;

use \SerialsSolutions\Summon\Zend2 as Connector;
use \SerialsSolutions_Summon_Query as Query;

use \Zend\Config\Config;
use \Zend\Config\Reader\Ini;

use \VuFindSearch\Backend\Summon\Backend;

class BackendTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var Connector
     */
    protected $connector;



    /**
     * @return void
     */
    public function setUp()
    {
        $iniReader  = new Ini();
        $config     = new Config($iniReader->fromFile('../../../local/config/vufind/config.ini'));

        $this->connector = new Connector($config->get('Summon')->get('apiId'),$config->get('Summon')->get('apiKey'));
    }



    /**
     * @return void
     */
    public function testConnection()
    {
        try {
            $result = $this->connector->query(new Query('a'));
        } catch (Exception $e) {
            $this->fail("An error occured during the request.");
        }

        $this->assertTrue(!array_key_exists('errors',$result), "An error occured during the request.");
    }



    /**
     * @return void
     */
    public function testDataAmountMoreThanZero()
    {
        $result = $this->connector->query(new Query('a'));

        $this->assertTrue(count($result['documents']) > 0, "More than zero documents found.");
    }

} 