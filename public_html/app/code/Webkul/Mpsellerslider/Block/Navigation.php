<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsellerslider
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpsellerslider\Block;

/**
 * Webkul Mpsellerslider Seller Navigation Block
 */

class Navigation extends \Magento\Framework\View\Element\Template
{
    /**
     * [getIsSecure check is secure or not]
     *
     * @return [boolean]
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }
}
