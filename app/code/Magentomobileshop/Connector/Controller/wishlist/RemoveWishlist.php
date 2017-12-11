<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\wishlist;

class RemoveWishlist extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->customerSession    = $customerSession;
        $this->wishlistRepository = $wishlistRepository;
        $this->customHelper       = $customHelper;
        $this->wishlist           = $wishlist;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->request            = $requestInterface;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $params         = $this->request->getParam('product_id');
        $result         = $this->resultJsonFactory->create();
        if (!$params) {
            $result->setData(['status' => 'error', 'message' => __('Product Id is missing.')]);
            return $result;
        }
        if ($this->customerSession->isLoggedIn()) {
            $customer   = $this->customerSession->getCustomer();
            $customerId = $customer->getId();
            $wishlists  = $this->wishlist->loadByCustomerId($customerId);
            try {
                foreach ($wishlists->getItemCollection() as $item) {
                    if ($item->getProduct()->getId() == $params) {
                        try {
                            $item->delete();
                            $wishlists->save();
                            $result->setData(['status' => 'success', 'message' => __('Item removed from wishlist.')]);
                            return $result;
                        } catch (\Eexception $e) {
                            $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                            return $result;
                        }
                    }
                }
            } catch (\Exception $e) {
                $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                return $result;
            }
        } else {
            $result->setData(['status' => 'error', 'message' => __('Login first')]);
            return $result;
        }
    }
}
