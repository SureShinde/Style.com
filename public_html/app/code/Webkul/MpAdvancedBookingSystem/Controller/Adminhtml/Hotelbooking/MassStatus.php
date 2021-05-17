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

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory;

/**
 * Controller MassStatus
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Update booking product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        $records = $collection->getSize();
        $currentStatus = (int) $this->getRequest()->getParam('status');
        foreach ($collection as $region) {
            $region->setStatus($currentStatus);
            $region->save();
        }

        $this->messageManager->addSuccess(__(
            'A total of %1 record(s) have been updated.',
            $records
        ));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('mpadvancebooking/hotelbooking/questions');
    }
}
