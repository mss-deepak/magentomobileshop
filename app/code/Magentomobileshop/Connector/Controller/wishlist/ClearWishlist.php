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

class ClearWishlist extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->customerSession    = $customerSession;
        $this->wishlistRepository = $wishlistRepository;
        $this->customHelper       = $customHelper;
        $this->resultJsonFactory  = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $result         = $this->resultJsonFactory->create();
        if ($this->customerSession->isLoggedIn()) {
            $customer   = $this->customerSession->getCustomer();
            $customerId = $customer->getId();
            $wishlist   = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);
            if (!$wishlist->getId()) {
                $result->setData(['status' => 'success', 'message' => __('Item not found')]);
                return $result;
            }
            try {
                $wishlist->delete();
                $wishlist->save();
            } catch (\Exception $e) {
                $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                return $result;
            }
            $result->setData(['status' => 'success', 'message' => __('All wishlist items removed.')]);
            return $result;
        } else {
            $result->setData(['status' => 'error', 'message' => __('Kindly Signin first.')]);
            return $result;
        }
    }
}
