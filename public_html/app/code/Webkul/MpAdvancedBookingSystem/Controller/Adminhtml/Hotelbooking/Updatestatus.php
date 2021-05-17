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
namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Hotelbooking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Controller Updatestatus class
 */
class Updatestatus extends Action
{
    /**
     * @var Webkul\MpAdvancedBookingSystem\Model\QuestionFactory
     */
    protected $questionModel;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->questionModel = $questionModel;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postItems = $this->getRequest()->getParams();
        $statuses = $this->helper->getQuestionStatuses();
        if (empty($postItems)) {
            $messages[] = __('Please correct the data sent.');
            $error = true;
        } else {
            try {
                $model = $this->questionModel->create()->load((int)$postItems['id']);
                $model->setStatus($postItems['status'])->save();
                $this->messageManager->addSuccess(__('Query has been %1.', $statuses[$postItems['status']]));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
