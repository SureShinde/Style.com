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
namespace Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;

class Hotelbooking extends Widget
{
    /**
     * Reference to product objects that is being edited
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product = null;

    /**
     * Accordion block id
     *
     * @var string
     */
    protected $_blockId = 'hotelBookingInfo';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @param Context  $context
     * @param Registry $registry
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param DataPersistorInterface $dataPersistor
     * @param LocatorInterface $locator
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        DataPersistorInterface $dataPersistor,
        LocatorInterface $locator,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_backendUrl = $backendUrl;
        $this->dataPersistor = $dataPersistor;
        $this->locator = $locator;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->locator->getProduct()->getId() && $this->dataPersistor->get('catalog_product')) {
            return $this->resolvePersistentData();
        }
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Resolve data persistence
     *
     * @param array $data
     * @return array
     */
    private function resolvePersistentData()
    {
        $persistentData = (array)$this->dataPersistor->get('catalog_product');
        $data = [];
        if (!empty($persistentData['product'])) {
            $data = $persistentData['product'];
            if (!empty($data['slot_data'])) {
                unset($persistentData['product']['slot_data']);
                $this->dataPersistor->set('catalog_product', $persistentData);
            }
        }
        return $data;
    }

    /**
     * _prepareLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', true);
        return parent::_prepareLayout();
    }

    /**
     * getRegionUpdateUrl
     *
     * @return string
     */
    public function getRegionUpdateUrl()
    {
        return $this->_backendUrl->getUrl("directory/json/countryRegion");
    }
}
