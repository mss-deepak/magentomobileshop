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

class GetFilters extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magentomobileshop\Connector\Helper\Filters $filters,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->customHelper      = $customHelper;
        $this->filters           = $filters;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request           = $requestInterface;
        parent::__construct($context);
    }

    /**
     * @param categoryid
     * @description : get Category Navigation Filters
     * @return Json
     */
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $categoryId     = $this->request->getParam('categoryid');
        $result         = $this->resultJsonFactory->create();
        if (empty($categoryId)) {
            $result->setData(['status' => 'false', 'message' => __('Category id field is not empty')]);
            return $result;
        } else {
            $resultData = $this->filters->getFilterByCategory($categoryId);
        }
        $result->setData([$resultData]);
        return $result;
    }
}
