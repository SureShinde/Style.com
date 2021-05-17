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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Webkul\MpAdvancedBookingSystem\Model\Product\Type\Hotelbooking as Hotelbooking;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul MpAdvancedBookingSystem Configurable Plugin
 */
class Configurable
{
    /**
     * @var VariationHandler
     */
    private $variationHandler;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Factory
     */
    private $optionsFactory;

    /**
     * @var array
     */
    private $keysPost = [
        'status',
        'sku',
        'name',
        'price',
        'configurable_attribute',
        'media_gallery',
        'swatch_image',
        'small_image',
        'thumbnail',
        'image'
    ];

    /**
     * Constructor
     *
     * @param VariationHandler $variationHandler
     * @param RequestInterface $request
     * @param Factory          $optionsFactory
     */
    public function __construct(
        VariationHandler $variationHandler,
        RequestInterface $request,
        Factory $optionsFactory
    ) {
        $this->variationHandler = $variationHandler;
        $this->request = $request;
        $this->optionsFactory = $optionsFactory;
    }

    /**
     * Initialize data for configurable product
     *
     * @param  Helper           $subject
     * @param  ProductInterface $product
     * @return ProductInterface
     * @throws \InvalidArgumentException
     */
    public function afterInitialize(Helper $subject, ProductInterface $product)
    {
        $attributesParam = $this->request->getParam('attributes');
        $productData = $this->request->getPost('product', []);

        if ($product->getTypeId() !== Hotelbooking::TYPE_CODE || empty($attributesParam)) {
            return $product;
        }

        $setId = $this->request->getPost('new-variations-attribute-set-id');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        $extensionAttributes = $product->getExtensionAttributes();

        $product->setNewVariationsAttributeSetId($setId);

        $hotelbookingOptions = [];
        if (!empty($productData['configurable_attributes_data'])) {
            $hotelbookingOptions = $this->optionsFactory->create(
                (array) $productData['configurable_attributes_data']
            );
        }

        $extensionAttributes->setConfigurableProductOptions($hotelbookingOptions);

        $this->setLinkedProducts($product, $extensionAttributes);
        $product->setCanSaveConfigurableAttributes(
            (bool) $this->request->getPost('affect_configurable_product_attributes')
        );

        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    /**
     * Relate simple products to configurable
     *
     * @param  ProductInterface          $product
     * @param  ProductExtensionInterface $extensionAttributes
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setLinkedProducts(
        ProductInterface $product,
        ProductExtensionInterface $extensionAttributes
    ) {
        $associatedProductIds = $this->request->getPost(
            'associated_product_ids_serialized',
            '[]'
        );
        if ($associatedProductIds != null && !empty($associatedProductIds)) {
            $associatedProductIds = json_decode($associatedProductIds, true);
        }

        $hotelbookingVariationsMatrix = $this->getVariationMatrix();

        if ($associatedProductIds || $hotelbookingVariationsMatrix) {
            $this->variationHandler->prepareAttributeSet($product);
        }

        if (!empty($hotelbookingVariationsMatrix)) {
            $generatedProductIds = $this->variationHandler->generateSimpleProducts(
                $product,
                $hotelbookingVariationsMatrix
            );
            $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
        }
        if (!is_array($associatedProductIds)) {
            $associatedProductIds = [];
        }
        $extensionAttributes->setConfigurableProductLinks(
            array_filter($associatedProductIds)
        );
    }

    /**
     * Get variation-matrix from request
     *
     * @return array
     */
    protected function getVariationMatrix()
    {
        $result = [];
        $hotelbookingMatrix = $this->request->getParam('configurable-matrix-serialized', "[]");
        if (isset($hotelbookingMatrix) && $hotelbookingMatrix != "") {
            $hotelbookingMatrix = json_decode($hotelbookingMatrix, true);

            foreach ($hotelbookingMatrix as $item) {
                if ($item['newProduct']) {
                    $vKey = $item['variationKey'];
                    $result[$vKey] = $this->mapData($item);

                    if (isset($item['qty'])) {
                        $result[$vKey]['quantity_and_stock_status']['qty'] = $item['qty'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Map data from POST
     *
     * @param  array $item
     * @return array
     */
    private function mapData(array $item)
    {
        $result = [];

        foreach ($this->keysPost as $key) {
            if (isset($item[$key])) {
                $result[$key] = $item[$key];
            }
        }

        return $result;
    }
}
