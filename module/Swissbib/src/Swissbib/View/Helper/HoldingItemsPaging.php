<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * [Description]
 *
 */
class HoldingItemsPaging extends AbstractHelper
{
	public function __invoke($baseUrl, $total, $activePage = 1)
	{
		$maxPages	= 10;
		$activePage	= $activePage > $total ? 1 : $activePage;
		$spread		= $maxPages/2;
		$startPage	= $activePage > $spread ? $activePage-$spread : 1;
		$endPage	= $startPage + $maxPages > $total ? $total : $startPage + $maxPages;

		$data = array(
			'pages'		=> ceil($total/20),
			'active'	=> $activePage,
			'url'		=> $baseUrl,
			'startPage'	=> $startPage,
			'endPage'	=> $endPage
		);

		return $this->getView()->render('Holdings/holding-items-paging', $data);
	}
}
