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
namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;

class MassDelete extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Massactions filter
     *
     * @var MassActionFilter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param MassActionFilter  $filter
     * @param ProductBuilder    $productBuilder
     * @param CollectionFactory $collectionFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Context $context,
        ProductBuilder $productBuilder,
        MassActionFilter $filter,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository = null
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->create(
                ProductRepository::class
            );
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $bookingCollection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        $deletedProductCount = 0;
        /** @var \Magento\Catalog\Model\Product $bookingProduct */
        foreach ($bookingCollection->getItems() as $bookingProduct) {
            $this->productRepository->delete($bookingProduct);
            $deletedProductCount++;
        }
        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been deleted.',
                $deletedProductCount
            )
        );

        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath('mpadvancebooking/bookings/products');
    }
}
