<?php
namespace Magentomobileshop\Bannersliderapp\Model\ResourceModel\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
   
    protected $_idFieldName = 'banner_id';
    protected function _construct()
    {
        $this->_init('Magentomobileshop\Bannersliderapp\Model\Grid', 'Magentomobileshop\Bannersliderapp\Model\ResourceModel\Grid');
    }
}
