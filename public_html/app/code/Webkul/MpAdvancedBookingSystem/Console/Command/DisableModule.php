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
namespace Webkul\MpAdvancedBookingSystem\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Webkul MpAdvancedBookingSystem DisableModule Command
 */
class DisableModule extends Command
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private $eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    private $modStatus;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Framework\Module\Status $modStatus
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus
    ) {
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->eavAttribute = $entityAttribute;
        $this->modStatus = $modStatus;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mpadvancedbookingsystem:disable')
            ->setDescription('Marketplace Advanced Booking System Disable Command');
        parent::configure();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->moduleManager->isEnabled('Webkul_MpAdvancedBookingSystem')) {
            $connection = $this->resource
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            
            // delete MpAdvancedBookingSystem product attribute
            $this->eavAttribute->loadByCode('catalog_product', 'show_contact_button_to')->delete();
            $this->eavAttribute->loadByCode('catalog_product', 'renting_type')->delete();
            $this->eavAttribute->loadByCode('catalog_product', 'event_chart_image')->delete();
            $this->eavAttribute->loadByCode('catalog_product', 'price_charged_per')->delete();
            $this->eavAttribute->loadByCode('catalog_product', 'hotel_country')->delete();
            $this->eavAttribute->loadByCode('catalog_product', 'price_charged_per_hotel')->delete();
            $this->eavAttribute->loadByCode('catalog_product', 'price_charged_per_table')->delete();

            // disable MpAdvancedBookingSystem
            $this->modStatus->setIsEnabled(false, ['Webkul_MpAdvancedBookingSystem']);

            // delete entry from setup_module table
            $tableName = $connection->getTableName('setup_module');
            $where = [
                $connection->quoteIdentifier("module") . '=?' => "Webkul_MpAdvancedBookingSystem"
            ];
            $connection->delete($tableName, $where);

            $output->writeln('<info>Module Webkul_MpAdvancedBookingSystem has been disabled successfully.</info>');
        }
    }
}
