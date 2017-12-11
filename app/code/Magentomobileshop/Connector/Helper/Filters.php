<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Helper;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class Filters extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CollectionFactory $factory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->collectionFactory = $factory;
        $this->_logger           = $logger;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resultJsonFactory  = $resultJsonFactory;
    }

    public function getFilterByCategory($categoryId)
    {
        $result         = $this->resultJsonFactory->create();
        try {
            $filterableAttributes = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);
            $layerResolver        = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Model\Layer\Resolver::class);

            $filterList = \Magento\Framework\App\ObjectManager::getInstance()->create(
                \Magento\Catalog\Model\Layer\FilterList::class,
                [
                    'filterableAttributes' => $filterableAttributes,
                ]
            );
            $category = $categoryId;
            $layer    = $layerResolver->get();
            $layer->setCurrentCategory($category);
            $filters = $filterList->getFilters($layer);

            $resultfilters = array();
            $k             = 0;
            foreach ($filters as $filter) {
                if ($filter->getName() == 'Price') {
                    continue;
                }
                if ($filter->getItems()) {
                    $resultfilters[$k]['label'] = $filter->getName();
                    $resultfilters[$k]['code']  = $filter->getRequestVar();
                }
                foreach ($filter->getItems() as $item) {
                    $myfilters                    = array();
                    $myfilters['code']            = $item->getLabel();
                    $myfilters['label']           = $item->getValue();
                    $resultfilters[$k]['value'][] = $myfilters;
                }
                $k++;
            }
            $json = array('status' => 'success', 'category' => null, 'filters' => array_values($resultfilters));
        } catch (Exception $e) {
            $json = array('status' => 'error', 'message' => $e->getMessage());
        }
        $result->setData([$json]);
        return $result;
    }
}
