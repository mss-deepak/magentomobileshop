<?php
namespace Magentomobileshop\Connector\Controller\index;

class clearCaches extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->cache        = $cache;
        $this->customHelper = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->cache->remove("mss_dashboard_store1");
        $this->cache->remove("mss_menu_store1");
        echo json_encode(array('status' => 'success'));
    }

}
