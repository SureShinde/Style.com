<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    public function _toHtml()
    {
        if (!$this->_scopeConfig->isSetFlag('payment/mpstripe/active') &&
            !$this->_scopeConfig->isSetFlag('payment/mpstripe/vault_active')
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
