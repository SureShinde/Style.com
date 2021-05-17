<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Button;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\Product\Type;

/**
 * Webkul MpAdvancedBookingSystem Save Block
 */
class Save extends \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button\Save
{
    /**
     * @var array
     */
    private static $availableProductTypes = [
        ConfigurableType::TYPE_CODE,
        Type::TYPE_SIMPLE,
        Type::TYPE_VIRTUAL
    ];

    /**
     * isConfigurableProduct
     *
     * @return boolean
     */
    protected function isConfigurableProduct()
    {
        if ($this->getProduct()->getTypeId() == "hotelbooking") {
            return true;
        } else {
            return in_array($this->getProduct()->getTypeId(), self::$availableProductTypes);
        }
    }
}
