<?php
namespace Swissbib\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;

/**
 * Build link for for item actions
 *
 */
class HoldingActions extends AbstractTranslatorHelper
{

	/**
	 * Render action link list
	 *
	 * @param	Array	$item
	 * @return	String
	 */
	public function __invoke(array $item)
	{
		/** @var RecordLink $recordLink */
		$recordLink = $this->getView()->plugin('recordLink');
		$actions	= array();

		if (isset($item['backlink'])) {
			$actions['backlink'] = array(
				'label' => 'zum Bestand',
				'href'  => $item['backlink']
			);
		}

		if (isset($item['userActions'])) {
			if ($item['userActions']['hold']) {
				$actions['hold'] = array(
					'label' => 'ausleihen',
					'href'  => $recordLink->getHoldUrl($item['holdLink'])
				);
			}
			if ($item['userActions']['shortLoan']) {
				$actions['shortloan'] = array(
					'label' => 'Kurzausleihe',
					'href'  => 'javascript:alert(\'Not implemented yet\')'
				);
			}
			if ($item['userActions']['photocopyRequest']) {
				$actions['photocopy'] = array(
					'label' => 'Kopie bestellen',
					'href'  => $item['userActions']['photocopyRequestLink']
				);
			}
			if ($item['userActions']['bookingRequest']) {
				$actions['booking'] = array(
					'label' => 'Booking Request',
					'href'  => 'javascript:alert(\'Not implemented yet\')'
				);
			}
		} elseif (isset($item['holdLink'])) {
			$actions['hold'] = array(
				'label' => 'ausleihen',
				'href'  => $recordLink->getHoldUrl($item['holdLink'])
			);
		}

		if (isset($item['eodlink']) && $item['eodlink']) {
			$actions['eod'] = array(
				'label' => $this->translator->translate('Order_EBook_tooltip'),
				'href'  => $item['eodlink']
			);
		}
		
		foreach ($actions as $key => $action) {
			$actions[$key]['class'] = isset($action['class']) ? $action['class'] . ' ' . $key : $key;
		}

		$data = array(
			'actions' => $actions
		);

		return $this->getView()->render('Holdings/holding-actions', $data);
	}
}
