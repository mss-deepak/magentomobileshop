<?php
namespace Magentomobileshop\Connector\Controller\token;

class getconfiguration extends \Magento\Framework\App\Action\Action
{

    const XML_SETTING_ACTIVE           = 'wishlist/general/active';
    const XML_SETTING_GUEST_REVIEW     = 'catalog/review/allow_guest';
    const XML_SETTING_GUEST_CHECKOUT   = 'checkout/options/guest_checkout';
    const XML_SETTING_GOOGLE_CLIENT_ID = 'mss_social/mss_google_key/client_id';
    const XML_SETTING_GOOGLE_SECRET_ID = 'mss_social/mss_google_key/client_secret';
    const XML_SETTING_FACEBOOK_ID      = 'mss_social/mss_facebook_key/facebook_id';
    const XML_SETTING_GOOGLE_SENDER_ID = 'mss_pushnotification/setting_and/googlesenderid';
    const XML_DEFAULT_STORE_LANG       = 'general/locale/code';

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->scopeConfig  = $scopeConfig;
        $this->resolver     = $resolver;
        $this->customHelper = $customHelper;
        $this->storeManager = $storeManager;

    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $config_data    = array();
        $storeId        = $this->storeManager->getStore()->getId();
        //  $local = $this->scopeConfig->getValue(self::XML_DEFAULT_STORE_LANG, $storeId);
        $local                             = $this->scopeConfig->getValue(self::XML_DEFAULT_STORE_LANG);
        $config_data['wishlist']           = $this->scopeConfig->getValue(self::XML_SETTING_ACTIVE);
        $config_data['review_allow_guest'] = $this->scopeConfig->getValue(self::XML_SETTING_GUEST_REVIEW);
        $config_data['guestcheckout']      = $this->scopeConfig->getValue(self::XML_SETTING_GUEST_CHECKOUT);

        $config_data['google_clientid']        = $this->scopeConfig->getValue(self::XML_SETTING_GOOGLE_CLIENT_ID);
        $config_data['google_secretid']        = $this->scopeConfig->getValue(self::XML_SETTING_GOOGLE_SECRET_ID);
        $config_data['facebook_id']            = $this->scopeConfig->getValue(self::XML_SETTING_FACEBOOK_ID);
        $config_data['google_senderid']        = $this->scopeConfig->getValue(self::XML_SETTING_GOOGLE_SENDER_ID);
        $config_data['default_store_name']     = $this->storeManager->getStore()->getCode();
        $config_data['default_store_id']       = $this->storeManager->getStore()->getId();
        $config_data['default_view_id']        = $this->storeManager->getDefaultStoreView()->getId();
        $config_data['default_store_currency'] = $this->storeManager->getStore()->getDefaultCurrencyCode();
        $config_data['default_lang']           = $this->resolver->getLocale();

        if($this->currency) {
            $alllowedCurrency = $this->storeManager->getStore()->getAllowedCurrencies();
            if (in_array($this->currency, $alllowedCurrency)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('directory_currency_rate');

                //Select Data from table
                $sql = "Select * FROM " . $tableName . " WHERE currency_from='". $config_data['default_store_currency'] ."' AND currency_to='" .$this->currency. "' LIMIT 1";
                $result = $connection->fetchAll($sql);
                if(!$result)
                {
                    $config_data['currency_error'] = true; 
                }           

            } else {
                $config_data['currency_error'] = true; 
            }
        }

        echo json_encode(array('data' => $config_data));
    }

}
