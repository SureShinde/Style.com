<?php
namespace RockLab\Form\Model;

use Magento\Framework\Model\AbstractModel;

class Form extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('RockLab\Form\Model\ResourceModel\Form');
    }
}
