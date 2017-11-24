<?php
namespace Magentomobileshop\Connector\Controller\products;

class getproductdetail extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magentomobileshop\Connector\Helper\Products $productHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->customHelper  = $customHelper;
        $this->productHelper = $productHelper;
        $this->jsonHelper    = $jsonHelper;
        parent::__construct($context);
    }

    /*
     * @params productid
     * @description : get the detail of product.
     * @return Json
     */
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $product_id     = $this->getRequest()->getParam('productid');
        if (!$product_id) {
            echo json_encode(array('status' => false, 'message' => 'Product id is must'));
            exit;
        }
        $result = $this->productHelper->loadProduct($product_id);
        echo $this->jsonHelper->jsonEncode($result);
        exit();
    }
}
