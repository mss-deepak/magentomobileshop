<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\products;

class GetshortbyListing extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->customHelper      = $customHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request           = $requestInterface;
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
        $category_id    = $this->request->getParam('category_id');
        $result         = $this->resultJsonFactory->create();
        if (!$category_id) {
            $result->setData(['status' => false, 'message' => __('Category id is must')]);
            return $result;
        }
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($category_id);
        $results        = array();
        foreach ($object_manager->getAvailableSortByOptions() as $key => $value) {
            if ($key == 'position') {
                $results[][$key] = $value->getText();
            } else {
                $results[][$key] = $value;
            }
        }
        $result->setData([$results]);
        return $result;
    }
}
