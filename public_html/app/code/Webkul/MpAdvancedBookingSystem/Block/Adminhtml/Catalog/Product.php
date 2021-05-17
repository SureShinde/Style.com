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
namespace Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog;

class Product extends \Magento\Catalog\Block\Adminhtml\Product
{
    /**
     * _getAddProductButtonOptions
     * Retrieve options(except hotelbooking type) for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $buttonOptions = [];
        $productTypes = $this->_typeFactory->create()->getTypes();
        uasort(
            $productTypes,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );

        foreach ($productTypes as $proTypeId => $productType) {
            if ($proTypeId != 'hotelbooking') {
                $buttonOptions[$proTypeId] = [
                    'label' => __($productType['label']),
                    'onclick' => "setLocation('" . $this->_getProductCreateUrl($proTypeId) . "')",
                    'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $proTypeId,
                ];
            }
        }

        return $buttonOptions;
    }
}
