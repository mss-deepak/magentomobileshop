<?php
namespace Magentomobileshop\Connector\Controller\cart;

//Connector Magento Mobile Shop get Order details api.
class getOrderDetails extends \Magento\Framework\App\Action\Action
{

    protected $_order;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {

        $this->customHelper = $customHelper;
        $this->jsonHelper   = $jsonHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        echo $this->jsonHelper->jsonEncode($this->customHelper->getOrderDetails($this->getRequest()->getParam('order_id')));
    }
}
