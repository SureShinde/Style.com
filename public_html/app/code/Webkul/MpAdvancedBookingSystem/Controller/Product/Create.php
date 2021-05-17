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
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul MpAdvancedBookingSystem Product Create Controller Class.
 */
class Create extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->mpHelper = $mpHelper;
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

        if (!$this->_getSession()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    private function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Seller Product Create page.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $isPartner = $this->mpHelper->isSeller();
        $sellerDashboardUrl = $this->resultRedirectFactory->create()->setPath(
            'marketplace/account/dashboard',
            ['_secure' => $this->getRequest()->isSecure()]
        );
        $currentUrl = $this->resultRedirectFactory->create()->setPath(
            '*/*/create',
            ['_secure' => $this->getRequest()->isSecure()]
        );
        if ($isPartner == 1) {
            try {
                if (!$this->helper->getMpConfigValue('enable_booking')) {
                    return $sellerDashboardUrl;
                }
                $allowedProductType = $this->helper->getMpConfigValue('booking_types');
                $allowedBookingTypes = [];
                if (trim($allowedProductType)) {
                    $allowedBookingTypes = explode(',', $allowedProductType);
                }
                if (count($allowedBookingTypes) > 1) {
                    if (!$this->getRequest()->isPost()) {
                        /** @var \Magento\Framework\View\Result\Page $resultPage */
                        $resultPage = $this->resultPageFactory->create();
                        if ($this->mpHelper->getIsSeparatePanel()) {
                            $resultPage->addHandle('mpadvancebooking_layout2_product_create');
                        }
                        $resultPage->getConfig()->getTitle()->set(
                            __('Add New Booking Product')
                        );

                        return $resultPage;
                    }
                    if (!$this->formKeyValidator->validate($this->getRequest())) {
                        return $currentUrl;
                    }
                    $params = $this->getRequest()->getParams();

                    if (!empty($params['type'])) {
                        $bookingType = $params['type'];
                        $setAndTypeArr = $this->helper->getAttributeSetAndProductTypeForBooking($bookingType);

                        if (!in_array($bookingType, $allowedBookingTypes)
                            || empty($setAndTypeArr)
                            || $setAndTypeArr['set'] == 0
                        ) {
                            $this->messageManager->addError(
                                'Booking Product Type is Invalid Or Not Allowed'
                            );
                            return $currentUrl;
                        }
                        $attributeSet = $setAndTypeArr['set'];
                        $productType = $setAndTypeArr['type'];
                        // $bookingProductType = $setAndTypeArr['booking_type'];
                        $this->_getSession()->setAttributeSet($attributeSet);
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/add',
                            [
                                'set' => $attributeSet,
                                'type' => $productType,
                                'booking_type' => $bookingType,
                                '_secure' => $this->getRequest()->isSecure(),
                            ]
                        );
                    } else {
                        $this->messageManager->addError(
                            __('Please select booking product type.')
                        );

                        return $currentUrl;
                    }
                } elseif (count($allowedBookingTypes) == 0) {
                    $this->messageManager->addError(
                        'Please ask admin to configure booking product settings properly to add products.'
                    );
                    return $sellerDashboardUrl;
                } else {
                    $setAndTypeArr = $this->helper->getAttributeSetAndProductTypeForBooking($allowedBookingTypes[0]);

                    if (empty($setAndTypeArr) || $setAndTypeArr['set'] == 0) {
                        $this->messageManager->addError(
                            'Booking Product Type is Invalid Or Not Allowed'
                        );
                        return $sellerDashboardUrl;
                    } else {
                        $attributeSet = $setAndTypeArr['set'];
                        $productType = $setAndTypeArr['type'];
                        // $bookingProductType = $setAndTypeArr['booking_type'];

                        $this->_getSession()->setAttributeSet($attributeSet);
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/add',
                            [
                                'set' => $attributeSet,
                                'type' => $productType,
                                'booking_type' => $allowedBookingTypes[0],
                                '_secure' => $this->getRequest()->isSecure(),
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Controller_Product_Create_execute Exception : ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
                return $currentUrl;
            }
        } else {
            $this->helper->logDataInLogger("Controller_Product_Create_execute NO PARTNER");
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
