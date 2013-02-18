<?php
namespace Swissbib\VuFind\ILS\Driver;

use VuFind\ILS\Driver\Aleph as AlephDriver;
use \SimpleXMLElement;
use VuFind\Exception\ILS as ILSException;

class Aleph extends AlephDriver {

	/**
	 * Fix xserver port problem
	 *
	 * @param	String		$op
	 * @param	Array		$params
	 * @param	Boolean		$auth
	 * @return SimpleXMLElement
	 * @throws ILSException
	 * @throws \Exception
	 */
	protected function doXRequest($op, $params, $auth=false) {
		if( isset($this->config['xServer']['port']) ) {
			$port	= $this->config['xServer']['port'];
			$auth	= isset($this->config['xServer']['auth']) ? !!$this->config['xServer']['auth'] : $auth;

			try {
				$oldHost	= $this->host;
				$this->host	.= ':' . $port;

				$returnValue= parent::doXRequest($op, $params, $auth);

				$this->host	= $oldHost;

				return $returnValue;
			} catch(\Exception $e) {
					// Reset host to leave it clear
				$this->host	= $oldHost;
					// Go on with exception
				throw $e;
			}
		} else {
				// Normal handling
			return parent::doXRequest($op, $params, $auth);
		}
	}

}