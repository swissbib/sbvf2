<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * [Description]
 *
 */
class HoldingItemsPaging extends AbstractHelper
{
	public function __invoke($total, $offset = 0)
	{

		return 'Paging';
	}
}
