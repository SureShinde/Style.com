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
namespace Webkul\MpAdvancedBookingSystem\Model\Source\Product\Attribute\Backend;

class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var string
     */
    private $additionalData = '_additional_data_';

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
    }

    /**
     * Save uploaded file and set its name to product
     *
     * @param  \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $object->getData($attributeName);
        try {
            $name = '';
            if (is_array($value) && isset($value[0]['name'])) {
                $name = $value[0]['name'];
            }
            if ($name) {
                $object->setData($this->additionalData . $attributeName, $value);
                $object->setData($attributeName, $name);
            } elseif (!is_string($value)) {
                $object->setData($attributeName, '');
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $this;
    }
}
