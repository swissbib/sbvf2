<?php
namespace Swissbib\XServer;

use SimpleXMLElement;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Http\Response;

use Swissbib\XServer\Exception\Exception as xException;
use Swissbib\XServer\Exception\MissingCredentialsException;

/**
 * Client to connect to the x server with given credentials
 *
 */
class Client extends HttpClient {

	/**
	 * @var	Array	Credentials for login
	 */
	protected $credentials;

	/**
	 * @var	Array	Parsed response data (converted from xml)
	 */
	protected $responseData;

	/**
	 * @var	Boolean		Data loaded from server
	 */
	protected $loaded = false;

	/**
	 * @var
	 */
	protected $loginException;



	/**
	 * @param null $uri
	 * @param null $options
	 * @param string $id
	 * @param string $verification
	 */
	public function __construct($uri = null, $options = null, $id = '', $verification = '') {
		parent::__construct($uri, $options);

		if( $id ) {
			$this->setCredentials($id, $verification);
		}
	}



	/**
	 * Set xServer credentials
	 *
	 * @param	String		$id
	 * @param	String		$verification
	 */
	public function setCredentials($id, $verification) {
		$this->credentials = array(
			'id'			=> $id,
			'verification'	=> $verification
		);

			// new credentials require a new fetch
		$this->loaded = false;
	}



	/**
	 * Get user id from response
	 *
	 * @return	String
	 */
	public function getUserID() {
		return $this->getValue('z303', 'id');
	}



	/**
	 * Get name
	 *
	 * @return	String
	 */
	public function getName() {
		return $this->getValue('z303', 'name');
	}



	/**
	 * Get email
	 *
	 * @return	String
	 */
	public function getEmail() {
		return $this->getValue('z304', 'email-address');
	}


	public function getBasicData() {
		return array(
			'id'	=> $this->getUserID(),
			'name'	=> $this->getName(),
			'email'	=> $this->getEmail()
		);
	}


	public function isValidLogin($username, $password) {
		try {
			return !!$this->getUserID();
		} catch(xException $e) {
			$this->loginException = $e;
			return false;
		} catch(\Exception $e) {
			die($e->getMessage());
		}
	}



	/**
	 *
	 *
	 * @return	xException
	 */
	public function getLoginException() {
		return $this->loginException;
	}



	/**
	 * Get value from group and key part
	 *
	 * @param	String		$group
	 * @param	String		$key
	 * @return	String|null
	 */
	protected function getValue($group, $key) {
		if( !$this->loaded ) {
			$this->fetchData();
		}

		return isset($this->responseData[$group]) && isset($this->responseData[$group][$group . '-' . $key]) ? $this->responseData[$group][$group . '-' . $key] : null;
	}



	/**
	 * Fetch data from x server
	 *
	 * @throws	xException
	 * @return	Array
	 */
	protected function fetchData() {
		if( !$this->loaded ) {
			$response = $this->sendRequest();

			if( !$response->isSuccess() ) {
				throw new xException('xServer request failed: Connection status ' . $this->response->getReasonPhrase());
			}

			$this->loaded = true;

			$this->parseResponseData();

			if( isset($this->responseData['error']) ) {
				throw new xException('xServer request failed: ' . $this->responseData['error']);
			}
		}

		return $this->responseData;
	}



	/**
	 * Send request with defined parameters
	 *
	 * @return	Response
	 */
	protected function sendRequest() {
		$this->initParameters();

		$request	= $this->getRequest();
		$response	= $this->dispatch($request);

		return $response;
	}



	/**
	 * Parse response data
	 * Convert XML response into array structure
	 *
	 * @return	Array
	 */
	protected function parseResponseData() {
		$xml				= simplexml_load_string($this->response->getContent());
		$this->responseData	= $this->convertXmlToArray($xml);

		return $this->responseData;
	}



	/**
	 * Convert xml tree to array structure
	 *
	 * @param	SimpleXMLElement	$xml
	 * @return	Array
	 */
	protected function convertXmlToArray($xml) {
		$array = (array)$xml;

		foreach($array as $index => $value) {
			if( is_string($value) ) {
				continue;
			}

			if( !sizeof((array)$value) ) {
				$array[$index] = '';
				continue;
			}

			if( $value instanceof SimpleXMLElement || is_array($value) ) {
				$array[$index] = $this->convertXmlToArray($value);
			}
		}

		return $array;
	}



	/**
	 * Initialize credential request parameters
	 *
	 * @throws MissingCredentialsException
	 */
	protected function initParameters() {
		if( !is_array($this->credentials) ) {
			throw new MissingCredentialsException('No credentials set for xserver request. Use setCredentials()');
		}

		$this->setParameterGet(array(
			'op'			=> 'bor-auth',
			'bor-id'		=> $this->credentials['id'],
			'verification'	=> $this->credentials['verification']
		));
	}

}

