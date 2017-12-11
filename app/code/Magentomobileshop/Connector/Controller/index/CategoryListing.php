<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\index;

class CategoryListing extends \Magento\Framework\App\Action\Action
{
    protected $_withProductCount;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Catalog\Model\Category $category
    ) {
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->storeManager        = $storeManager;
        $this->categoryTreeFactory = $categoryTreeFactory;
        $this->category            = $category;
        $this->customHelper        = $customHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $result         = $this->resultJsonFactory->create();
        return $result->setData($this->getRoot());
    }

    public function getRoot($recursionLevel = 3)
    {

        $storeId = $this->storeManager->getStore()->getId();

        if ($storeId) {
            $store  = $this->storeManager->getStore();
            $parent = $store->getRootCategoryId();
        } else {
            $parent = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        }

        $tree  = $this->categoryTreeFactory->create();
        $nodes = $tree->loadNode($parent)->loadChildren($recursionLevel)->getChildren();
        $tree->addCollectionData(null, false, $parent, true, true);

        $categoryTreeData = array();
        $category_model   = $this->category;
        foreach ($nodes as $node) {
            if ($node->getIsActive() && $category_model->load($node->getId())->getIncludeInMenu()) {
                $categoryTreeData[] = $this->getNodeChildrenData($node);
            }
        }
        return $categoryTreeData;
    }

    protected function getNodeChildrenData(\Magento\Framework\Data\Tree\Node $node)
    {
        $data = array(
            'id'    => $node->getData('entity_id'),
            'title' => $node->getData('name'),
            'url'   => $node->getData('url_key'),
        );

        foreach ($node->getChildren() as $childNode) {
            if (!array_key_exists('children', $data)) {
                $data['children'] = array();
            }
            if ($childNode->getIsActive()) {
                $data['children'][] = $this->getNodeChildrenData($childNode);
            }
        }
        return $data;
    }
}
