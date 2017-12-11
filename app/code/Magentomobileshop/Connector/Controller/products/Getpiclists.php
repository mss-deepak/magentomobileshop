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

class Getpiclists extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->productModel = $productModel;
        $this->imageHelper  = $imageHelper;
        $this->customHelper = $customHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $productId      = $this->getRequest()->getParam('product');
        $_images        = $this->productModel->load($productId);
        $media          = $_images->getMediaGallery();
        $images         = array();
        $result         = $this->resultJsonFactory->create();
        $images[] = array(
            'url'       => $this->imageHelper
                ->init($_images, 'product_page_image_large')
                ->setImageFile($_images['file'])
                ->resize('500', '500')
                ->getUrl(),
            'thumbnail' => $this->imageHelper
                ->init($_images, 'product_page_image_small')
                ->setImageFile($_images['file'])
                ->resize('100', '100')
                ->getUrl(),
            'position'  => $_images['position'],
        );
        $result->setData([$images]);
        return $result;
    }
}
