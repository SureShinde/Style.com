<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpsellersliderTwo
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpsellersliderTwo\Block;

/**
 * Webkul MpsellersliderTwo Seller Navigation Block
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
