<?php
namespace Magentomobileshop\Connector\Controller\wishlist;

class RemoveWishlist extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Wishlist\Model\Wishlist $wishlist
    ) {
        $this->customerSession    = $customerSession;
        $this->wishlistRepository = $wishlistRepository;
        $this->customHelper       = $customHelper;
        $this->wishlist           = $wishlist;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $params         = $this->getRequest()->getParam('product_id');
        if (!$params) {
            echo json_encode(array('status' => 'error', 'message' => __('Product Id is missing.')));
            exit;
        }   
        if ($this->customerSession->isLoggedIn()) {
            $customer   = $this->customerSession->getCustomer();
            $customerId = $customer->getId();
            $wishlists   = $this->wishlist->loadByCustomerId($customerId);
            try {
                foreach($wishlists->getItemCollection() as $item):
                    if($item->getProduct()->getId() == $params):
                        try{
                            $item->delete();
                            $wishlists->save();
                             echo json_encode(array('status' => 'success', 'message' => __('item removed from wishlist.')));
                            exit;
                        }
                        catch(exception $e){
                           echo json_encode(array('status' => 'error', 'message' => __($e->getMessage())));
                            exit;
                        }
                    endif;
            endforeach;
            } catch (exception $e) {
                echo json_encode(array('status' => 'error', 'message' => __($e->getMessage())));
                exit;
            }
        } else {
            echo json_encode(array('status' => 'error', 'message' => __('Login first')));
            exit;
        }
    }
}
