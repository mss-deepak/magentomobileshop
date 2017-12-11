<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\storeinfo;

class Getstoreinfo extends \Magento\Framework\App\Action\Action
{
    const RECIPIENT_EMAIL = 'configuration/contact_information/email';
    const STORE_PHONE     = 'configuration/contact_information/phone_number';
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Store\Model\Store $storeModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_storeManager     = $storeManager;
        $this->customHelper      = $customHelper;
        $this->storeModel        = $storeModel;
        $this->scopeConfig       = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $result         = $this->resultJsonFactory->create();
        try {
            $storeinfo = array();
            if ($this->scopeConfig->getValue(self::STORE_PHONE)) {
                $results['store_phoneno'] = $this->scopeConfig->getValue(self::STORE_PHONE);
            } else {
                $results['store_phoneno'] = $this->scopeConfig->getValue(self::STORE_PHONE);
            }
            if ($this->scopeConfig->getValue(self::RECIPIENT_EMAIL)) {
                $results['store_email'] = $this->scopeConfig->getValue(self::RECIPIENT_EMAIL);
            } else {
                $results['store_email'] = $this->scopeConfig->getValue(self::RECIPIENT_EMAIL);
            }
            $results['store_weburl'] = $this->_storeManager->getStore()->getBaseUrl();

            $storeinfo = $results;
            $result->setData(['status' => 'success', 'data' => $storeinfo]);
            return $result;
        } catch (\Exception $e) {
            $result->setData(['status' => 'error', 'data' => __('Problem in loading data')]);
            return $result;
        }
    }
}
