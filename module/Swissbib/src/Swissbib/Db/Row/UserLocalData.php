<?php

namespace Swissbib\Db\Row;

use Zend\Db\RowGateway\RowGateway;

/**
 * Row Definition for user_localdata
 *
 * @category Swissbib
 * @package  Db_Row
 */
class UserLocalData extends RowGateway
{

	/**
	 * Constructor
	 *
	 * @param \Zend\Db\Adapter\Adapter $adapter Database adapter
	 */
	public function __construct($adapter)
	{
		parent::__construct('id', 'user_localdata', $adapter);
	}
}
