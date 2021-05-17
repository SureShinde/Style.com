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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Controller MassStatus
 */
class MassStatus extends \Magento\Catalog\Controller\Adminhtml\Product\MassStatus
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Product\Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product\Action $productAction
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Action $productAction
    ) {
        $this->productAction = $productAction;
        parent::__construct($context, $productBuilder, $productPriceIndexerProcessor, $filter, $collectionFactory);
    }

    /**
     * Update booking product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $productCollection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        $bookingProductIds = $productCollection->getAllIds();
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $currentStatus = (int) $this->getRequest()->getParam('status');
        $filters = (array)$this->getRequest()->getParam('filters', []);

        if (isset($filters['store_id'])) {
            $storeId = (int)$filters['store_id'];
        }

        try {
            $this->_validateMassStatus($bookingProductIds, $currentStatus);
            $this->productAction->updateAttributes(
                $bookingProductIds,
                ['status' => $currentStatus],
                $storeId
            );
            $this->messageManager->addSuccess(
                __(
                    'A total of %1 record(s) have been updated.',
                    count($bookingProductIds)
                )
            );
            $this->_productPriceIndexerProcessor->reindexList($bookingProductIds);
        } catch (LocalizedException $e) {
            $this->messageManager->addError(
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->_getSession()->addException(
                $e,
                __('Something went wrong while updating the product(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        return $resultRedirect->setPath(
            'mpadvancebooking/bookings/products',
            ['store' => $storeId]
        );
    }
}
