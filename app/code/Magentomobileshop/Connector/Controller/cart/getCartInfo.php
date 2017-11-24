<?php
namespace Magentomobileshop\Connector\Controller\Cart;

class getCartInfo extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magentomobileshop\Connector\Helper\Data $customHelper
    ) {
        $this->checkoutCart   = $checkoutCart;
        $this->messageManager = $messageManager;
        $this->customHelper   = $customHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $addressId = $this->getRequest()->getParam('address_id');
        $countryId = $this->getRequest()->getParam('country_id');
        $setRegionId = $this->getRequest()->getParam('region_id');
        $shipping_method = $this->getRequest()->getParam('shippingmethod');
        $zipcode = $this->getRequest()->getParam('zipcode');
        echo json_encode($this->customHelper->_getCartInformation($addressId ,$countryId ,$setRegionId, $shipping_method, $zipcode));
        exit();
    }
}
