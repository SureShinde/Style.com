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
namespace Webkul\MpAdvancedBookingSystem\Helper;

use Magento\Framework\Exception\MailException;

/**
 * Webkul MpAdvancedBookingSystem Helper Email.
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Contact us email template
     */
    const XML_PATH_EMAIL_CONTACT = 'mpadvancedbookingsystem/email/contact_template';

    /**
     * Hotel asked question email template
     */
    const XML_PATH_EMAIL_QUESTION = 'mpadvancedbookingsystem/email/question_template';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * selected template id
     */
    private $template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * generateTemplate
     *
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->transportBuilder->setTemplateIdentifier($this->template)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId(),
            ])
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        $this->transportBuilder->setReplyTo($senderInfo['email']);
        return $this;
    }

    /**
     * sendContactMailFromBuyer
     *
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    public function sendContactMailFromBuyer($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_CONTACT);

        $this->inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        
        $this->inlineTranslation->resume();
    }

    /**
     * sendAskedQuestionMail
     *
     * @param array $data
     * @param array $emailTemplateVariables
     * @param array $receiverInfo
     * @return void
     */
    public function sendAskedQuestionMail($data, $emailTemplateVariables, $receiverInfo)
    {
        $senderInfo = [
            'name' => $data['nick_name'],
            'email' => $this->getConfigValue(
                'trans_email/ident_support/email',
                $this->getStore()->getStoreId()
            )
        ];
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_QUESTION);

        $this->inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->inlineTranslation->resume();
    }
}
