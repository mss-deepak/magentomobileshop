<?php
namespace Magentomobileshop\Bannersliderapp\Model\ResourceModel;

class Grid extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_idFieldName = 'banner_id';
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }
    protected function _construct()
    {
        $this->_init('magentomobile_bannersliderapp', 'banner_id');
    }
}
