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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Controller\Adminhtml\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as CatalogProductBuilder;
use Magento\Framework\App\RequestInterface;
use Webkul\MpAdvancedBookingSystem\Model\Product\Type\Hotelbooking;

class Builder
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @param ProductFactory    $productFactory
     * @param Type\Configurable $configurableType
     */
    public function __construct(
        ProductFactory $productFactory,
        Type\Configurable $configurableType
    ) {
        $this->productFactory = $productFactory;
        $this->configurableType = $configurableType;
    }

    /**
     * Set type and data to configurable product
     *
     * @param CatalogProductBuilder $subject
     * @param Product               $product
     * @param RequestInterface      $request
     * @return Product
     */
    public function afterBuild(
        CatalogProductBuilder $subject,
        Product $product,
        RequestInterface $request
    ) {
        if ($request->has('attributes')) {
            $attributesParam = $request->getParam('attributes');
            $productParams = $request->getParam('product');
            if (!empty($attributesParam)) {
                if (empty($productParams['weight'])) {
                    $product->setTypeId(Hotelbooking::TYPE_CODE);
                } else {
                    $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
                }
                $this->configurableType->setUsedProductAttributes($product, $attributesParam);
            } else {
                if (empty($productParams['weight'])) {
                    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL);
                } else {
                    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                }
            }
        }

        // Required attributes of virtual product for hotelbooking creation
        if ($request->getParam('popup') && ($requiredAttr = $request->getParam('required'))) {
            $requiredAttr = explode(",", $requiredAttr);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttr)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($request->getParam('popup')
            && $request->getParam('product')
            && !is_array($request->getParam('product'))
            && $request->getParam('id', false) === false
        ) {
            $hotelProduct = $this->productFactory->create();
            $hotelProduct->setStoreId(0)
                ->load($request->getParam('product'))
                ->setTypeId($request->getParam('type'));

            $data = [];
            foreach ($hotelProduct->getTypeInstance()->getSetAttributes($hotelProduct) as $attribute) {
                /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                if (!$attribute->getIsUnique()
                    && $attribute->getFrontend()->getInputType() != 'gallery'
                    && $attribute->getAttributeCode() != 'required_options'
                    && $attribute->getAttributeCode() != 'has_options'
                    && $attribute->getAttributeCode() != $hotelProduct->getIdFieldName()
                ) {
                    $data[$attribute->getAttributeCode()] = $hotelProduct->getData(
                        $attribute->getAttributeCode()
                    );
                }
            }
            $product->addData($data);
            $product->setWebsiteIds($hotelProduct->getWebsiteIds());
        }

        return $product;
    }
}
