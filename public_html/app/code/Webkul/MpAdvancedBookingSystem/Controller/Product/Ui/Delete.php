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

namespace Webkul\MpAdvancedBookingSystem\Controller\Product\Ui;

use Magento\Framework\App\Action\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul MpAdvancedBookingSystem Product Delete controller.
 */
class Delete extends Action
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var SellerProduct
     */
    private $sellerProductCollectionFactory;

    /**
     * @var HelperData
     */
    private $mpHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct $sellerProductCollectionFactory
     * @param HelperData $mpHelper
     * @param ProductRepositoryInterface $productRepository
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Session $customerSession,
        Registry $coreRegistry,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        HelperData $mpHelper,
        ProductRepositoryInterface $productRepository,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->filter = $filter;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->mpHelper = $mpHelper;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Mass delete seller products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->mpHelper->isSeller();
        if ($isPartner == 1) {
            try {
                $collection = $this->filter->getCollection(
                    $this->productCollectionFactory->create()
                );
                $ids = $collection->getAllIds();
                $wholedata = [];

                $sellerId = $this->mpHelper->getCustomerId();
                $this->coreRegistry->register('isSecureArea', 1);
                $deletedIdsArr = [];
                $sellerProducts = $this->sellerProductCollectionFactory->create()
                    ->addFieldToFilter(
                        'mageproduct_id',
                        ['in' => $ids]
                    )->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                foreach ($sellerProducts as $sellerProduct) {
                    array_push($deletedIdsArr, $sellerProduct['mageproduct_id']);
                    $wholedata['id'] = $sellerProduct['mageproduct_id'];
                    $this->_eventManager->dispatch(
                        'mp_delete_product',
                        [$wholedata]
                    );
                    $sellerProduct->delete();
                }

                foreach ($deletedIdsArr as $id) {
                    try {
                        $product = $this->productRepository->getById($id);
                        $this->productRepository->delete($product);
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }

                $unauthIds = array_diff($ids, $deletedIdsArr);
                $this->coreRegistry->unregister('isSecureArea');
                if (!count($unauthIds)) {
                    // clear cache
                    $this->mpHelper->clearCache();
                    $this->messageManager->addSuccess(
                        __('Booking Products are successfully deleted from your account.')
                    );
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Controller_Product_Ui_Delete_execute Exception : ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
            }
            return $this->resultRedirectFactory->create()->setPath(
                'mpadvancebooking/product/bookinglist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
