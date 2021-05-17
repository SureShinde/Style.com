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
namespace Webkul\MpAdvancedBookingSystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class ControllersDataSave implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Webkul\Marketplace\Model\ControllersRepository
     */
    private $controllersRepository;
    
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        
        // setup default
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        
        /**
         * insert sellerstorepickup controller's data
         */
        $data = [];

        if (!count($this->controllersRepository->getByPath('mpadvancebooking/product/add'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAdvancedBookingSystem',
                'controller_path' => 'mpadvancebooking/product/add',
                'label' => 'Booking Product Add',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mpadvancebooking/product/create'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAdvancedBookingSystem',
                'controller_path' => 'mpadvancebooking/product/create',
                'label' => 'Booking Product Create',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mpadvancebooking/product/bookinglist'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAdvancedBookingSystem',
                'controller_path' => 'mpadvancebooking/product/bookinglist',
                'label' => 'Booking Product List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mpadvancebooking/hotelbooking/questions'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAdvancedBookingSystem',
                'controller_path' => 'mpadvancebooking/hotelbooking/questions',
                'label' => 'Booking Questions List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
