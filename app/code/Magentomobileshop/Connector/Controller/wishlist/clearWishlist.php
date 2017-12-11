<?php
namespace Magentomobileshop\Connector\Controller\wishlist;

class ClearWishlist extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository
    ) {
        $this->customerSession    = $customerSession;
        $this->wishlistRepository = $wishlistRepository;
        $this->customHelper       = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        if ($this->customerSession->isLoggedIn()) {
            $customer   = $this->customerSession->getCustomer();
            $customerId = $customer->getId();
            $wishlist   = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);
            if (!$wishlist->getId()) {
                echo json_encode(array('status' => 'success', 'message' => __('Item not found')));
                exit;
            }
            try {
                $wishlist->delete();
                $wishlist->save();
            } catch (\Exception $e) {
            }
            echo json_encode(array('status' => 'success', 'message' => __('All wishlist items removed.')));
            exit;
        } else {
            echo json_encode(array('status' => 'error', 'message' => __('Kindly Signin first.')));
            exit;
        }
    }
}
