<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\index;

class ClearCaches extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->cache        = $cache;
        $this->customHelper = $customHelper;
        $this->resultJsonFactory           = $resultJsonFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->cache->remove("mss_dashboard_store1");
        $this->cache->remove("mss_menu_store1");
        $result->setData(['status'=>'success', 'message'=>'Dashboard cache flush']);
        return $result;
    }
}
