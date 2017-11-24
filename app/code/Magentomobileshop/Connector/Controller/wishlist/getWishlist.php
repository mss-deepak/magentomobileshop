<?php
namespace Magentomobileshop\Connector\Controller\wishlist;

class GetWishlist extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Directory\Model\Currency $currentCurrency,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->customerSession    = $customerSession;
        $this->wishlistRepository = $wishlistRepository;
        $this->currentCurrency    = $currentCurrency;
        $this->productModel       = $productModel;
        $this->coreRegistry       = $coreRegistry;
        $this->customHelper       = $customHelper;
        $this->imageHelper        = $imageHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $wishlist       = $this->coreRegistry->registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }
        $customer   = $this->customerSession->getCustomer();
        $customerId = $customer->getId();
        $wishlist   = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);
        $this->coreRegistry->registry('wishlist', $wishlist);
        $items = array();
        foreach ($wishlist->getItemCollection() as $item) {
            $item = $this->productModel->setStoreId($item->getStoreId())->load($item->getProductId());
            if ($item->getId()) {
                $items[] = array(
                    'name'                   => $item->getName(),
                    'entity_id'              => $item->getId(),
                    'regular_price_with_tax' => number_format($item->getPrice(), 2, '.', ''),
                    'final_price_with_tax'   => number_format($item->getFinalPrice(), 2, '.', ''),
                    'sku'                    => $item->getSku(),
                    'symbol'                 => $this->currentCurrency->getCurrencySymbol(),
                    'image_url'              => $this->imageHelper
                        ->init($item, 'product_page_image_large')
                        ->setImageFile($item->getFile())
                        ->resize('100', '100')
                        ->getUrl(),
                );
            }
        }
        echo json_encode(array('wishlist' => $wishlist->getData(), 'items' => $items));
    }
}
