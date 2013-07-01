<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Build holdings items paging
 *
 */
class HoldingItemsPaging extends AbstractHelper
{

	/**
	 * @var	Integer
	 */
	protected $pageSize = 10;

	public function __invoke($baseUrl, $total, $activePage = 1)
	{
		$maxPages	= 10;
		$activePage	= $activePage > $total ? 1 : $activePage;
		$spread		= $maxPages/2;
		$startPage	= $activePage > $spread ? $activePage-$spread : 1;
		$endPage	= $startPage + $maxPages > $total ? $total : $startPage + $maxPages;

		$data = array(
			'pages'		=> ceil($total/$this->pageSize),
			'active'	=> $activePage,
			'url'		=> $baseUrl,
			'startPage'	=> $startPage,
			'endPage'	=> $endPage
		);

		return $this->getView()->render('Holdings/holding-items-paging', $data);
	}
}
