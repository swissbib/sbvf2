<?php

namespace Swissbib\VuFind\View\Helper\Root;
use Zend\View\Helper\AbstractHelper, Zend\Mvc\Controller\Plugin\FlashMessenger;
use VuFind\View\Helper\Root\Flashmessages as VFFlashmessages;

 
 /**
 * [...description of the type ...]
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 9/12/13
 * Time: 11:46 AM
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category swissbib_VuFind2
 * @package  [...package name...]
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */


class Flashmessages  extends VFFlashmessages {

    public function __construct(FlashMessenger $fm)
    {
        parent::__construct($fm);
    }

    /**
     * Generate flash message <div>'s with appropriate classes based on message type.
     *
     * @return string $html
     */
    public function __invoke()
    {
        $html = '';
        $namespaces = array('error', 'info');
        foreach ($namespaces as $ns) {
            $this->fm->setNamespace($ns);
            $messages = array_merge(
                $this->fm->getMessages(), $this->fm->getCurrentMessages()
            );
            foreach (array_unique($messages) as $msg) {
                $html .= '<div class="status_' . $this->getClassForNamespace($ns) . '"><h4>';
                // Advanced form:
                if (is_array($msg)) {
                    // Use a different translate helper depending on whether
                    // or not we're in HTML mode.
                    if (!isset($msg['translate']) || $msg['translate']) {
                        $helper = (isset($msg['html']) && $msg['html'])
                            ? 'translate' : 'transEsc';
                    } else {
                        $helper = (isset($msg['html']) && $msg['html'])
                            ? false : 'escapeHtml';
                    }
                    $helper = $helper
                        ? $this->getView()->plugin($helper) : false;
                    $tokens = isset($msg['tokens']) ? $msg['tokens'] : array();
                    $default = isset($msg['default']) ? $msg['default'] : null;
                    $html .= $helper
                        ? $helper($msg['msg'], $tokens, $default) : $msg['msg'];
                } else {
                    // Basic default string:
                    $transEsc = $this->getView()->plugin('transEsc');
                    $html .= $transEsc($msg);
                }
                $html .= '</h4></div>';
            }
            $this->fm->clearMessages();
            $this->fm->clearCurrentMessages();
        }
        return $html;
    }

}