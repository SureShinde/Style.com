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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Controller\Checkout\Cart;

use Magento\Framework\Controller\ResultFactory;

class Configure
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helper;

    /**
     * @param ResultFactory $resultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
    }

    public function afterExecute(\Magento\Checkout\Controller\Cart\Configure $subject, $result)
    {
        try {
            if ($this->helper->canConfigureCart()) {
                $this->messageManager->addError(
                    __("Can not configure booking.")
                );
                return $this->resultFactory->create(
                    ResultFactory::TYPE_REDIRECT
                )->setPath('checkout/cart');
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Plugin_Controller_Checkout_Cart_Configure afterExecute : ".$e->getMessage()
            );
        }
        return $result;
    }
}
