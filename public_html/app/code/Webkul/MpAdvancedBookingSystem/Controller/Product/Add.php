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
namespace Webkul\MpAdvancedBookingSystem\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Product Add Controller.
 */
class Add extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Array of actions which can be processed without secret key validation.
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param Webkul\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\Marketplace\Controller\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
        );
        $this->productBuilder = $productBuilder;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->customerUrl = $customerUrl;
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
     * Seller Product Add Action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $isPartner = $this->mpHelper->isSeller();
        $createProductPageUrl = $this->resultRedirectFactory->create()->setPath(
            '*/*/create',
            ['_secure' => $this->getRequest()->isSecure()]
        );
        $sellerDashboardUrl = $this->resultRedirectFactory->create()->setPath(
            'marketplace/account/dashboard',
            ['_secure' => $this->getRequest()->isSecure()]
        );
        $allowedBookingTypes = explode(',', $this->helper->getMpConfigValue('booking_types'));
        $allowedProductTypes = [
            "virtual",
            "configurable"
        ];
        if ($isPartner == 1) {
            try {
                if (!$this->helper->getMpConfigValue('enable_booking')) {
                    return $sellerDashboardUrl;
                }
                $params = $this->getRequest()->getParams();
                if (!empty($params['set'])
                    && !empty($params['type'])
                    && !empty($params['booking_type'])
                ) {
                    $set = $params['set'];
                    $type = $params['type'];
                    $bookingType = $params['booking_type'];
                    $allowedsets = $this->helper->getAllowedAttrSetIDs();
                    $allowedsets[] = $this->helper->getProductAttributeSetIdByLabel(
                        'Default'
                    );
                    $setAndTypeArr = $this->helper->getAttributeSetAndProductTypeForBooking($bookingType);
                    if (!in_array($type, $allowedProductTypes)
                        || !in_array($set, $allowedsets)
                        || !in_array($bookingType, $allowedBookingTypes)
                        || empty($setAndTypeArr)
                        || $setAndTypeArr['set'] !== $set
                    ) {
                        $this->messageManager->addError(
                            __('Product Type Or Attribute Set Invalid or Not Allowed')
                        );
                        return $createProductPageUrl;
                    }
                    $product = $this->productBuilder->build(
                        $this->getRequest()->getParams(),
                        $this->mpHelper->getCurrentStoreId()
                    );
                    $resultPage = $this->resultPageFactory->create();
                    if ($this->mpHelper->getIsSeparatePanel()) {
                        $resultPage->addHandle('marketplace_layout2_product_add');
                    } else {
                        $resultPage->addHandle('marketplace_product_add');
                    }
                    
                    $resultPage->getConfig()->getTitle()->set(
                        __('Add Booking Product')
                    );
                    return $resultPage;
                } else {
                    return $createProductPageUrl;
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Controller_Product_Add_execute Exception : ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
                return $createProductPageUrl;
            }
        } else {
            $this->helper->logDataInLogger("Controller_Product_Add_execute NO PARTNER");
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
