<?php
namespace RockLab\Form\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class Form extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('form', 'id');
    }
}
