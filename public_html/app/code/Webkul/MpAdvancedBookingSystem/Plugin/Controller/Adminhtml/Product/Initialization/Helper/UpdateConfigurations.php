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

use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;

class UpdateConfigurations
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var VariationHandler
     */
    protected $variationHandler;

    /**
     * @var array
     */
    private $keysPostData = [
        'status',
        'sku',
        'name',
        'price',
        'configurable_attribute',
        'media_gallery',
        'swatch_image',
        'small_image',
        'thumbnail',
        'image',
    ];

    /**
     * @param RequestInterface           $request
     * @param ProductRepositoryInterface $productRepository
     * @param \VariationHandler          $variationHandler
     */
    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        VariationHandler $variationHandler
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->variationHandler = $variationHandler;
    }

    /**
     * Update data for hotelbooking product configurations
     * @return \Magento\Catalog\Model\Product
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $hotelBookingProduct
    ) {
        $configurations = $this->getConfigurations();
        $configurations = $this->variationHandler->duplicateImagesForVariations(
            $configurations
        );
        if (count($configurations)) {
            foreach ($configurations as $productId => $productData) {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->getById(
                    $productId,
                    false,
                    $this->request->getParam('store', 0)
                );
                $productData = $this->variationHandler->processMediaGallery(
                    $product,
                    $productData
                );
                $product->addData($productData);
                if ($product->hasDataChanges()) {
                    $product->save();
                }
            }
        }
        return $hotelBookingProduct;
    }

    /**
     * Get configurations of hotel booking product from request
     *
     * @return array
     */
    protected function getConfigurations()
    {
        $result = [];
        $hotelbookingMatrix = $this->request->getParam('configurable-matrix-serialized', "[]");
        if (isset($hotelbookingMatrix) && $hotelbookingMatrix != "") {
            $hotelbookingMatrix = json_decode($hotelbookingMatrix, true);

            foreach ($hotelbookingMatrix as $item) {
                if (empty($item['was_changed'])) {
                    continue;
                } else {
                    unset($item['was_changed']);
                }

                if (!$item['newProduct']) {
                    $result[$item['id']] = $this->mapData($item);

                    if (isset($item['qty'])) {
                        $result[$item['id']]['quantity_and_stock_status']['qty'] = $item['qty'];
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

        foreach ($this->keysPostData as $key) {
            if (isset($item[$key])) {
                $result[$key] = $item[$key];
            }
        }

        return $result;
    }
}
