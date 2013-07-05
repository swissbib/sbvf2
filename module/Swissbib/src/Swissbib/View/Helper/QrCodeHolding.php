<?php
namespace Swissbib\View\Helper;

use Swissbib\RecordDriver\SolrMarc;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use QRCode\Service\QRCode as QRCodeService;

/**
 * [Description]
 *
 */
class QrCodeHolding extends AbstractTranslatorHelper
{
	/** @var  QrCode */
	protected $qrCodeHelper;

	public function __invoke(array $item, $recordTitle = '')
	{
		if (!$this->qrCodeHelper) {
			$this->qrCodeHelper = $this->getView()->plugin('qrCode');
		}

		$data = array();

		if ($recordTitle) {
			$data[] = $recordTitle;
		}
		if ($item['institution']) {
			$data[] = $this->translator->translate(strtolower($item['institution']), 'institution');
		}
		if ($item['locationLabel']) {
			$data[] = $item['locationLabel'];
		}
		if ($item['signature']) {
			$data[] = $item['signature'];
		}

		$text		= implode(', ', $data);
		$qrCodeUrl	= $this->qrCodeHelper->source($text, 250);

//		http://chart.apis.google.com/chart?cht=qr&chs=230x230&choe=UTF-8&chl=Conjoncture+%C3%A9conomique%20-%20Uni+Basel+-+WWZ-Bibliothek+%2F+SWA,%20+:%20-

		return $this->getView()->render('Holdings/qr-code', array(
																 'item'	=> $item,
																 'url'	=> $qrCodeUrl
															));
	}
}
