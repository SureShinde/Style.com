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
namespace Webkul\MpAdvancedBookingSystem\Model\Product\Type;

class Hotelbooking extends \Magento\ConfigurableProduct\Model\Product\Type\Configurable
{
    const TYPE_CODE = 'hotelbooking';

    /**
     * Return true if product has options
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        return true;
    }

    /**
     * Check if product has required options
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasRequiredOptions($product)
    {
        return true;
    }

    /**
     * Check is virtual product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        return true;
    }

    /**
     * Return product weight based on simple product
     * weight or configurable product weight
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getWeight($product)
    {
        return 0;
    }

    /**
     * Get sku of product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSku($product)
    {
        $simpleOption = $product->getCustomOption('virtual_product');
        if ($simpleOption) {
            $optionProduct = $simpleOption->getProduct();
            $simpleSku = null;
            if ($optionProduct) {
                $simpleSku = $simpleOption->getProduct()->getSku();
            }
            $sku = parent::getOptionSku($product, $simpleSku);
        } else {
            $sku = parent::getSku($product);
        }

        return $sku;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then add Configurable specific options.
     *
     * @param \Magento\Framework\DataObject  $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $processMode
     * @return \Magento\Framework\Phrase|array|string
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $attributes = $buyRequest->getSuperAttribute();
        if ($attributes || !$this->_isStrictProcessMode($processMode)) {
            if (!$this->_isStrictProcessMode($processMode)) {
                if (is_array($attributes)) {
                    foreach ($attributes as $key => $val) {
                        if (empty($val)) {
                            unset($attributes[$key]);
                        }
                    }
                } else {
                    $attributes = [];
                }
            }
            
            $result = \Magento\Catalog\Model\Product\Type\AbstractType::_prepareProduct(
                $buyRequest,
                $product,
                $processMode
            );
            if (is_array($result)) {
                $subProduct = true;
                if ($this->_isStrictProcessMode($processMode)) {
                    foreach ($this->getConfigurableAttributes($product) as $attributeItem) {
                        /* @var $attributeItem \Magento\Framework\DataObject */
                        $attributeId = $attributeItem->getData('attribute_id');
                        if (!isset($attributes[$attributeId]) || empty($attributes[$attributeId])) {
                            $subProduct = null;
                            break;
                        }
                    }
                }
                
                if ($subProduct) {
                    $subProduct = $this->getProductByAttributes($attributes, $product);
                }
                if ($subProduct) {
                    $subProductLinkFieldId = $subProduct->getId();
                    $product->addCustomOption('attributes', $this->serializer->serialize($attributes));
                    $product->addCustomOption('product_qty_' . $subProductLinkFieldId, 1, $subProduct);
                    $product->addCustomOption('virtual_product', $subProductLinkFieldId, $subProduct);
                    
                    $_resultProcessConfig = $subProduct->getTypeInstance()->processConfiguration(
                        $buyRequest,
                        $subProduct,
                        $processMode
                    );
                    if (is_string($_resultProcessConfig) && !is_array($_resultProcessConfig)) {
                        return $_resultProcessConfig;
                    }

                    if (!isset($_resultProcessConfig[0])) {
                        return __('You can\'t add the item to shopping cart.')->render();
                    }

                    /**
                     * Adding parent product custom options to child product
                     * to be sure that it will be unique as its parent
                     */
                    if ($optionIds = $product->getCustomOption('option_ids')) {
                        $optionIds = explode(',', $optionIds->getValue());
                        foreach ($optionIds as $optionId) {
                            if ($option = $product->getCustomOption('option_' . $optionId)) {
                                $_resultProcessConfig[0]->addCustomOption('option_' . $optionId, $option->getValue());
                            }
                        }
                    }

                    $productLinkFieldId = $product->getId();
                    $_resultProcessConfig[0]->setParentProductId($productLinkFieldId)
                        ->addCustomOption('parent_product_id', $productLinkFieldId);
                    if ($this->_isStrictProcessMode($processMode)) {
                        $_resultProcessConfig[0]->setCartQty(1);
                    }
                    $result[] = $_resultProcessConfig[0];
                    return $result;
                } else {
                    if (!$this->_isStrictProcessMode($processMode)) {
                        return $result;
                    }
                }
            }
        }

        return $this->getSpecifyOptionMessage()->render();
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $options = parent::getOrderOptions($product);
        $options['attributes_info'] = $this->getSelectedAttributesInfo($product);
        if ($simpleOption = $product->getCustomOption('virtual_product')) {
            $options['simple_name'] = $simpleOption->getProduct()->getName();
            $options['simple_sku'] = $simpleOption->getProduct()->getSku();
        }

        $options['product_calculations'] = self::CALCULATE_PARENT;
        $options['shipment_type'] = self::SHIPMENT_TOGETHER;

        return $options;
    }
}
