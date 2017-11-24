<?php
namespace Magentomobileshop\Connector\Controller\index;

class index extends \Magento\Framework\App\Action\Action
{
    /**
     * Product Collection
     *
     * @var array
     */
    protected $_productCollectionFactory;

    /**
     * category Collection
     *
     * @var array
     */
    protected $_categoryFactory;

    /**
     * Product Id
     *
     * @var int
     */
    protected $_page;

    /**
     * order
     *
     * @var desc, asc
     */
    protected $_order;

    /**
     * Collection
     *
     * @var null
     */
    protected $_productFinalCollection;

    /**
     * limt
     *
     * @var int
     */
    protected $_limit;

    /**
     * Sort
     *
     * @var int
     */
    protected $_dir;

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Products $productHelper,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->jsonHelper                = $jsonHelper;
        $this->_stockItemRepository      = $stockItemRepository;
        $this->_categoryFactory          = $categoryFactory;
        $this->imageHelper               = $imageHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_cacheTypeList            = $cacheTypeList;
        $this->_reviewFactory            = $reviewFactory;
        $this->_storeManager             = $storeManager;
        $this->customHelper              = $customHelper;
        $this->productHelper             = $productHelper;

        parent::__construct($context);

    }

    /**
     * @param cmd,categoryid,page,order,limit,filters,dir
     * @description : get Product listing
     * @return Json
     */
    public function execute()
    {
        //$this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->currency = $this->getRequest()->getHeader('currency');
        $cmd = $this->getRequest()->getParam('cmd');
        if (!$cmd) {
            $result            = array();
            $result['status']  = 'error';
            $result['message'] = 'filter field should not be empty';
            echo $this->jsonHelper->jsonEncode($result);
            exit;
        }
        switch ($cmd) {
            case 'catalog':
                $categoryid = $this->getRequest()->getParam('categoryid');

                if (!$categoryid) {
                    $result            = array();
                    $result['status']  = 'error';
                    $result['message'] = 'category id should not be empty';
                    echo $this->jsonHelper->jsonEncode($result);
                    exit;
                }

                $this->_page  = ($this->getRequest()->getParam('page')) ? ($this->getRequest()->getParam('page')) : 1;
                $this->_limit = ($this->getRequest()->getParam('limit')) ? ($this->getRequest()->getParam('limit')) : 10;
                $this->_order = ($this->getRequest()->getParam('order')) ? ($this->getRequest()->getParam('order')) : 'entity_id';
                $this->_dir   = ($this->getRequest()->getParam('dir')) ? ($this->getRequest()->getParam('dir')) : 'desc';

                $collection = $this->getProductCollectionFromCatId($categoryid); // Magento Mobile Shop Product Collection get By cat id

                $price_filter = array();
                /*filter added*/
                if ($this->getRequest()->getParam('filter')) {
                    $filters = $this->jsonHelper->jsonDecode($this->getRequest()->getParam('filter'));
                    foreach ($filters as $key => $filter) {
                        if (sizeof($filter)) {
                            if ($key == 'price') {
                                $price        = explode(',', $filter[0]);
                                $price_filter = array('gt' => $price['0'], 'lt' => $price['1']);
                                $collection   = $collection->addAttributeToFilter('price', array('gt' => $price['0']));
                                $collection   = $collection->addAttributeToFilter('price', array('lt' => $price['1']));
                            } else {
                                  $tableAlias = $key . '_idx';
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                $connection = $resource->getConnection();

                                $attributeModel = $objectManager->get('Magento\Eav\Model\Entity\Attribute')->getCollection()->addFieldToFilter('attribute_code', $key );
                                
                                if($attributeModel)
                                {
                                    $attributeId  = $attributeModel->getFirstItem()->getId();
                                } else {
                                    continue;
                                }

                                $conditions = [
                                    "{$tableAlias}.entity_id = e.entity_id",
                                    $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attributeId),
                                    $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
                                    $connection->quoteInto("{$tableAlias}.value = ?", $filter[0]),
                                ];

                                $collection->getSelect()->join(
                                    [$tableAlias => 'catalog_product_index_eav'],
                                    implode(' AND ', $conditions),
                                    []
                                );  

                                // $collection = $collection->addFieldToFilter($key, array('in' => $filter));
                              //  print_r($collection->getData()); die('keys');
                            }
                        }
                    }
                }
                /*filter added*/

                if ($this->getRequest()->getParam('min')) {
                    $collection = $collection->addAttributeToFilter('price', array('gt' => $this->getRequest()->getParam('min')));
                }
                if ($this->getRequest()->getParam('max')) {
                    $collection = $collection->addAttributeToFilter('price', array('lt' => $this->getRequest()->getParam('max')));
                }

                $collection = $this->__applyFilters($collection);
                $pages      = $collection->setPageSize($this->_limit)->getLastPageNumber();

                if ($this->_page <= $pages) {
                    $collection->setPageSize($this->_limit)->setCurPage($this->_page);
                    $this->getProductlist($collection, 'catalog', $price_filter);
                }

                $count = $collection->getSize();

                if (!$count) {
                    $result            = array();
                    $result['status']  = 'error';
                    $result['message'] = 'No Record Found';
                    echo $this->jsonHelper->jsonEncode($result);
                    exit;
                }

                if (sizeof($this->_productFinalCollection)) {
                    echo $this->jsonHelper->jsonEncode($this->_productFinalCollection);
                } else {
                    echo "[]";
                }
                break;
        }
        exit();

    }

    /**
     * @param categoryId
     * @description : get Product Collection form cat id
     * @return array
     */
    public function getProductCollectionFromCatId($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        if ($category->getdata()) {
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoryFilter($category);
            $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            return $collection;
        } else {
            $result            = array();
            $result['status']  = 'error';
            $result['message'] = 'category id not found';
            echo $this->jsonHelper->jsonEncode($result);
            exit;
        }
    }

    protected function __applyFilters($collection)
    {
        $collection->setOrder($this->_order, $this->_dir);
        return $collection;
    }

    public function getProductlist($products, $mod = 'product', $price_filter = array())
    {

        $productlist = array();
        foreach ($products as $product) {

            if ($mod == 'catalog') {
                $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
                $rating_final = (int) $product->getRatingSummary()->getRatingSummary() / 20;
            }
            if ($product->getTypeId() == "configurable") {
                $qty = $this->_stockItemRepository->get($product->getId())->getIsInStock();
            } else {
                $qty = (int) $this->_stockItemRepository->get($product->getId())->getQty();
            }

            $productlist[] = $this->__getListProduct($product, $qty, $rating_final);
        }

        $this->_productFinalCollection = $productlist;
    }

    protected function __getListProduct($product, $qty, $rating_final)
    {
        $result = array(
            'entity_id'              => $product->getId(),
            'sku'                    => $product->getSku(),
            'name'                   => $product->getName(),
            'news_from_date'         => $product->getNewsFromDate(),
            'news_to_date'           => $product->getNewsToDate(),
            'special_from_date'      => $product->getSpecialFromDate(),
            'special_to_date'        => $product->getSpecialToDate(),
            'image_url'              => $this->imageHelper
                ->init($product, 'product_page_image_large')
                ->setImageFile($product->getFile())
                ->resize('300', '300')
                ->getUrl(),
            'url_key'                => $product->getProductUrl(),
            'regular_price_with_tax' => number_format($product->getPrice(), 2, '.', ''),
            'final_price_with_tax'   => number_format($product->getFinalPrice(), 2, '.', ''),
            'symbol'                 => $this->customHelper->getCurrencysymbolByCode($this->currency),
            'qty'                    => $qty,
            'product_type'           => $product->getTypeId(),
            'rating'                 => $rating_final,
            'wishlist'               => $this->productHelper->checkWishlist($product->getId()),
            'specialprice'           => number_format($this->customHelper->getSpecialPriceProduct(
                                                            $product->getId ()),2,'.',''),
            
        );

        return $result;
    }
}
