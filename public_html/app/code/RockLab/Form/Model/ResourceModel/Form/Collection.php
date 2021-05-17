<?php
namespace RockLab\Form\Model\ResourceModel\Form;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('RockLab\Form\Model\Form', 'RockLab\Form\Model\ResourceModel\Form');
    }
}
