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
namespace Webkul\MpAdvancedBookingSystem\Controller\Hotelbooking;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\MpAdvancedBookingSystem\Helper\Email as EmailHelper;

class Submitquestion extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory
     */
    private $questionModel;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @param Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param EmailHelper $emailHelper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        EmailHelper $emailHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->questionModel = $questionModel;
        $this->helper = $helper;
        $this->date = $date;
        $this->emailHelper = $emailHelper;
        $this->mpHelper = $mpHelper;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        try {
            if ($this->getRequest()->isPost()) {
                $params = $this->getRequest()->getParams();
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $resultRedirect;
                }
                if ($params['customer_nickname'] == "") {
                    $this->messageManager->addError(__('Please enter your nick name'));
                } elseif ($params['user_question'] == "") {
                    $this->messageManager->addError(__('Please enter your question'));
                } else {
                    $data['product_id'] = $params['product_id'];
                    $data['nick_name'] = $params['customer_nickname'];
                    $data['question'] = $params['user_question'];
                    $data['customer_id'] = 0;
                    $data['created_at'] = $this->date->gmtDate();
                    $data['updated_at'] = $this->date->gmtDate();
                    if ($this->helper->isCustomerLoggedIn()) {
                        $data['customer_id'] = $this->helper->getCustomerId();
                    }
                    if (!$this->helper->getConfigValue('auto_approve_question')) {
                        $data['status'] = \Webkul\MpAdvancedBookingSystem\Model\Question::STATUS_APPROVED;
                    }
                    $model = $this->questionModel->create()->setData($data)->save();
                    $id = $model->getId();
                    if ($id) {
                        $this->sendEmail($data);
                        $this->messageManager->addSuccess(
                            __('Your query has been submitted successfully')
                        );
                    } else {
                        $this->messageManager->addError(
                            __('Something went wrong !!!')
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Hotelbooking_Submitquestion_execute Exception : ".$e->getMessage()
            );
            $this->messageManager->addError(
                __('Something Went Wrong, Please try again later.')
            );
        }
        
        return $resultRedirect;
    }

    /**
     * sendEmail
     *
     * @param array $data
     * @return void
     */
    private function sendEmail($data)
    {
        try {
            $receiverInfo = [];
            $isSeller = false;
            $receiverName = __('Admin');
            $emailTemplateVariables = [];
            $sellerData = $this->mpHelper->getSellerProductDataByProductId(
                $data['product_id']
            );
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
            $emailTemplateVariables['myvar1'] = $receiverName;
            if (!empty($data['product_id'])) {
                $_product = $this->helper->getProduct($data['product_id']);
                $emailTemplateVariables['product_name'] = $_product->getName();
                $emailTemplateVariables['product_sku'] = $_product->getSku();
            } else {
                $emailTemplateVariables['product_name'] = "hotel";
                $emailTemplateVariables['product_sku'] = "hotel";
            }
            $emailTemplateVariables['question'] = $data['question'];
            $emailTemplateVariables['subject'] = __(
                "On booking of %1",
                $emailTemplateVariables['product_name']
            );

            $this->emailHelper->sendAskedQuestionMail(
                $data,
                $emailTemplateVariables,
                $receiverInfo
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Hotelbooking_Submitquestion_sendEmail Exception : ".$e->getMessage()
            );
        }
    }
}
