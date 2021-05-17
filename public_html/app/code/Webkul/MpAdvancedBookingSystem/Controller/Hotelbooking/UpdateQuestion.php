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

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory as QuestionCollection;

/**
 * Webkul MpAdvancedBookingSystem UpdateQuestion controller.
 */
class UpdateQuestion extends Action
{
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
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var HelperData
     */
    private $mpHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param FormKeyValidator $formKeyValidator
     * @param HelperData $mpHelper
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking\Questions $questionBlock
     * @param QuestionCollection $questionCollection
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $coreRegistry,
        FormKeyValidator $formKeyValidator,
        HelperData $mpHelper,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking\Questions $questionBlock,
        QuestionCollection $questionCollection,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
        $this->formKeyValidator = $formKeyValidator;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->questionBlock = $questionBlock;
        $this->questionCollection = $questionCollection;
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
     * Mass delete seller products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $isPartner = $this->mpHelper->isSeller();
            if ($isPartner == 1) {
                try {
                    if (!$this->formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/questions',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $params = $this->getRequest()->getParams();
                    
                    if (empty($params['product_mass_delete'])) {
                        $this->messageManager->addError(
                            __('Please select questions first.')
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/questions',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $idsData = $this->getActualQuestionIds($params);
                    $flag = $this->updateQuestionData($idsData['ids'], $params);

                    if ($flag) {
                        // clear cache
                        $this->mpHelper->clearCache();
                        if (!empty($params['question_action'])
                            && $params['question_action'] == 1
                        ) {
                            $successMsg = __(
                                'Hotel Booking Questions are successfully deleted from your account.'
                            );
                        } else {
                            $successMsg = __(
                                'Hotel Booking Questions status are successfully updated in your account.'
                            );
                        }
                        $this->messageManager->addSuccess($successMsg);
                    } else {
                        $this->messageManager->addError(
                            __("Something went wrong !!!")
                        );
                    }
                } catch (\Exception $e) {
                    $this->helper->logDataInLogger(
                        "Controller_Hotelbooking_UpdateQuestion_execute Exception : ".$e->getMessage()
                    );
                    $this->messageManager->addError($e->getMessage());
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/questions',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * getActualQuestionIds
     *
     * @param array $params
     * @return array
     */
    private function getActualQuestionIds($params)
    {
        $quesIds = [];
        $ids = [];
        try {
            $questionsColl = $this->questionBlock->getAllQuestions();
            if ($questionsColl) {
                $quesIds = $questionsColl->getAllIds();
            }
            if (!empty($quesIds)) {
                foreach ($params['product_mass_delete'] as $idToDelete) {
                    if (in_array($idToDelete, $quesIds)) {
                        $ids[] = $idToDelete;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Hotelbooking_UpdateQuestion_getActualQuestionIds Exception : ".$e->getMessage()
            );
        }
        return [
            'quesIds' => $quesIds,
            'ids' => $ids
        ];
    }

    /**
     * updateQuestionData
     *
     * @param array $ids
     * @param array $params
     * @return boolean
     */
    private function updateQuestionData($ids, $params)
    {
        try {
            if (!empty($ids)) {
                $this->coreRegistry->register('isSecureArea', 1);
                
                $collection = $this->questionCollection->create()
                    ->addFieldToFilter(
                        'entity_id',
                        ['in' => $ids]
                    );
                if ($collection->getSize()) {
                    if (!empty($params['question_action'])
                        && $params['question_action'] == 1
                    ) {
                        $collection->walk('delete');
                    } elseif (!empty($params['question_status'])
                        && array_key_exists($params['question_status'], $this->helper->getQuestionStatuses())
                    ) {
                        foreach ($collection as $questionData) {
                            $questionData->setStatus($params['question_status'])->save();
                        }
                    }
                }
                $this->coreRegistry->unregister('isSecureArea');
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Hotelbooking_UpdateQuestion_updateQuestionData Exception : ".$e->getMessage()
            );
            return false;
        }
    }
}
