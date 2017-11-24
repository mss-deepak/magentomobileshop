<?php
namespace Magentomobileshop\Connector\Controller\index;

class getpiclists extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magentomobileshop\Connector\Helper\Data $customHelper
    ) {
        $this->productModel = $productModel;
        $this->imageHelper  = $imageHelper;
        $this->customHelper = $customHelper;
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
        foreach ($media as $_image) {
            $images[] = array(
                'url'       => $this->imageHelper
                    ->init($_image[1], 'product_page_image_large')
                    ->setImageFile($_image[1]['file'])
                    ->resize('500', '500')
                    ->getUrl(),
                'thumbnail' => $this->imageHelper
                    ->init($_image[1], 'product_page_image_small')
                    ->setImageFile($_image[1]['file'])
                    ->resize('100', '100')
                    ->getUrl(),
                'position'  => $_image[1]['position'],
            );
        }
        echo json_encode($images);
    }
}
