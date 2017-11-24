<?php
namespace Magentomobileshop\Connector\Controller\products;

class getshortbyListing extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->customHelper = $customHelper;
        $this->jsonHelper   = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @param productid
     * @description : get sort of products.
     * @return Json
     */
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $category_id    = $this->getRequest()->getParam('category_id');
        if (!$category_id) {
            echo json_encode(array('status' => false, 'message' => 'Category id is must'));
            exit;
        }
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($category_id);
        $result         = array();
        foreach ($object_manager->getAvailableSortByOptions() as $key => $value) {
            if ($key == 'position') {
                $result[][$key] = $value->getText();
            } else {
                $result[][$key] = $value;
            }

        }
        echo $this->jsonHelper->jsonEncode($result);
    }
}
