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
namespace Webkul\MpAdvancedBookingSystem\Controller\Hotelbooking\Question;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory as QuestionCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory as AnswerCollection;

/**
 * Webkul MpAdvancedBookingSystem DeleteAnswer controller.
 */
class DeleteAnswer extends Action
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
     * @param AnswerCollection $answerCollection
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
        AnswerCollection $answerCollection,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
        $this->formKeyValidator = $formKeyValidator;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->questionBlock = $questionBlock;
        $this->questionCollection = $questionCollection;
        $this->answerCollection = $answerCollection;
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
        $questionId = 0;
        if ($this->getRequest()->isPost()) {
            $isPartner = $this->mpHelper->isSeller();
            $params = $this->getRequest()->getParams();
            if (!empty($params['actual_question_id'])) {
                $questionId = $params['actual_question_id'];
            }
            if ($questionId > 0) {
                $url = $this->resultRedirectFactory->create()->setPath(
                    '*/*/answers',
                    [
                        '_secure' => $this->getRequest()->isSecure(),
                        'question_id' => $questionId
                    ]
                );
            } else {
                $url = $this->resultRedirectFactory->create()->setPath(
                    '*/*/answers',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            if ($isPartner == 1) {
                try {
                    if (!$this->formKeyValidator->validate($this->getRequest())) {
                        return $url;
                    }
                    
                    if (empty($params['product_mass_delete'])) {
                        $this->messageManager->addError(
                            __('Please select answers first.')
                        );
                        return $url;
                    }
                    $idsData = $this->getActualAnswerIds($params);
                    $flag = $this->updateAnswerData($idsData['ids']);
                    $questionId = $idsData['question_id'];

                    if ($flag) {
                        // clear cache
                        $this->mpHelper->clearCache();
                        $this->messageManager->addSuccess(
                            __('Selected Answer(s) are successfully deleted from your account.')
                        );
                    } else {
                        $this->messageManager->addError(
                            __("Something went wrong !!!")
                        );
                    }
                } catch (\Exception $e) {
                    $this->helper->logDataInLogger(
                        "Controller_Hotelbooking_DeleteAnswer_execute Exception : ".$e->getMessage()
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
        return $url;
    }

    /**
     * getActualAnswerIds
     *
     * @param array $params
     * @return array
     */
    private function getActualAnswerIds($params)
    {
        $quesIds = [];
        $ids = [];
        $questionId = 0;
        $ansIds = [];
        try {
            $questionsColl = $this->questionBlock->getAllQuestions();
            if ($questionsColl) {
                $quesIds = $questionsColl->getAllIds();
            }
            if (!empty($quesIds)
                && !empty($params['actual_question_id'])
                && in_array($params['actual_question_id'], $quesIds)
            ) {
                $questionId = $params['actual_question_id'];
                $ansColl = $this->answerCollection->create()
                    ->addFieldToFilter(
                        'question_id',
                        ['eq' => $questionId]
                    );
                $ansIds = $ansColl->getAllIds();
                foreach ($params['product_mass_delete'] as $idToDelete) {
                    if (in_array($idToDelete, $ansIds)) {
                        $ids[] = $idToDelete;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Hotelbooking_Question_DeleteAnswer_getActualAnswerIds Exception : ".$e->getMessage()
            );
        }
        return [
            'ansIds' => $ansIds,
            'ids' => $ids,
            'question_id' => $questionId
        ];
    }

    /**
     * updateAnswerData
     *
     * @param array $ids
     * @param array $params
     * @return boolean
     */
    private function updateAnswerData($ids)
    {
        try {
            if (!empty($ids)) {
                $this->coreRegistry->register('isSecureArea', 1);
                
                $collection = $this->answerCollection->create()
                    ->addFieldToFilter(
                        'entity_id',
                        ['in' => $ids]
                    );
                if ($collection->getSize()) {
                    $collection->walk('delete');
                }
                $this->coreRegistry->unregister('isSecureArea');
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Hotelbooking_Question_DeleteAnswer_updateAnswerData Exception : ".$e->getMessage()
            );
            return false;
        }
    }
}
