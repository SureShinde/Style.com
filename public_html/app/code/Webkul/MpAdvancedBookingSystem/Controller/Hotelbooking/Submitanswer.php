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

class Submitanswer extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
    protected $answerModel;
    protected $registry;
    protected $helper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        \Webkul\MpAdvancedBookingSystem\Model\AnswerFactory $answerModel,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->answerModel = $answerModel;
        $this->helper = $helper;
        $this->_date = $date;
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
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $resultRedirect;
                }
                if ($params['customer_nick_name'] == "") {
                    $this->messageManager->addError(__('Please enter your nick name'));
                } elseif ($params['user_answer'] == "") {
                    $this->messageManager->addError(__('Please enter your answer'));
                } else {
                    $data['nick_name'] = $params['customer_nick_name'];
                    $data['answer'] = $params['user_answer'];
                    $data['question_id'] = $params['question_id'];
                    $data['customer_id'] = 0;
                    $data['status'] = 1;
                    $data['created_at'] = $this->_date->gmtDate();
                    $data['updated_at'] = $this->_date->gmtDate();
                    if ($this->helper->isCustomerLoggedIn()) {
                        $data['customer_id'] = $this->helper->getCustomerId();
                    }
                    $model = $this->answerModel->create()->setData($data)->save();
                    $id = $model->getId();
                    if ($id) {
                        $this->messageManager->addSuccess(
                            __('Your answer has been submitted successfully')
                        );
                    } else {
                        $this->messageManager->addError(
                            __('Something went wrong !!!')
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something Went Wrong, Please try again later.')
            );
        }
        
        return $resultRedirect;
    }
}
