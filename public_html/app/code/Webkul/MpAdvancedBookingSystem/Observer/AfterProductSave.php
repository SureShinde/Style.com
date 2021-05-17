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
namespace Webkul\MpAdvancedBookingSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class AfterProductSave implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\InfoFactory
     */
    protected $_info;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\SlotFactory
     */
    protected $_slot;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $_indexerFactory;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $_indexerCollectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory
     */
    protected $_infoCollection;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper
     * @param \Webkul\MpAdvancedBookingSystem\Model\InfoFactory $info
     * @param \Webkul\MpAdvancedBookingSystem\Model\SlotFactory $slot
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper,
        \Webkul\MpAdvancedBookingSystem\Model\InfoFactory $info,
        \Webkul\MpAdvancedBookingSystem\Model\SlotFactory $slot,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_request = $request;
        $this->_bookingHelper = $bookingHelper;
        $this->_info = $info;
        $this->_slot = $slot;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        $this->configurable = $configurable;
        $this->_infoCollection = $infoCollectionFactory;
        $this->filesystem = $filesystem;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    /**
     * After save product event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $productId = $product->getId();
            $productType = $product->getTypeId();
            $data = $this->_request->getParams();
            $files = $this->_request->getFiles();

            if ($productType == "virtual") {
                $this->checkHotelChildProduct($productId, $data);
            }
            if ($productType != "booking" && $productType != "hotelbooking") {
                return;
            }
            $productSetId = $product->getAttributeSetId();
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            $helper = $this->_bookingHelper;

            $appointmentAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            $eventAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            $rentalAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            $hotelAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Hotel Booking'
            );
            $tableAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Table Booking'
            );
            $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
            if ($productSetId == $appointmentAttrSetId) {
                $this->saveAppointmentBooking($data, $productId, $productSetId);
            } elseif ($productSetId == $eventAttrSetId) {
                $this->saveEventBooking($product, $data, $productId, $productSetId);
            } elseif ($productSetId == $rentalAttrSetId) {
                $this->saveRentalBooking($product, $data, $productId, $productSetId);
            } elseif ($productSetId == $hotelAttrSetId) {
                $this->saveHotelBooking($data, $productId, $productSetId, $files);
            } elseif ($productSetId == $tableAttrSetId) {
                $this->saveTableBooking($data, $productId, $productSetId);
            } elseif (!in_array($productSetId, $allowedAttrSetIDs)) {
                $this->saveDefaultBooking($data, $productId, $productSetId);
            }
            $this->_bookingHelper->enableOptions($productId);
            $this->_bookingHelper->checkBookingProduct($productId);
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterProductSave execute : ".$e->getMessage()
            );
        }
    }

    /**
     * Save Appointment Booking Product.
     *
     * @param array $data
     */
    public function saveAppointmentBooking($data, $productId, $productSetId)
    {
        try {
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            if (empty($data['product']['slot_data'])) {
                return false;
            }
            $data['product'] = $this->getCalculatedSlotData($data['product']);
            $totalSlots = $data['product']['total_slots'];
            $qty = 0;
            if (isset($data['product']['quantity_and_stock_status']['qty'])) {
                $qty = $data['product']['quantity_and_stock_status']['qty'];
            } elseif (isset($data['product']['stock_data']['qty'])) {
                $qty = $data['product']['stock_data']['qty'];
            }

            if ($data['product']['slot_for_all_days']) {
                $data['product']['slot_data'][2] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][3] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][4] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][5] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][6] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][7] = $data['product']['slot_data'][1];
            }
            $slotDuration = $data['product']['slot_duration'];
            $slotBreakTime = $data['product']['break_time_bw_slot'];
            $totalSlotTime = $slotDuration + $slotBreakTime;
            foreach ($data['product']['slot_data'] as $dayKey => $dayValue) {
                $slotIndex = 1;
                $currentSlots = 0;
                if (!empty($dayValue)) {
                    foreach ($dayValue as $key => $value) {
                        if (empty($value['qty'])) {
                            $value['qty'] = 1;
                        }
                        if (!$data['product']['slot_has_quantity']) {
                            $value['qty'] = 1;
                        }
                        $availableSlotArr = [];
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        $currentSlots = $currentSlots + (int)($totalTimeStamp/$totalSlotTime);
                        $availableSlotArr[$slotIndex-1]['qty'] = $value['qty'];
                        $availableSlotArr[$slotIndex-1]['time'] = $value['from'];
                        for ($i=$slotIndex; $i < $currentSlots; $i++) {
                            $availableSlotArr[$i]['qty'] = $value['qty'];
                            $availableSlotArr[$i]['time'] = date(
                                "h:i a",
                                strtotime(
                                    '+'.$totalSlotTime.' minutes',
                                    strtotime($availableSlotArr[$i-1]['time'])
                                )
                            );
                        }
                        $slotIndex = $currentSlots;
                        $slotIndex++;
                        $data['product']['slot_data'][$dayKey][$key]['qty'] = $value['qty'];
                        $data['product']['slot_data'][$dayKey][$key]['slots_info'] = $availableSlotArr;
                    }
                }
            }
            $bookingSlotData = $helper->getJsonEcodedString($data['product']['slot_data']);

            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }
            $infoModelOld = null;

            $bookingProductId = 0;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
                $infoModelOld = $value;
            }

            $type = $data['product']['slot_for_all_days'];
            if ($this->canSaveAppointmentSlots($infoModelOld, $bookingSlotData, $type)) {
                $infoData = [
                    'attribute_set_id' => $productSetId,
                    'product_id' => $productId,
                    'type' => $type,
                    'start_date' => $data['product']['booking_available_from'],
                    'end_date' => $data['product']['booking_available_to'],
                    'info' => $bookingSlotData,
                    'qty' => $qty,
                    'total_slots' => $totalSlots
                ];
                $slotData = [
                    'product_id' => $productId,
                    'type' => $type,
                    'status' => 1
                ];
                $slotModel->load($slotProductId)
                ->setData($slotData)
                ->save();

                $infoModel->load($bookingProductId)
                ->addData($infoData)
                ->save();
            }
            $infoData = [
                'attribute_set_id' => $productSetId,
                'product_id' => $productId,
                'type' => $data['product']['slot_for_all_days'],
                'start_date' => $data['product']['booking_available_from'],
                'end_date' => $data['product']['booking_available_to'],
                'info' => $bookingSlotData,
                'qty' => $qty,
                'total_slots' => $totalSlots
            ];

            $infoModel->load($bookingProductId)
            ->addData($infoData)
            ->save();
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * @param object $infoModelOld
     * @param string $newJsonData
     * @param int $type
     * @param int $slotQty
     *
     * @return bool
     */
    public function canSaveAppointmentSlots($infoModelOld, $newJsonData, $type)
    {
        $helper = $this->_bookingHelper;
        if (empty($infoModelOld) || $infoModelOld->getType() != $type) {
            return true;
        }

        $oldJson = $helper->getJsonDecodedString($infoModelOld->getInfo());
        $newJson = $helper->getJsonDecodedString($newJsonData);

        if ($this->checkAppointmentSlotsData($oldJson, $newJson)) {
            return true;
        }
    }

    /**
     * @param array $oldSlots
     * @param array $newSlots
     *
     * @return bool
     */
    public function checkAppointmentSlotsData($oldSlots, $newSlots)
    {
        if (count($oldSlots) !== count($newSlots)) {
            return true;
        }

        foreach ($newSlots as $key => $daysData) {
            if (!isset($oldSlots[$key]) || count($oldSlots[$key]) !== count($daysData)) {
                return true;
            }
            
            foreach ($daysData as $slotKey => $slotsData) {
                if (!isset($oldSlots[$key][$slotKey])) {
                    return true;
                }
                $oldSlotData = $oldSlots[$key][$slotKey];
                if (isset($oldSlotData['from']) && $oldSlotData['from'] !== $slotsData['from']) {
                    return true;
                }
                
                if (isset($oldSlotData['to']) && $oldSlotData['to'] !== $slotsData['to']) {
                    return true;
                }
                
                if (isset($slotsData['slots_info']) && isset($oldSlotData['slots_info'])) {
                    $oldSlotInfo = $oldSlotData['slots_info'];
                    foreach ($slotsData['slots_info'] as $slotInfoKey => $slotInfo) {
                        if (isset($oldSlotInfo[$slotInfoKey]['qty'])
                            && $oldSlotInfo[$slotInfoKey]['qty'] !== $slotInfo['qty']
                        ) {
                            return true;
                        }

                        if (isset($oldSlotInfo[$slotInfoKey]['time'])
                            && $oldSlotInfo[$slotInfoKey]['time'] !== $slotInfo['time']
                        ) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * Save Event Booking Product.
     *
     * @param array $data
     */
    public function saveEventBooking($product, $data, $productId, $productSetId)
    {
        try {
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            if (empty($data['product']['options'])) {
                return false;
            }
            $eventOptions = $data['product']['options'];
            $optionIndex = 0;
            foreach ($eventOptions as $index => $option) {
                if ($option['title'] == 'Event Tickets') {
                    $optionIndex = $index;
                    break;
                }
            }
            $totalSlots = 0;
            $qty = 0;
            if (isset($data['product']['quantity_and_stock_status']['qty'])) {
                $qty = $data['product']['quantity_and_stock_status']['qty'];
            } elseif (isset($data['product']['stock_data']['qty'])) {
                $qty = $data['product']['stock_data']['qty'];
            }

            if (empty($eventOptions[$optionIndex]['values'])) {
                return false;
            }
            foreach ($eventOptions[$optionIndex]['values'] as $optionValue) {
                $totalSlots = $totalSlots + (int)$optionValue['qty'];
            }

            $updatedBookingOptions = [];
            $customOptions = $product->getOptions();
            $index = 0;
            foreach ($product->getProductOptionsCollection() as $key => $customOption) {
                $customOptionData = $customOption->getData();
                if (!empty($customOptionData['title']) && $customOptionData['title'] == 'Event Tickets') {
                    $updatedBookingOptions[$index] = $customOptionData;
                    $customOptionValues = $customOption->getValues() ?? [];
                    $optionValuesData = [];
                    foreach ($customOptionValues as $customOptionValue) {
                        $optionValuesData[] = $customOptionValue->getData();
                    }
                    $updatedBookingOptions[$index]['values'] = $optionValuesData;
                }
                $index++;
            }

            if (!empty($updatedBookingOptions)) {
                $bookingOptionsData = $helper->getJsonEcodedString($updatedBookingOptions);
            } else {
                $bookingOptionsData = $helper->getJsonEcodedString($eventOptions);
            }

            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }
            $slotData = [
                'product_id' => $productId,
                'type' => 1,
                'status' => 1
            ];
            $slotModel->load($slotProductId)
            ->setData($slotData)
            ->save();
            $bookingProductId = 0;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
            }
            $fromTime = date(
                'Y-m-d H:i',
                strtotime($data['product']['event_date_from'])
            );
            $toTime = date(
                'Y-m-d H:i',
                strtotime($data['product']['event_date_to'])
            );

            $infoData = [
                'attribute_set_id' => $productSetId,
                'product_id' => $productId,
                'type' => 1,
                'start_date' => $fromTime,
                'end_date' => $toTime,
                'info' => $bookingOptionsData,
                'qty' => $qty,
                'total_slots' => $totalSlots
            ];
            $infoModel->load($bookingProductId)
            ->addData($infoData)
            ->save();
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * Save Rental Booking Product.
     *
     * @param array $data
     */
    public function saveRentalBooking($product, $data, $productId, $productSetId)
    {
        try {
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            if (empty($data['product']['renting_type'])) {
                return false;
            }
            if (empty($data['product']['options'])) {
                return false;
            }
            $rentType = $data['product']['renting_type'];
            $qty = 0;
            if (isset($data['product']['quantity_and_stock_status']['qty'])) {
                $qty = $data['product']['quantity_and_stock_status']['qty'];
            } elseif (isset($data['product']['stock_data']['qty'])) {
                $qty = $data['product']['stock_data']['qty'];
            }
            $availableQty = $qty;
            if (!empty($data['product']['available_qty']) && $data['product']['available_qty']) {
                $availableQty = $data['product']['available_qty'];
            }
            if ($rentType != 1) {
                if (empty($data['product']['slot_data'])) {
                    return false;
                }
                $data['product'] = $this->getCalculatedRentSlotData($data['product']);
                $totalSlots = $data['product']['total_slots'];
                $totalSlotTime = 60; //60 mins
                foreach ($data['product']['slot_data'] as $dayKey => $dayValue) {
                    $slotIndex = 1;
                    $currentSlots = 0;
                    if (empty($dayValue)) {
                        continue;
                    }
                    foreach ($dayValue as $key => $value) {
                        if (empty($value['qty'])) {
                            $value['qty'] = $availableQty;
                        }
                        if (!$data['product']['slot_has_quantity']) {
                            $value['qty'] = $availableQty;
                        }
                        $availableSlotArr = [];
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        $currentSlots = $currentSlots + (int)($totalTimeStamp/$totalSlotTime);
                        $availableSlotArr[$slotIndex-1]['qty'] = $value['qty'];
                        $availableSlotArr[$slotIndex-1]['time'] = $value['from'];
                        for ($i=$slotIndex; $i <= $currentSlots; $i++) {
                            $availableSlotArr[$i]['qty'] = $value['qty'];
                            $availableSlotArr[$i]['time'] = date(
                                "h:i a",
                                strtotime(
                                    '+'.$totalSlotTime.' minutes',
                                    strtotime($availableSlotArr[$i-1]['time'])
                                )
                            );
                        }
                        $slotIndex = $currentSlots;
                        $slotIndex++;
                        $data['product']['slot_data'][$dayKey][$key]['qty'] = $value['qty'];
                        $data['product']['slot_data'][$dayKey][$key]['slots_info'] = $availableSlotArr;
                    }
                }
                if ($data['product']['slot_for_all_days']) {
                    $data['product']['slot_data'][2] = $data['product']['slot_data'][1];
                    $data['product']['slot_data'][3] = $data['product']['slot_data'][1];
                    $data['product']['slot_data'][4] = $data['product']['slot_data'][1];
                    $data['product']['slot_data'][5] = $data['product']['slot_data'][1];
                    $data['product']['slot_data'][6] = $data['product']['slot_data'][1];
                    $data['product']['slot_data'][7] = $data['product']['slot_data'][1];
                }
                $bookingSlotData = $helper->getJsonEcodedString($data['product']['slot_data']);
            } else {
                if ($data['product']['available_every_week']) {
                    $totalSlots = 99999999;
                } else {
                    $startDate = $data['product']['booking_available_from'];
                    $endDate = $data['product']['booking_available_to'];
                    $numOfDays = $this->getDateDifference($startDate, $endDate);
                    $totalSlots = $availableQty * $numOfDays;
                }
                $bookingSlotData = $helper->getJsonEcodedString([]);
            }
            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }

            $bookingProductId = 0;
            $infoModelOld = null;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
                $infoModelOld = $value;
            }
            if ($this->canSaveRentalSlots(
                $infoModelOld,
                $bookingSlotData,
                $rentType,
                $data['product']['booking_available_from'],
                $data['product']['booking_available_to'],
                $availableQty
            )) {
                $infoData = [
                    'attribute_set_id' => $productSetId,
                    'product_id' => $productId,
                    'type' => $rentType,
                    'start_date' => $data['product']['booking_available_from'],
                    'end_date' => $data['product']['booking_available_to'],
                    'info' => $bookingSlotData,
                    'qty' => $qty,
                    'available_qty' => $availableQty,
                    'total_slots' => $totalSlots
                ];
                $slotData = [
                    'product_id' => $productId,
                    'type' => $rentType,
                    'status' => 1
                ];
                $slotModel->load($slotProductId)
                ->setData($slotData)
                ->save();
                
                $infoModel->load($bookingProductId)
                ->addData($infoData)
                ->save();
            }

        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * Check Whether New Slots or Not
     *
     * @param array  $infoModelOld
     * @param string $newJsonData
     * @param int    $type
     * @param string $dateFrom
     * @param string $dateTo
     * @param int    $availableQty
     *
     * @return bool
     */
    public function canSaveRentalSlots($infoModelOld, $newJsonData, $type, $dateFrom, $dateTo, $availableQty)
    {
        $helper = $this->_bookingHelper;
        if ($infoModelOld === null) {
            return true;
        }

        if ($infoModelOld->getAvailableQty() != $availableQty) {
            return true;
        }

        if ($infoModelOld->getType() != $type) {
            return true;
        }

        if ($infoModelOld->getStartDate() != $dateFrom || $infoModelOld->getEndDate() != $dateTo) {
            return true;
        }
        
        $oldJson = $helper->getJsonDecodedString($infoModelOld->getInfo());
        $newJson = $helper->getJsonDecodedString($newJsonData);
        if ($this->checkRentalSlotsData($oldJson, $newJson)) {
            return true;
        }
    }

    /**
     * @param array $oldSlots
     * @param array $newSlots
     *
     * @return bool
     */
    public function checkRentalSlotsData($oldSlots, $newSlots)
    {
        foreach ($newSlots as $key => $daysData) {
            if (!isset($oldSlots[$key]) || count($oldSlots[$key]) !== count($daysData)) {
                return true;
            }
            
            foreach ($daysData as $slotKey => $slotsData) {
                if (isset($oldSlots[$key][$slotKey])) {
                    $oldSlotData = $oldSlots[$key][$slotKey];
                    if (isset($oldSlotData['from']) && $oldSlotData['from'] !== $slotsData['from']) {
                        return true;
                    }
                    
                    if (isset($oldSlotData['to']) && $oldSlotData['to'] !== $slotsData['to']) {
                        return true;
                    }
                    
                    if (isset($oldSlotData['qty']) && $oldSlotData['qty'] !== $slotsData['qty']) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * Save Hotel Booking Product.
     *
     * @param array $data
     */
    public function saveHotelBooking($data, $productId, $productSetId, $files)
    {
        try {
            $totalSlots = 0;
            $slotProductId = 0;
            $bookingProductId = 0;
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();

            if (empty($data['product_id']) && !empty($files) && !empty($files['amenities_icon'])) {
                foreach ($files['amenities_icon'] as $key => $amenitiyIcon) {
                    $target = $this->_mediaDirectory->getAbsolutePath(
                        'catalog/product/'.$productId.'/'.$key.'/'
                    );
                    $removeDir = $this->filesystem->getDirectoryRead(
                        DirectoryList::MEDIA
                    )->getAbsolutePath(
                        'catalog/product/'.$productId.'/'.$key.'/'
                    );
                    $this->deleteImage($removeDir);
                    if (!empty($amenitiyIcon['tmp_name'])) {
                        $this->uploadImageToDirectory($target, $key);
                    }
                }
            }
            if (empty($data['product']['configurable_attributes_data'])) {
                return false;
            }

            $bookingOptions = $helper->getJsonDecodedString($data['configurable-matrix-serialized']);

            if (empty($bookingOptions)) {
                return false;
            }
            $newBookingOptions = [];
            $hotelBookingProduct = $helper->getProduct($productId);
            $productTypeInstance = $hotelBookingProduct->getTypeInstance();
            $usedProducts = $productTypeInstance->getUsedProducts($hotelBookingProduct);
            foreach ($usedProducts as $child) {
                $childStockData = $helper->getStockData($child->getId());
                $newBookingOptions[$child->getId()] = [
                    'sku' => $child->getSku(),
                    'qty' => $childStockData->getQty(),
                    'price' => $child->getPrice(),
                    'status' => $child->getStatus(),
                ];
                $totalSlots += (int)$childStockData->getQty();
            }
            $qty = $totalSlots;
            $bookingOptionsData = $helper->getJsonEcodedString($newBookingOptions);

            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }
            $slotData = [
                'product_id' => $productId,
                'type' => 1,
                'status' => 1
            ];
            $slotModel->load($slotProductId)
                ->addData($slotData)
                ->save();

            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
            }
            $infoData = [
                'attribute_set_id' => $productSetId,
                'product_id' => $productId,
                'type' => 1,
                'start_date' => '',
                'end_date' => '',
                'info' => $bookingOptionsData,
                'qty' => $qty,
                'total_slots' => $totalSlots
            ];
            $infoModel->load($bookingProductId)
                ->addData($infoData)
                ->save();
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * [deleteImage deletes image]
     *
     * @param  [string] $path [contains path]
     * @return [object|boolean]
     */
    public function deleteImage($path)
    {
        try {
            $directory = $this->_mediaDirectory;
            $result = $directory->delete($directory->getRelativePath($path));
            return true;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("deleteImage : ".$e->getMessage());
            return false;
        }
    }

    public function uploadImageToDirectory($target, $fileId)
    {
        try {
            $uploader = $this->_fileUploaderFactory
                ->create(
                    ['fileId' => 'amenities_icon['.$fileId.']']
                );
            $image = $uploader->validateFile();

            if (isset($image['tmp_name'])
                && $image['tmp_name'] !== ''
                && $image['tmp_name'] !== null
            ) {
                $imageCheck = getimagesize($image['tmp_name']);

                if ($imageCheck['mime']) {
                    $image['name'] = str_replace(" ", "_", $image['name']);
                    $imgName = rand(1, 99999).$image['name'];

                    $uploader->setAllowedExtensions(
                        ['jpg', 'jpeg', 'gif', 'png']
                    );
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($target, $imgName);
                }
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("uploadImageToDirectory : ".$e->getMessage());
        }
    }

    /**
     * Save Table Booking Product.
     *
     * @param array $data
     */
    public function saveTableBooking($data, $productId, $productSetId)
    {
        try {
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            if (empty($data['product']['slot_data'])) {
                return false;
            }
            $data['product'] = $this->getCalculatedSlotDataForTableBooking($data['product']);
            $totalSlots = $data['product']['total_slots'];
            $qty = 0;
            if (isset($data['product']['quantity_and_stock_status']['qty'])) {
                $qty = $data['product']['quantity_and_stock_status']['qty'];
            } elseif (isset($data['product']['stock_data']['qty'])) {
                $qty = $data['product']['stock_data']['qty'];
            }

            if ($data['product']['slot_for_all_days']) {
                $data['product']['slot_data'][2] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][3] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][4] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][5] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][6] = $data['product']['slot_data'][1];
                $data['product']['slot_data'][7] = $data['product']['slot_data'][1];
            }

            $slotDuration = $data['product']['slot_duration'];
            $slotBreakTime = $data['product']['break_time_bw_slot'];
            $totalSlotTime = $slotDuration + $slotBreakTime;
            foreach ($data['product']['slot_data'] as $dayKey => $dayValue) {
                $slotIndex = 1;
                $currentSlots = 0;
                if (!empty($dayValue)) {
                    foreach ($dayValue as $key => $value) {
                        if (empty($value['qty'])) {
                            $value['qty'] = $data['product']['max_capacity'];
                        }
                        $availableSlotArr = [];
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        $currentSlots = $currentSlots + (int)($totalTimeStamp/$totalSlotTime);
                        $availableSlotArr[$slotIndex-1]['qty'] = $value['qty'];
                        $availableSlotArr[$slotIndex-1]['time'] = $value['from'];
                        for ($i=$slotIndex; $i < $currentSlots; $i++) {
                            $availableSlotArr[$i]['qty'] = $value['qty'];
                            $availableSlotArr[$i]['time'] = date(
                                "h:i a",
                                strtotime(
                                    '+'.$totalSlotTime.' minutes',
                                    strtotime($availableSlotArr[$i-1]['time'])
                                )
                            );
                        }
                        $slotIndex = $currentSlots;
                        $slotIndex++;
                        $data['product']['slot_data'][$dayKey][$key]['slots_info'] = $availableSlotArr;
                    }
                }
            }
            $bookingSlotData = $helper->getJsonEcodedString($data['product']['slot_data']);

            $slotProductId = 0;
            $slotCollection = $slotModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($slotCollection as $value) {
                $slotProductId = $value->getId();
            }
            $infoModelOld = null;

            $bookingProductId = 0;
            $bookingCollection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            foreach ($bookingCollection as $value) {
                $bookingProductId = $value->getId();
                $infoModelOld = $value;
            }
            $type = $data['product']['slot_for_all_days'];
            $slotQty = $data['product']['max_capacity'];
            if ($this->canSaveTableSlots($infoModelOld, $bookingSlotData, $type, $slotQty)) {
                $infoData = [
                    'attribute_set_id' => $productSetId,
                    'product_id' => $productId,
                    'type' => $type,
                    'info' => $bookingSlotData,
                    'qty' => $qty,
                    'total_slots' => $totalSlots
                ];
                $slotData = [
                    'product_id' => $productId,
                    'type' => $type,
                    'status' => 1
                ];
                $slotModel->load($slotProductId)
                ->setData($slotData)
                ->save();

                $infoModel->load($bookingProductId)
                ->addData($infoData)
                ->save();
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * @param object $infoModelOld
     * @param string $newJsonData
     * @param int $type
     * @param int $slotQty
     *
     * @return bool
     */
    public function canSaveTableSlots($infoModelOld, $newJsonData, $type, $slotQty)
    {
        $helper = $this->_bookingHelper;
        if (empty($infoModelOld) || $infoModelOld->getType() != $type) {
            return true;
        }

        $oldJson = $helper->getJsonDecodedString($infoModelOld->getInfo());
        $newJson = $helper->getJsonDecodedString($newJsonData);

        if ($this->checkTableSlotsData($oldJson, $newJson, $slotQty)) {
            return true;
        }
    }

    /**
     * @param array $oldSlots
     * @param array $newSlots
     *
     * @return bool
     */
    public function checkTableSlotsData($oldSlots, $newSlots, $slotQty)
    {
        if (count($oldSlots) !== count($newSlots)) {
            return true;
        }
        foreach ($newSlots as $key => $daysData) {
            if (!isset($oldSlots[$key]) || count($oldSlots[$key]) !== count($daysData)) {
                return true;
            }
            
            foreach ($daysData as $slotKey => $slotsData) {
                if (!isset($oldSlots[$key][$slotKey])) {
                    return true;
                }
                $oldSlotData = $oldSlots[$key][$slotKey];
                if (isset($oldSlotData['from']) && $oldSlotData['from'] !== $slotsData['from']) {
                    return true;
                }
                
                if (isset($oldSlotData['to']) && $oldSlotData['to'] !== $slotsData['to']) {
                    return true;
                }
                
                if (isset($slotsData['slots_info']) && isset($oldSlotData['slots_info'])) {
                    $oldSlotInfo = $oldSlotData['slots_info'];
                    foreach ($slotsData['slots_info'] as $slotInfoKey => $slotInfo) {
                        if (isset($oldSlotInfo[$slotInfoKey]['qty'])
                            && $oldSlotInfo[$slotInfoKey]['qty'] !== $slotInfo['qty']
                        ) {
                            return true;
                        }

                        if (isset($oldSlotInfo[$slotInfoKey]['time'])
                            && $oldSlotInfo[$slotInfoKey]['time'] !== $slotInfo['time']
                        ) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * Save Default Booking Product.
     *
     * @param array $data
     */
    public function saveDefaultBooking($data, $productId)
    {
        try {
            $helper = $this->_bookingHelper;
            $infoModel = $this->_info->create();
            $slotModel = $this->_slot->create();
            $bookingType = $this->getBookingType($data, $productId);
            $isNew = false;
            $collection = $infoModel->getCollection()
                ->addFieldToFilter("product_id", $productId);
            if ($collection->getSize()<=0) {
                $isNew = true;
            }
            if ($collection->getSize()<=0 && $bookingType == 0) {
                return;
            } else {
                $previousBookingType = $helper->getBookingType($productId);
                if ($bookingType == 0 && $previousBookingType == 0) {
                    return;
                }
            }
            if ($bookingType == 0 || ($bookingType==2 && !array_key_exists("start", $data['info']))) {
                $helper->disableSlots($productId);
                $helper->deleteInfo($productId);
                return;
            }

            if (!array_key_exists("info", $data)) {
                return;
            }

            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $qty = 0;
            if (isset($data['product']['quantity_and_stock_status']['qty'])) {
                $qty = $data['product']['quantity_and_stock_status']['qty'];
            } elseif (isset($data['product']['stock_data']['qty'])) {
                $qty = $data['product']['stock_data']['qty'];
            }

            $result = $this->prepareOptions($data, $bookingType);

            if (!empty($result)) {
                $bookingInfo = $result['info'];

                $count = $result['total'];
                //Setting Booking Information
                // $bookingInfo = $helper->getSerializedString($bookingInfo);
                $bookingInfo = $helper->getJsonEcodedString($bookingInfo);
                if (!$isNew && $isNew==false) {
                    $bookingData = $helper->getBookingInfo($productId);

                    if ($this->canSaveSlots($bookingData, $bookingInfo, $qty)) {
                        $helper->disableSlots($productId);
                        $slotData = [
                            'product_id' => $productId,
                            'type' => $bookingType,
                            'status' => 1
                        ];

                        $slotModel->setData($slotData)->save();
                    }
                } else {
                    $slotData = [
                        'product_id' => $productId,
                        'type' => $bookingType,
                        'status' => 1
                    ];
                    $slotModel->setData($slotData)->save();
                }

                $infoData = [
                    'product_id' => $productId,
                    'type' => $bookingType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'info' => $bookingInfo,
                    'qty' => $qty,
                    'total_slots' => $count
                ];
                $collection = $infoModel->getCollection();
                $item = $helper->getDataByField($productId, 'product_id', $collection);
                if ($item) {
                    $id = $item->getId();
                    $infoModel->addData($infoData)->setId($id)->save();
                } else {
                    $infoModel->setData($infoData)->save();
                }
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
    }

    /**
     * Get Booking Type
     *
     * @param array $data
     *
     * @return int
     */
    public function getBookingType($data, $productId)
    {
        $bookingType = 0;
        try {
            if (array_key_exists("booking_type", $data)) {
                $bookingType = $data['booking_type'];
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterProductSave getBookingType : ".$e->getMessage()
            );
        }

        return (int)$bookingType;
    }

    /**
     * Check Whether New Slots or Not
     *
     * @param array  $bookingData
     * @param string $bookingInfo
     * @param int    $qty
     *
     * @return bool
     */
    public function canSaveSlots($bookingData, $bookingInfo, $qty)
    {
        try {
            $helper = $this->_bookingHelper;
            if ($bookingData['is_booking']) {
                if (is_array($bookingData['info'])) {
                    $tempInfo = $helper->getJsonEcodedString($bookingData['info']);
                } else {
                    $tempInfo = $bookingData['info'];
                }

                if (strcmp($bookingInfo, $tempInfo) !== 0) {
                    return true;
                } else {
                    if ($bookingData['qty'] != $qty) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterProductSave_canSaveSlots Exception : ".$e->getMessage()
            );
        }

        return false;
    }

    /**
     * Prepare Options
     *
     * @param array $data
     * @param int   $bookingType
     *
     * @return array
     */
    public function prepareOptions($data, $bookingType)
    {
        $result = [];
        try {
            if ($bookingType == 1) {
                $result = $this->prepareManyBookingOptions($data);
            } elseif ($bookingType == 2) {
                $result = $this->prepareOneBookingOptions($data);
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("Observer_AfterProductSave prepareOptions : ".$e->getMessage());
        }
        return $result;
    }

    /**
     * Prepare Many Booking Options
     *
     * @param array $data
     *
     * @return array
     */
    public function prepareManyBookingOptions($data)
    {
        try {
            $count = 1;
            $info = $data['info'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $slotTime = $data['time_slot'];
            $breakTime = $data['break_time'];
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            for ($i = 0; $i <= $numOfDays; $i++) {
                $date = strtotime("+$i day", strtotime($startDate));
                $day = strtolower(date("l", $date));
                $status = $info[$day]['status'];
                if ($status == 1) {
                    $startHour = $info[$day]['start_hour'];
                    $startMinute = $info[$day]['start_minute'];
                    $endHour = $info[$day]['end_hour'];
                    $endMinute = $info[$day]['end_minute'];
                    $startCount = $startHour*60 + $startMinute;
                    $endCount = $endHour*60 + $endMinute;
                    $diff = $endCount - $startCount;
                    while ($diff >= $slotTime) {
                        $diff = $diff - ($breakTime + $slotTime);
                        $count++;
                    }
                }
            }

            unset($data['info']['start_hour']);
            unset($data['info']['start_minute']);
            unset($data['info']['end_hour']);
            unset($data['info']['end_minute']);
            $bookingInfo = $data['info'];
            $bookingInfo['time_slot'] = $slotTime;
            $bookingInfo['break_time'] = $breakTime;
            $result = [];
            $result['info'] = $bookingInfo;
            $result['start_date'] = $startDate;
            $result['end_date'] = $endDate;
            $result['total'] = $count-1;
            return $result;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterProductSave prepareManyBookingOptions : ".$e->getMessage()
            );
        }
    }

    /**
     * Prepare One Booking Options
     *
     * @param array $data
     *
     * @return array
     */
    public function prepareOneBookingOptions($data)
    {
        try {
            $count = 1;
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            $startData = $data['info']['start'];
            $endData = $data['info']['end'];
            $startDays = $startData['day'];

            for ($i = 0; $i <= $numOfDays; $i++) {
                $date = strtotime("+$i day", strtotime($startDate));
                $day = strtolower(date("l", $date));
                $date = strtolower(date("Y-m-d", $date));
                foreach ($startDays as $key => $startDay) {
                    if ($day == $startDay) {
                        $count++;
                    }
                }
            }

            $bookingInfo = ['start' => $startData, 'end' => $endData];
            $result = [];
            $result['info'] = $bookingInfo;
            $result['total'] = $count-1;
            return $result;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterProductSave_prepareOneBookingOptions Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Get Difference  of Dates
     *
     * @param string $firstDate
     * @param string $lastDate
     *
     * @return int
     */
    public function getDateDifference($firstDate, $lastDate)
    {
        try {
            $date1 = date_create($firstDate);
            $date2 = date_create($lastDate);
            $diff = date_diff($date1, $date2);
            $numOfDays = (int)$diff->format("%R%a");
            $numOfDays++; // TODO: temp. fix to get exact no. of days.
            return $numOfDays;
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("Observer_AfterProductSave getDateDifference : ".$e->getMessage());
        }
    }

    /**
     * Get Slot Data
     *
     * @param array $data
     *
     * @return array $data
     */
    public function getCalculatedSlotData($data)
    {
        try {
            $count = 1;
            $startDate = $data['booking_available_from'];
            $endDate = $data['booking_available_to'];
            $days = 7;
            $totalDaysCountPerWeek = 0;
            if ($data['slot_for_all_days']) {
                $days = 1;
                $totalDaysCountPerWeek = 7;
            }
            $totalSlots = 0;
            $dayWiseSlots = [];
            for ($i = 1; $i <= $days; $i++) {
                if (empty($data['slot_data'][$i])) {
                    continue;
                }

                if (!$data['slot_for_all_days']) {
                    $totalDaysCountPerWeek++;
                }
                foreach ($data['slot_data'][$i] as $key => $value) {
                    $slotFromTimeStamp = strtotime($value['from']);
                    $slotToTimeStamp = strtotime($value['to']);
                    if ($slotToTimeStamp > $slotFromTimeStamp) {
                        $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                    } else {
                        unset($data['slot_data'][$i][$key]);
                        $totalTimeStamp = 0;
                    }
                    if (empty($totalTimeStamp)) {
                        continue;
                    }

                    $slotDuration = (float)$data['slot_duration'];
                    $slotBreakTime = (float)$data['break_time_bw_slot'];
                    $currentSlots = (int)(($totalTimeStamp)/($slotDuration + $slotBreakTime));
                    if (!empty($data['slot_data'][$i][$key]['qty'])) {
                        $currentSlots = $currentSlots * $data['slot_data'][$i][$key]['qty'];
                    } else {
                        $data['slot_data'][$i][$key]['qty'] = 1;
                    }
                    $totalSlots = $totalSlots + $currentSlots;
                    if (isset($dayWiseSlots[$i])) {
                        $dayWiseSlots[$i] = $dayWiseSlots[$i] + $currentSlots;
                    } else {
                        $dayWiseSlots[$i] = $currentSlots;
                    }
                }
            }

            if ($data['available_every_week']) {
                $data['total_slots'] = 99999999;
                return $data;
            }
            
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            $daysCount = [];
            if (!$data['slot_for_all_days']) {
                if ($numOfDays > 0) {
                    $from = date_create($startDate);
                    $to = date_create($endDate);
                    $to->modify('+1 day');
                    $interval = new \DateInterval('P1D');
                    $periods = new \DatePeriod($from, $interval, $to);
                    
                    foreach ($periods as $period) {
                        if (isset($daysCount[$period->format('w')])) {
                            $daysCount[$period->format('w')] = $daysCount[$period->format('w')] + 1;
                        } else {
                            $daysCount[$period->format('w')] = 1;
                        }
                    }
                }
            } else {
                if (!empty($dayWiseSlots)) {
                    $allSlots = 0;
                    foreach ($dayWiseSlots as $slots) {
                        $allSlots = $allSlots + $slots;
                    }
                    $totalSlots = ($numOfDays+1) * $allSlots;
                }
            }

            if (!empty($daysCount)) {
                $totalSlots = 0;
                foreach ($daysCount as $dayNumber => $count) {
                    $dayIndex = ($dayNumber == 0) ? 7 : $dayNumber;
                    
                    if (isset($dayWiseSlots[$dayIndex])) {
                        $totalSlots = $totalSlots + ($dayWiseSlots[$dayIndex]*$count);
                    }
                }
            }
        } catch (\Exception $e) {
            $totalSlots = 0;
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
        $data['total_slots'] = $totalSlots;
        return $data;
    }

    /**
     * Get Rent Slot Data
     *
     * @param array $data
     *
     * @return array $data
     */
    public function getCalculatedRentSlotData($data)
    {
        try {
            $count = 1;
            $startDate = $data['booking_available_from'];
            $endDate = $data['booking_available_to'];
            $days = 7;
            $totalDaysCountPerWeek = 0;
            if ($data['slot_for_all_days']) {
                $days = 1;
                $totalDaysCountPerWeek = 7;
            }
            $totalSlots = 0;
            for ($i = 1; $i <= $days; $i++) {
                if (!empty($data['slot_data'][$i])) {
                    if (!$data['slot_for_all_days']) {
                        $totalDaysCountPerWeek++;
                    }
                    foreach ($data['slot_data'][$i] as $key => $value) {
                        if (empty($data['slot_data'][$i][$key]['qty'])) {
                            $data['slot_data'][$i][$key]['qty'] = 1;
                        }
                        $totalSlots = $totalSlots + $data['slot_data'][$i][$key]['qty'];
                    }
                }
            }
            if ($data['available_every_week']) {
                $totalSlots = 99999999;
            } else {
                $numOfDays = $this->getDateDifference($startDate, $endDate);
                $totalWeeks = round($numOfDays/7);
                if ($totalWeeks) {
                    $totalDays = $totalWeeks * $totalDaysCountPerWeek;
                    $totalSlots = $totalSlots * $totalDays;
                }
            }
        } catch (\Exception $e) {
            $totalSlots = 0;
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
        $data['total_slots'] = $totalSlots;
        return $data;
    }

    public function checkHotelChildProduct($productId, $data)
    {
        $parentProducts = $this->configurable->getParentIdsByChild($productId);
        if ($parentProducts && !empty($parentProducts) && is_array($parentProducts)) {
            $collection = $this->_infoCollection->create()
                ->addFieldToFilter("product_id", ['in' => $parentProducts]);
            if ($collection->getSize()) {
                foreach ($collection as $info) {
                    $bookingInfoData = $this->_bookingHelper->getJsonDecodedString(
                        $info->getInfo()
                    );
                    if (array_key_exists($productId, $bookingInfoData)) {
                        $qty = 0;

                        if (isset($data['product']['quantity_and_stock_status']['qty'])) {
                            $qty = $data['product']['quantity_and_stock_status']['qty'];
                        } elseif (isset($data['product']['stock_data']['qty'])) {
                            $qty = $data['product']['stock_data']['qty'];
                        }

                        $updatedQty = $qty;
                        $bookingInfoData[$productId]['qty'] = $updatedQty;
                        $infoData = $this->_bookingHelper->getJsonEcodedString($bookingInfoData);
                        $info->setInfo($infoData)->save();
                    }
                }
            }
        }
    }

    /**
     * Get Slot Data for Table type Booking
     *
     * @param array $data
     *
     * @return array $data
     */
    public function getCalculatedSlotDataForTableBooking($data)
    {
        try {
            $count = 1;
            $days = 7;
            $totalSlots = 0;
            $totalDaysCountPerWeek = 0;
            if ($data['slot_for_all_days']) {
                $days = 1;
                $totalDaysCountPerWeek = 7;
            }
            for ($i = 1; $i <= $days; $i++) {
                if (!empty($data['slot_data'][$i])) {
                    if (!$data['slot_for_all_days']) {
                        $totalDaysCountPerWeek++;
                    }
                    foreach ($data['slot_data'][$i] as $key => $value) {
                        $slotFromTimeStamp = strtotime($value['from']);
                        $slotToTimeStamp = strtotime($value['to']);
                        if ($slotToTimeStamp > $slotFromTimeStamp) {
                            $totalTimeStamp = ($slotToTimeStamp - $slotFromTimeStamp)/60; // in hours
                        } else {
                            unset($data['slot_data'][$i][$key]);
                            $totalTimeStamp = 0;
                        }
                        if ($totalTimeStamp) {
                            $slotDuration = (float)$data['slot_duration'];
                            $slotBreakTime = (float)$data['break_time_bw_slot'];
                            $currentSlots = (int)(($totalTimeStamp)/($slotDuration + $slotBreakTime));
                            $totalSlots += $currentSlots;
                        }
                    }
                }
            }
            $totalSlots = 99999999;
        } catch (\Exception $e) {
            $totalSlots = 0;
            $this->_bookingHelper->logDataInLogger($e->getMessage());
        }
        $data['total_slots'] = $totalSlots;
        return $data;
    }
}
