<?php
namespace Swissbib\Auth;

use VuFind\Auth\AbstractBase;
use VuFind\Exception\Auth as AuthException;
use VuFind\Config\Reader;
use VuFind\Db\Row\User;

use Swissbib\XServer\Client as XServerClient;


class XServer extends AbstractBase {

	/**
	 * @var	XServerClient
	 */
	protected $xServer;



	/**
	 * Attempt to authenticate the current user.  Throws exception if login fails.
	 *
	 * @param \Zend\Http\PhpEnvironment\Request $request Request object containing
	 * account credentials.
	 *
	 * @throws AuthException
	 * @return \VuFind\Db\Row\User Object representing logged-in user.
	 */
	public function authenticate($request) {
		$username	= $request->getPost('username');
		$password	= $request->getPost('password');

		if( $username == '' || $password == '' ) {
			throw new AuthException('authentication_error_blank');
		}

		$xServer	= $this->getXServer($username, $password);

		if( !$xServer->isValidLogin($username, $password) ) {
			throw new AuthException($xServer->getLoginException()->getMessage());
		}

		return $this->getUser($username);
	}



	/**
	 * Get xServer
	 *
	 * @param	String		$id
	 * @param	String		$verification
	 * @return	XServerClient
	 */
	protected function getXServer($id = '', $verification = '') {
		if( !$this->xServer ) {
			$config			= Reader::getConfig();
			$xServerUrl		= $config->XServer->url;
			$this->xServer	= new XServerClient($xServerUrl, null, $id, $verification);
		} elseif( $id ) {
			$this->xServer->setCredentials($id, $verification);
		}

		return $this->xServer;
	}



	/**
	 * Get user by username
	 * User is created in the database if not available
	 *
	 * @param	String		$username
	 * @return	User
	 */
	protected function getUser($username) {
		$user		= $this->getUserTable()->getByUsername($username);
		$xServer	= $this->getXServer();

		list($firstname, $lastname)	= explode(',', $xServer->getName());

		$user->username 	= $username;
		$user->firstname	= trim($firstname);
		$user->lastname		= trim($lastname);
		$user->email		= $xServer->getEmail();

		$user->save();

		return $user;
	}
}