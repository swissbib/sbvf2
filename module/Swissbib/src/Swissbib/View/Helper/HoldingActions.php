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
     * @param    Array    $item
     * @param    String    $listClass        Custom class for list element
     * @return    String
     */
    public function __invoke(array $item, $listClass = '')
    {
        /** @var RecordLink $recordLink */
        $recordLink = $this->getView()->plugin('recordLink');
        $actions    = array();
        $loginURL   = $this->getView()->url('myresearch-home');

        if (isset($item['backlink'])) {
            $actions['backlink'] = array(
                'label' => $this->translate('hold_backlink'),
                'href'  =>   $this->getView()->redirectProtocolWrapper()->getWrappedURL($item['backlink']),
                'target'=> '_blank'
            );
        }

        if (isset($item['userActions'])) {
            if ($item['userActions']['login']) {
                // show different label and sign in
                $actions['sign_in'] = array(
                    'label'  => $this->translate('Login'),
                    'href'   => $loginURL,
                );
            }
            if ($item['userActions']['hold']) {
                $actions['hold'] = array(
                    $itemkey = key($item['availability']),
                    'label' => array_search('lendable_borrowed', $item['availability'][$itemkey]) ? $this->translate('Recall This') : $this->translate('hold_place'),
                    'href'  => $recordLink->getHoldUrl($item['holdLink'])
                );
            }
            if ($item['userActions']['shortLoan']) {
                $actions['shortloan'] = array(
                    'label' => $this->translate('hold_shortloan'),
                    'href'  => 'javascript:alert(\'Not implemented yet\')'
                );
            }
            if ($item['userActions']['photocopyRequest']) {
                $actions['photocopy'] = array(
                    'label' => $this->translate('hold_copy'),
                    'href'  => $item['userActions']['photocopyRequestLink'],
                    'target' => '_blank',
                );
            }
            if ($item['userActions']['bookingRequest']) {
                $actions['booking'] = array(
                    'label'  => $this->translate('hold_booking'),
                    'href'   => $item['userActions']['bookingRequestLink'],
                    'target' => '_blank',
                );
            }
        } elseif (isset($item['holdLink'])) {
            $actions['hold'] = array(
                'label' => $this->translate('hold_place'),
                'href'  => $recordLink->getHoldUrl($item['holdLink'])
            );
        }

        if (isset($item['eodlink']) && $item['eodlink']) {
            $actions['eod'] = array(
                'label' => $this->translate('Order_EBook_tooltip'),
                'href'  => $item['eodlink']
            );
        }

        foreach ($actions as $key => $action) {
            $actions[$key]['class'] = isset($action['class']) ? $action['class'] . ' ' . $key : $key;
        }

        $data = array(
            'actions'     => $actions,
            'listClass'    => $listClass
        );

        return $this->getView()->render('Holdings/holding-actions', $data);
    }



    /**
     * Translate message
     *
     * @param        $message
     * @param string $textDomain
     * @param null   $locale
     * @return string
     */
    protected function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->translator->translate($message, $textDomain, $locale);
    }
}
