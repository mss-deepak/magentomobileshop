<?php
namespace Magentomobileshop\Connector\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Products extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Product Id
     *
     * @var int
     */
    protected $productId;

    /**
     * Product data
     *
     * @var array
     */
    protected $getProduct;

    public function __construct(
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\Review\Model\Review $review,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewFactory,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Downloadable\Model\Link $link,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlbuilder,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType
    ) {
        $this->productModel        = $productModel;
        $this->imageHelper         = $imageHelper;
        $this->priceCurrency       = $priceCurrency;
        $this->wishlistHelper      = $wishlistHelper;
        $this->stockStateInterface = $stockStateInterface;
        $this->review              = $review;
        $this->currency            = $currency;
        $this->catalogProduct      = $catalogProduct;
        $this->wishlistProvider    = $wishlistProvider;
        $this->request             = $request;
        $this->_link               = $link;
        $this->_storeManager       = $storeManager;
        $this->_reviewFactory      = $reviewFactory;
        $this->_voteFactory        = $voteFactory;
        $this->configurableType    = $configurableType;
        $this->productRepository   = $productRepository;
        $this->_downloadableFile   = $downloadableFile;
        $this->_objectManager      = \Magento\Framework\App\ObjectManager::getInstance();
        $this->urlBuilder          = $urlbuilder;
        $this->helper              = $helper;
    }

    // Magento Mobile Shop Connector api Helper to load Product
    public function loadProduct($product_id)
    {
        $all_custom_option_array = array();
        $product                 = $this->productModel->load($product_id);
        if ($product->getId()) {
            $this->productId  = $this->request->getParam('productid');
            $this->getProduct = &$product;
            $addtionatt       = $this->getAdditionalData(); // additional information about product
            $tier_prices      = $this->_tierproductprice($product); // Magento Mobile Shop For teir Price
            $ratingCollection = $this->_ratingCollect($product); // Magento Mobile Shop Reviews for product
            // If product has custom options
            if ($product->hasOptions()) {
                $has_custom_options = true;
                $attVal             = $product->getOptions();
                $optStr             = "";
                $inc                = 0;
                $has_custom_option  = 0;

                foreach ($attVal as $optionKey => $optionVal) {
                    $has_custom_option                                          = 1;
                    $all_custom_option_array[$inc]['custom_option_name']        = $optionVal->getTitle();
                    $all_custom_option_array[$inc]['custom_option_id']          = $optionVal->getId();
                    $all_custom_option_array[$inc]['custom_option_is_required'] = $optionVal->getIsRequire();
                    $all_custom_option_array[$inc]['custom_option_type']        = $optionVal->getType();
                    $all_custom_option_array[$inc]['sort_order']                = $optionVal->getSortOrder();
                    $all_custom_option_array[$inc]['all']                       = $optionVal->getData();

                    if ($all_custom_option_array[$inc]['all']['default_price_type'] == "percent") {
                        $all_custom_option_array[$inc]['all']['price'] = number_format((($product->getFinalPrice() * round($all_custom_option_array[$inc]['all']['price'] * 10, 2) / 10) / 100), 2);
                    } else {
                        $all_custom_option_array[$inc]['all']['price'] = number_format($all_custom_option_array[$inc]['all']['price'], 2);
                    }

                    $all_custom_option_array[$inc]['all']['price'] = str_replace(",", "", $all_custom_option_array[$inc]['all']['price']);

                    // $all_custom_option_array[$inc]['all']['price'] = strval(round($this->convert_currency($all_custom_option_array[$inc]['all']['price'],$basecurrencycode,$currentcurrencycode),2));
                    $inner_inc = 0;
                    if ($optionVal->getValues()) {
                        foreach ($optionVal->getValues() as $valuesKey => $valuesVal) {

                            $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['id']    = $valuesVal->getId();
                            $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['title'] = $valuesVal->getTitle();

                            $defaultcustomprice = str_replace(",", "", ($valuesVal->getPrice()));
                            // $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = strval(round($this->convert_currency($defaultcustomprice,$basecurrencycode,$currentcurrencycode),2));
                            $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price_type'] = $valuesVal->getPriceType();
                            $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sku']        = $valuesVal->getSku();
                            $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sort_order'] = $valuesVal->getSortOrder();

                            if ($valuesVal->getPriceType() == "percent") {

                                $defaultcustomprice = str_replace(",", "", ($product->getFinalPrice()));
                                // $customproductprice = strval(round($this->convert_currency($defaultcustomprice,$basecurrencycode,$currentcurrencycode),2));
                                $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = str_replace(",", "", round((floatval($defaultcustomprice) * floatval(round($valuesVal->getPrice(), 1)) / 100), 2));
                            }

                            $inner_inc++;
                        }
                    }
                    $inc++;
                }
            } else {
                $has_custom_options = false;
            }
            $rdetails[]    = array();
            $productdetail = array(
                'entity_id'              => $this->productId,
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
                'weight'                 => $product->getWeight(),
                'qty'                    => $this->stockStateInterface->getStockQty($this->productId, $product->getStore()->getWebsiteId()),
                'minqty'                 => $product->getMinQty(),
                'minsaleqty'             => $product->getMinSaleQty(),
                'maxsaleqty'             => $product->getMaxSaleQty(),
                'tier_price'             => $tier_prices,
                'specialprice'           => number_format($product->getSpecialPrice(), 2, '.', ''),
                'review'                 => isset($ratingCollection['rdetails']) ? $ratingCollection['rdetails'] : '',
                'rating'                 => isset($ratingCollection['rating']) ? $ratingCollection['rating'] : '',
                'symbol'                 => $this->currency->getCurrencySymbol(),
                'has_custom_options'     => $has_custom_options,
                'url_key'                => $product->getProductUrl() . '?shareid=' . $this->productId,
                'additional'             => $addtionatt,
                'wishlist'               => $this->checkWishlist($this->productId),
                'image_url'              => $this->imageHelper
                    ->init($product, 'product_page_image_large')
                    ->setImageFile($product->getFile())
                    ->resize('250', '250')
                    ->getUrl(),
            );
            // If product is configurable
            if ($product->getTypeId() == 'configurable') {

                $childrenIds = array_values($this->configurableType->getChildrenIds($product->getId())[0]);
                $options     = $this->helper->getOptions($product, $this->getAllowProducts());
                $attributes  = [];
                foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
                    $attributeOptionsData = $this->getAttributeOptionsData($attribute, $options);
                    if ($attributeOptionsData) {
                        $productAttribute         = $attribute->getProductAttribute();
                        $attributeId              = $productAttribute->getId();
                        $attributes[] = [
                            'id'      => $attributeId,
                            'code'    => $productAttribute->getAttributeCode(),
                            'label'   => $productAttribute->getStoreLabel($product->getStoreId()),
                            'options' => $attributeOptionsData,
                        ];
                    }
                }
                $configurableOptionData = $attributes;

                if ($childrenIds) {
                    $k        = 0;
                    $childern = array();
                    foreach ($childrenIds as $key => $child) {
                        $load_product_data                      = $this->productModel->load($child);
                        $childern[$k]['name']                   = $load_product_data->getName();
                        $finalPrice                             = ($load_product_data->getData('final_price')) ?: $load_product_data->getData('special_price');
                        $childern[$k]['regular_price_with_tax'] = number_format($load_product_data->getPrice(), 2, '.', '');
                        $childern[$k]['final_price_with_tax']   = number_format($finalPrice, 2, '.', '');
                        $childern[$k]['id']                     = $load_product_data->getId();
                        $k++;
                    }
                }

                $productdetail['configurable']  = $configurableOptionData;
                $productdetail['childProducts'] = $childern;

            }
            //If Product is Downloadable
            if ($product->getTypeId() == 'downloadable') {
                $productdetail['download_options']          = $this->getDownloadableproduct($product);
                $productdetail['downloadable_can_purchase'] = $this->isProductLinksCanBePurchasedSeparately($product);
            }
            $productdetail["custom_option"] = $all_custom_option_array;
        } else {
            $productdetail['status']  = 'error';
            $productdetail['message'] = "We didn't find any product with this id";
        }
        return $productdetail;
    }

    // Functionality to check product is in wishlist or not
    public function checkWishlist($productId)
    {
        $customer = $this->_objectManager->get('Magento\Customer\Model\Session');
        if ($customer->isLoggedIn()) {
            $currentUserWishlist = $this->wishlistProvider->getWishlist();
            if ($currentUserWishlist) {
                $wishListItemCollection = $currentUserWishlist->getItemCollection();
            } else {
                return false;
            }
            $wishlist_product_id = array();
            foreach ($wishListItemCollection as $item) {

                $wishlist_product_id[] = $item->getProductId();
            }
            if (in_array($productId, $wishlist_product_id)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     */
    public function getAdditionalData(array $excludeAttr = [])
    {
        $data       = [];
        $product    = $this->getProduct;
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = __('N/A');
                } elseif ((string) $value == '') {
                    $value = __('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen($value)) {
                    $data[] = [
                        'label' => $attribute->getFrontendLabel(),
                        'value' => $value,
                        'code'  => $attribute->getAttributeCode(),
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * @param
     * @description : Get the product Review || rating
     * @return array | False
     */
    protected function _ratingCollect($product)
    {
        if ($this->productId) {
            $avg        = 0;
            $ratings    = array();
            $result     = array();
            $collection = $this->_reviewFactory->create()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $this->productId
            )->setDateOrder();
            if (count($collection->getdata()) > 0) {
                foreach ($collection->getItems() as $review) {

                    // Magento Mobile Shop get rating of products
                    $ratingCollection = $this->_voteFactory->create()->getResourceCollection()->setReviewFilter(
                        $review->getReviewId()
                    )->addRatingInfo(
                        $this->_storeManager->getStore()->getId()
                    )->setStoreFilter(
                        $this->_storeManager->getStore()->getId()
                    )->load();
                    $review_rating = 0;
                    $rating_method = array();
                    $l             = 0;
                    foreach ($ratingCollection as $vote) {
                        $rating_method[$l][$vote->getRatingCode()] = number_format($vote->getPercent() / 20, 1, '.', ',');
                        $review_rating                             = $vote->getPercent();
                        $ratings[]                                 = $vote->getPercent();
                    }
                    $l++;
                    if ($review_rating) {
                        $rating_by = ($review_rating / 20);
                    }

                    $result['rdetails'][] = array(
                        'title'       => $review->getTitle(),
                        'description' => $review->getDetail(),
                        'reviewby'    => $review->getNickname(),
                        'rating_by'   => $rating_method,
                        'rating_date' => date("d-m-Y", strtotime($review->getCreatedAt())),
                    );
                }
                $avg = array_sum($ratings) / count($ratings);
            }
            $result['rating'] = number_format($avg / 20, 1, '.', ',');
            return $result;
        } else {
            return false;
        }
    }

    /**
     * @param
     * @description : Get the teir Price of Product
     * @return array | null
     */
    protected function _tierproductprice($product)
    {
        $productPrice        = $product->getPrice();
        $product_tier_prices = $product->getTierPrice();
        if (count($product_tier_prices) > 0) {
            $product_tier_prices = (object) $product_tier_prices;
            $tier_prices         = '';
            $currency            = $this->_storeManager->getStore()->getCurrentCurrencyCode();
            foreach ($product_tier_prices as $_index => $_price) {
                $product_qty = $_price['price_qty'];
                $tier_price  = number_format($_price['price'], 2, '.', '');
                $discount    = ceil(100 - ((100 / $productPrice) * $_price['price']));
                $symbol      = $currency;
                $tier        = 'Buy ' . $product_qty . ' for ' . $symbol . $tier_price . ' each and save ' . $discount . '%';
                $tier_prices .= '<span>' . $tier . '</span>';
            }
        } else {
            $tier_prices = null;
        }
        return $tier_prices;
    }

    /**
     * Get Configurable Attribute Data
     *
     * @param int[] $attributeIds
     * @return array
     */
    private function getConfigurableAttributesData($attributeIds)
    {
        $configurableAttributesData = [];
        $attributes                 = $this->getAttributeFactory()->create()
            ->getCollection()
            ->addFieldToFilter('attribute_id', $attributeIds)
            ->getItems();
        foreach ($attributes as $attribute) {
            $attributeValues = array();
            foreach ($attribute->getOptions() as $option) {
                if ($option->getValue()) {
                    $attributeValues[] = [
                        'label'        => $option->getLabel(),
                        'attribute_id' => $attribute->getId(),
                        'value_index'  => $option->getValue(),
                    ];
                }
            }

            $configurableAttributesData[] =
                [
                'id'      => $attribute->getId(),
                'code'    => $attribute->getAttributeCode(),
                'label'   => $attribute->getStoreLabel(),
                'options' => $attributeValues,
            ];
        }

        return $configurableAttributesData;
    }

    /**
     * Get Attribute Factory
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     *
     * @deprecated
     */
    private function getAttributeFactory()
    {

        $this->attributeFactory = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class);

        return $this->attributeFactory;
    }

    /**
     * Get Downloadable Options
     *
     * @return array
     *
     */
    public function getDownloadableproduct($product)
    {
        $links      = $product->getTypeInstance()->getLinks($product);
        $linkArr    = array();
        $fileHelper = $this->_downloadableFile;
        foreach ($links as $item) {
            $tmpLinkItem = [
                'link_id'             => $item->getId(),
                'title'               => $item->getTitle(),
                'price'               => $item->getPrice(),
                'number_of_downloads' => $item->getNumberOfDownloads(),
                'is_shareable'        => $item->getIsShareable(),
                'link_url'            => $item->getLinkUrl(),
                'link_type'           => $item->getLinkType(),
                'sample_file'         => $item->getSampleFile(),
                'sample_url'          => $item->getSampleUrl(),
                'sample_type'         => $item->getSampleType(),
                'sort_order'          => $item->getSortOrder(),
            ];

            $sampleFile = $item->getSampleFile();
            if ($sampleFile) {
                $file = $fileHelper->getFilePath($this->_link->getBaseSamplePath(), $sampleFile);

                $fileExist = $fileHelper->ensureFileInFilesystem($file);

                if ($fileExist) {
                    $name = '<a href="' . $this->urlBuilder->getUrl(
                        'downloadable/download/linkSample',
                        ['link_id' => $item->getId(), '', '_secure' => true]
                    ) . '">' . $fileHelper->getFileFromPathFile(
                        $sampleFile
                    ) . '</a>';
                    $tmpLinkItem['sample_file_save'] = [
                        [
                            'file'   => $item->getSampleFile(),
                            'name'   => $name,
                            'size'   => $fileHelper->getFileSize($file),
                            'status' => 'old',
                        ],
                    ];
                }
            }
            $linkArr[] = $tmpLinkItem;
        }
        return $linkArr;
    }

    /**
     * Get Links can be purchased separately value for current product
     *
     * @return bool
     */
    public function isProductLinksCanBePurchasedSeparately($product)
    {
        return (bool) $product->getData('links_purchased_separately');
    }

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId               = $attributeOption['value_index'];
            $attributeOptionsData[] = [
                'id'       => $optionId,
                'label'    => $attributeOption['label'],
                'products' => isset($config[$attribute->getAttributeId()][$optionId])
                ? $config[$attribute->getAttributeId()][$optionId]
                : [],
            ];
        }
        return $attributeOptionsData;
    }

    /**
     * Get Allowed Products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProducts()
    {

        $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();

        $products = $skipSaleableCheck ?
        $this->getProduct->getTypeInstance()->getUsedProducts($this->getProduct, null) :
        $this->getProduct->getTypeInstance()->getSalableUsedProducts($this->getProduct, null);

        return $products;
    }

    public function getSpecialPriceProduct($product)
    { 
        $specialprice = $product->getPriceInfo()->getPrice('special_price')->getAmount()->getValue();
        $final_price_with_tax = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
         if($specialprice >= $final_price_with_tax){
            return $final_price_with_tax;
         } else {
            return $specialprice;
         }
    }
}
