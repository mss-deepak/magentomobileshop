<?php
namespace Magentomobileshop\Connector\Controller\storeinfo;

class Getstoredata extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Store\Model\Website $website,
        \Magento\Store\Model\Store $storeModel
    ) {
        $this->customerSession   = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory   = $customerFactory;
        $this->_storeManager     = $storeManager;
        $this->customHelper      = $customHelper;
        $this->website           = $website;
        $this->storeModel        = $storeModel;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->getuserinfoApi();
        echo json_encode($this->getuserinfoApi());
    }
    protected function getuserinfoApi()
    {
        $array                     = array();
        $website_id                = $this->_storeManager->getStore()->getWebsiteId();
        $website                   = $this->_storeManager->getWebsite($this->website);
        $storeId                   = $this->_storeManager->getStore()->getId();
        $store                     = $this->_storeManager->getStore($storeId);
        $array['store_name']       = $this->getStoreName();
        $array['view_id']          = $this->getStoreId();
        $array['store_id']         = $this->storeModel->getGroupId();
        $array['website_id']       = $this->getWebsiteId();
        $array['store_code']       = $this->getStoreCode();
        $array['store_url']        = $this->getStoreUrl();
        $array['is_active']        = $this->isStoreActive();
        $array['root_category_id'] = $store->getRootCategoryId();
        return $array;
    }
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }
    public function getStoreUrl($fromStore = true)
    {
        return $this->_storeManager->getStore()->getCurrentUrl($fromStore);
    }
    public function isStoreActive()
    {
        return $this->_storeManager->getStore()->isActive();
    }
}
