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
namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Hotelbooking\Question;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory;

/**
 * Controller MassDeleteAnswer
 */
class MassDeleteAnswer extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter
     *
     * @var MassActionFilter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param MassActionFilter  $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        MassActionFilter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $questionId = 0;

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $records = $collection->getSize();
        $questionIds = array_unique($collection->getColumnValues('question_id'));
        $flag = false;
        if (count($questionIds)==1 && !empty($questionIds[0])) {
            $questionId = $questionIds[0];
            $collection->walk('delete');
            $flag = true;
        }
        if ($flag) {
            $this->messageManager->addSuccess(__(
                'A total of %1 record(s) have been deleted.',
                $records
            ));
        } else {
            $this->messageManager->addError(__(
                'Something went wrong !!!'
            ));
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($questionId) {
            return $resultRedirect->setPath(
                '*/*/view',
                ['question_id' => $questionId]
            );
        } else {
            return $resultRedirect->setPath(
                '*/hotelbooking/questions'
            );
        }
    }
}
