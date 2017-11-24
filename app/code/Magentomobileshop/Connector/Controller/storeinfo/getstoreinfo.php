<?php
namespace Magentomobileshop\Connector\Controller\storeinfo;

class Getstoreinfo extends \Magento\Framework\App\Action\Action
{
    const RECIPIENT_EMAIL = 'configuration/contact_information/email';
    const STORE_PHONE = 'configuration/contact_information/phone_number';
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Store\Model\Store $storeModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager     = $storeManager;
        $this->customHelper      = $customHelper;
        $this->storeModel        = $storeModel;
        $this->scopeConfig       = $scopeConfig;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
    try{
        $storeinfo = array();
        if($this->scopeConfig->getValue(self::STORE_PHONE)):
            $result['store_phoneno'] = $this->scopeConfig->getValue(self::STORE_PHONE);
        else:
            $result['store_phoneno'] = $this->scopeConfig->getValue(self::STORE_PHONE);
        endif;

        if($this->scopeConfig->getValue(self::RECIPIENT_EMAIL)):
            $result['store_email'] = $this->scopeConfig->getValue(self::RECIPIENT_EMAIL);
        else:
            $result['store_email'] = $this->scopeConfig->getValue(self::RECIPIENT_EMAIL); 
        endif;  
        
            $result['store_weburl'] = $this->_storeManager->getStore()->getBaseUrl();
        
        $storeinfo = $result;

        echo json_encode(array('status'=>'success','data'=>$storeinfo));
        exit;
        }
        catch(exception $e){
            echo json_encode(array('status'=>'error','message'=> 'Problem in loading data.'));
            exit;
        }
    }

}
