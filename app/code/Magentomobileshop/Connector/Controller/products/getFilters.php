<?php
namespace Magentomobileshop\Connector\Controller\products;

class getFilters extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magentomobileshop\Connector\Helper\Filters $filters,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->customHelper = $customHelper;
        $this->filters      = $filters;
        $this->jsonHelper   = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @param categoryid
     * @description : get Category Navigation Filters
     * @return Json
     */
    public function execute()
    {
       //$this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $categoryId     = $this->getRequest()->getParam('categoryid');
        if (empty($categoryId)) {
            echo json_encode(array('status' => false, 'message' => 'Category id field is not empty'));
            exit();
        } else {
            $resultData = $this->filters->getFilterByCategory($categoryId);
        }

        echo $this->jsonHelper->jsonEncode($resultData);
    }
}
