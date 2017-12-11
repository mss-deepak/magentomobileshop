<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\Index;

class Search extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->productModel             = $productModel;
        $this->imageHelper              = $imageHelper;
        $this->stockStateInterface      = $stockStateInterface;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customHelper             = $customHelper;
        $this->resultJsonFactory        = $resultJsonFactory;
        $this->request                  = $requestInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $searchstring   = $this->request->getParam('search');
        $page           = ($this->request->getParam('page')) ? ($this->request->getParam('page')) : 1;
        $limit          = ($this->request->getParam('limit')) ? ($this->request->getParam('limit')) : 10;
        $order          = ($this->request->getParam('order')) ? ($this->request->getParam('order')) : 'entity_id';
        $result = $this->resultJsonFactory->create();
        $productlist    = array();
        if ($searchstring) {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect(array('name', 'entity_id', 'status', 'visibility'), 'inner')
                ->setPageSize($limit)
                ->addAttributeToFilter(array(
                    array('attribute' => 'name', 'like' => '%' . $searchstring . '%'),
                ))
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility', array('neq' => 1))
                ->setPage($page, $limit);
            foreach ($products as $key => $pro) {
                $product = $this->productModel->load($pro->getData('entity_id'));

                $productlist[] = array(
                    'entity_id'              => $product->getId(),
                    'product_type'           => $product->getTypeId(),
                    'sku'                    => $product->getSku(),
                    'name'                   => $product->getName(),
                    'news_from_date'         => $product->getNewsFromDate(),
                    'news_to_date'           => $product->getNewsToDate(),
                    'special_from_date'      => $product->getSpecialFromDate(),
                    'special_to_date'        => $product->getSpecialToDate(),
                    'description'            => $product->getDescription(),
                    'short_description'      => $product->getShortDescription(),
                    'is_in_stock'            => $product->isAvailable(),
                    'regular_price_with_tax' => number_format($product->getPrice(), 2, '.', ''),
                    'final_price_with_tax'   => number_format($product->getFinalPrice(), 2, '.', ''),
                    'weight'                 => number_format($product->getWeight(), 2, '.', ''),
                    'qty'                    => $this->stockStateInterface->getStockQty($product->getId(), $product->getStore()->getWebsiteId()),
                    'specialprice'           => number_format($product->getSpecialPrice(), 2, '.', ''),
                    'url_key'                => $product->getProductUrl() . '?shareid=' . $product->getId(),
                    'image_url_large'        => $this->imageHelper
                        ->init($product, 'product_page_image_large')
                        ->setImageFile($product->getFile())
                        ->resize('500', '500')
                        ->getUrl(),
                    'image_url_small'        => $this->imageHelper
                        ->init($product, 'product_page_image_small')
                        ->setImageFile($product->getFile())
                        ->resize('250', '250')
                        ->getUrl(),
                    'image_url_medium'       => $this->imageHelper
                        ->init($product, 'product_page_image_medium')
                        ->setImageFile($product->getFile())
                        ->getUrl(),
                );
            }
            if (sizeof($productlist)) {
                $result->setData([$productlist]);
                return $result;
            } else {
                $result->setData(['status' => 'error', 'message' => __('There are no products matching the selection')]);
                return $result;
            }
        } else {
            $result->setData(['status' => 'error', 'message' => __('Search string is required')]);
            return $result;
        }
    }
}
