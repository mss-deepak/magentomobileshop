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

class Search extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Search\Model\Autocomplete\ItemFactory $itemFactory,
        \Magento\Framework\Api\Search\SearchInterface $search,
        \Magento\Framework\Api\Search\SearchCriteriaFactory $searchCriteriaFactory,
        \Magento\Framework\Api\Search\FilterGroupBuilder $searchFilterGroupBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->queryFactory             = $queryFactory;
        $this->itemFactory              = $itemFactory;
        $this->search                   = $search;
        $this->SearchCriteriaFactory    = $searchCriteriaFactory;
        $this->filterBuilder            = $filterBuilder;
        $this->searchFilterGroupBuilder = $searchFilterGroupBuilder;
        $this->productRepository        = $productRepository;
        $this->SearchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->productModel             = $productModel;
        $this->imageHelper              = $imageHelper;
        $this->stockStateInterface      = $stockStateInterface;
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
        $productlist    = array();
        $searchstring   = $this->request->getParam('search');
        $page           = ($this->request->getParam('page')) ? ($this->request->getParam('page')) : 1;
        $limit          = ($this->request->getParam('limit')) ? ($this->request->getParam('limit')) : 10;
        $order          = ($this->request->getParam('order')) ? ($this->request->getParam('order')) : 'entity_id';
        $result         = $this->resultJsonFactory->create();

        if ($searchstring) {
            $searchCriteria = $this->SearchCriteriaFactory->create();
            $searchCriteria->setRequestName('quick_search_container');
            $filter      = $this->filterBuilder->setField('search_term')->setValue($searchstring)->setConditionType('like')->create();
            $filterGroup = $this->searchFilterGroupBuilder->addFilter($filter)->create();
            $currentPage = 1;
            $searchCriteria->setFilterGroups([$filterGroup])
                ->setPageSize($limit, $page);
            $searchResults = $this->search->search($searchCriteria);
            foreach ($searchResults->getItems() as $searchDocument) {
                $productIds = $searchDocument->getId();
                $product    = $this->productModel->load(41);
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

                    'final_price_with_tax'   => number_format($product->getFinalPrice(), 2, '.', ''),
                    'regular_price_with_tax' => number_format($product->getPrice(), 2, '.', ''),
                    'specialprice'           => number_format(
                        $this->customHelper->getSpecialPriceProduct(
                            $product->getId()
                        ),
                        2,
                        '.',
                        ''
                    ),

                    'weight'                 => number_format($product->getWeight(), 2, '.', ''),
                    'qty'                    => $this->stockStateInterface->getStockQty($product->getId(), $product->getStore()->getWebsiteId()),
                    'url_key'                => $product->getProductUrl() . '?shareid=' . $product->getId(),
                    'image_url'              => $this->imageHelper
                        ->init($product, 'product_page_image_large')
                        ->setImageFile($product->getFile())
                        ->resize('500', '500')
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
