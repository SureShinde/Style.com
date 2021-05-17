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

namespace Webkul\MpAdvancedBookingSystem\Block\Product;

use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory as AnswersCollectionFactory;

/**
 * Webkul MpAdvancedBookingSystem View Block
 */
class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var object
     */
    protected $_questionsLists;

    /**
     * @var CollectionFactory
     */
    protected $questionCollection;

    /**
     * @var AnswersCollectionFactory
     */
    protected $answerCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context    $context
     * @param \Magento\Framework\Url\EncoderInterface             $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface            $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils               $string
     * @param \Magento\Catalog\Helper\Product                     $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface           $localeFormat
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface     $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface   $priceCurrency
     * @param CollectionFactory                                   $questionCollection
     * @param AnswersCollectionFactory                            $answerCollection
     * @param array                                               $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        CollectionFactory $questionCollection,
        AnswersCollectionFactory $answerCollection,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->questionCollection = $questionCollection;
        $this->answerCollection = $answerCollection;
    }

    /**
     * getAllQuestions
     *
     * @return bool|\Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\Collection
     */
    public function getAllQuestions()
    {
        if (!isset($this->questionsLists) && $this->getProduct()->getId()) {
            $paramData = $this->getRequest()->getParams();
            $questionText = '';

            if (isset($paramData['search_question'])) {
                $questionText = $paramData['search_question'] != '' ? $paramData['search_question'] : '';
            }

            $collection = $this->questionCollection->create()
                ->addFieldToFilter(
                    'product_id',
                    ['eq' => $this->getProduct()->getId()]
                )->addFieldToFilter(
                    'status',
                    ['eq' => \Webkul\MpAdvancedBookingSystem\Model\Question::STATUS_APPROVED]
                );

            if ($questionText) {
                $collection->addFieldToFilter(
                    'question',
                    ['like' => '%' . $questionText . '%']
                );
            }

            $collection->setOrder(
                'created_at',
                'desc'
            );
            $this->_questionsLists = $collection;
        }

        return $this->_questionsLists;
    }

    /**
     * _prepareLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllQuestions()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpbookingsystem.questions.pager'
            )->setCollection(
                $this->getAllQuestions()
            );
            $this->setChild('pager', $pager);
            $this->getAllQuestions()->load();
        }

        return $this;
    }

    /**
     * getPagerHtml
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * getAnswersList
     *
     * @param int $questionId
     * @return object|boolean
     */
    public function getAnswersList($questionId)
    {
        if ($questionId && $questionId!=="") {
            $collection = $this->answerCollection->create()
                ->addFieldToFilter('question_id', ['eq' => $questionId]);
            return $collection;
        }
        return false;
    }

    /**
     * getCurrentUrl
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
}
