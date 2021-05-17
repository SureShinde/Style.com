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
namespace Webkul\MpAdvancedBookingSystem\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Webkul\MpAdvancedBookingSystem\Api\OptionRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceModelConfigurable;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Webkul MpAdvancedBookingSystem SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var OptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var ResourceModelConfigurable
     */
    private $resourceModel;

    /**
     * SaveHandler constructor
     *
     * @param ResourceModelConfigurable $resourceModel
     * @param OptionRepositoryInterface $optionRepository
     */
    public function __construct(
        ResourceModelConfigurable $resourceModel,
        OptionRepositoryInterface $optionRepository
    ) {
        $this->resourceModel = $resourceModel;
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param ProductInterface $entity
     * @param array            $arguments
     * @return ProductInterface
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getTypeId() !== "hotelbooking") {
            return $entity;
        }

        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $entity;
        }

        if ($extensionAttributes->getConfigurableProductOptions() !== null) {
            $this->deleteConfigurableProductAttributes($entity);
        }

        $hotelbookingOptions = (array) $extensionAttributes->getConfigurableProductOptions();
        if (!empty($hotelbookingOptions)) {
            $this->saveConfigurableProductAttributes($entity, $hotelbookingOptions);
        }

        $hotelbookingLinks = $extensionAttributes->getConfigurableProductLinks();
        if ($hotelbookingLinks !== null) {
            $hotelbookingLinks = (array)$hotelbookingLinks;
            $this->resourceModel->saveProducts($entity, $hotelbookingLinks);
        }

        return $entity;
    }

    /**
     * Save attributes for hotelbooking product
     *
     * @param  ProductInterface $product
     * @param  array            $attributes
     * @return array
     */
    private function saveConfigurableProductAttributes(ProductInterface $product, array $attributes)
    {
        $ids = [];
        foreach ($attributes as $attribute) {
            $attribute->setId(null);
            $ids[] = $this->optionRepository->save($product->getSku(), $attribute);
        }

        return $ids;
    }

    /**
     * Remove product attributes
     *
     * @param  ProductInterface $product
     * @return void
     */
    private function deleteConfigurableProductAttributes(ProductInterface $product)
    {
        $list = $this->optionRepository->getList($product->getSku());
        foreach ($list as $item) {
            $this->optionRepository->deleteById($product->getSku(), $item->getId());
        }
    }
}
