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
namespace Webkul\MpAdvancedBookingSystem\Controller\Booking;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\MpAdvancedBookingSystem\Helper\Customer as CustomerHelper;
use Webkul\MpAdvancedBookingSystem\Helper\Email as EmailHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Catalog\Model\Product;

class Contact extends Action
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var EmailHelper
     */
    protected $emailHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @param Context $context
     * @param customerHelper $customerHelper
     * @param EmailHelper $emailHelper
     * @param JsonHelper $jsonHelper
     * @param Product $product
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        CustomerHelper $customerHelper,
        EmailHelper $emailHelper,
        JsonHelper $jsonHelper,
        Product $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        $this->_product = $product;
        $this->customerHelper = $customerHelper;
        $this->emailHelper = $emailHelper;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Sendmail to Seller action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            if ($this->getRequest()->getPostValue()) {
                $emailTemplateVariables = [];
                $senderInfo = [];
                $receiverInfo = [];
                $isSeller = false;
                $receiverName = __('Admin');

                $data = $this->getRequest()->getParams();

                if (!empty($data['product-id'])) {
                    $sellerData = $this->mpHelper->getSellerProductDataByProductId($data['product-id']);
                    if ($sellerData->getSize()) {
                        foreach ($sellerData as $sellerInfo) {
                            $receiverName = $sellerInfo->getName();
                            $receiverInfo = [
                                'name' => $receiverName,
                                'email' => $sellerInfo->getEmail()
                            ];
                            $isSeller = true;
                            break;
                        }
                    }
                }
                if (!$isSeller) {
                    $receiverInfo = [
                        'name' => $this->emailHelper->getConfigValue(
                            'trans_email/ident_general/name',
                            $this->storeManager->getStore()->getStoreId()
                        ),
                        'email' => $this->emailHelper->getConfigValue(
                            'trans_email/ident_general/email',
                            $this->storeManager->getStore()->getStoreId()
                        )
                    ];
                }

                if ($this->customerHelper->isCustomerLoggedIn()) {
                    $buyerName = $this->customerHelper->getCustomerName();
                    $buyerEmail = $this->customerHelper->getCustomerEmail();
                } else {
                    $buyerEmail = $data['email'];
                    $buyerName = $data['name'];
                    if (strlen($buyerName) < 2) {
                        $buyerName = __('Guest');
                    }
                }

                if ($this->customerHelper->isCustomerLoggedIn()) {
                    $buyerName = $this->customerHelper->getCustomerName();
                    $buyerEmail = $this->customerHelper->getCustomerEmail();
                } else {
                    $buyerEmail = $data['email'];
                    $buyerName = $data['name'];
                    if (strlen($buyerName) < 2) {
                        $buyerName = 'Guest';
                    }
                }

                $emailTemplateVariables['myvar1'] = $receiverName;
                if (!isset($data['product-id'])) {
                    $data['product-id'] = 0;
                } else {
                    $emailTemplateVariables['myvar3'] = $this->_product->load(
                        $data['product-id']
                    )->getName();
                }
                $emailTemplateVariables['myvar4'] = $data['query'];
                $emailTemplateVariables['myvar6'] = $data['subject'];
                $emailTemplateVariables['myvar5'] = $buyerEmail;
                $senderInfo = [
                    'name' => $buyerName,
                    'email' => $buyerEmail,
                ];
                $this->emailHelper->sendContactMailFromBuyer(
                    $emailTemplateVariables,
                    $senderInfo,
                    $receiverInfo
                );
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode('true')
                );
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Booking_Contact_execute Exception : ".$e->getMessage());
        }
    }
}
