<?php
namespace Magentomobileshop\Connector\Helper;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class Filters extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CollectionFactory $factory
        // \Magento\Catalog\Model\Layer $layer
        /*\Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Layer\Filter\Price $Price*/

    ) {
        $this->collectionFactory = $factory;
        $this->_logger           = $logger;
        // $this->layer            =  $layer;
        /*$this->categoryFactory  =  $categoryFactory;
        $this->Price            =  $Price;*/

        $this->_objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getFilterByCategory($categoryId)
    {  
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
                if($filter->getName() == 'Price'){
                //    foreach ($filter->getItems() as $item) {
                //         $myfilters                    = array();
                //         $myfilters['code']            = $item->getLabel();
                //         $myfilters['label']           = $item->getValue();
                //         // $resultfilters[$k]['value'][] = $this->getpricerange($category);
                //    }
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
            $json = array('status' => 'success','category' => null, 'filters' => array_values($resultfilters));
        } catch (Exception $e) {
            $json = array('status' => 'error', 'message' => $e->getMessage());
        }
        echo json_encode(array($json)); exit();
        // echo '<pre>'; print_r(array($json)); die;
    }

/*     public function getpricerange($maincategoryId) {
        $pricerange =array();   
        die('check');
        $layer = $this->layer;
        $category = $this->categoryFactory->load($maincategoryId);
        if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
        }
        $r=$this->Price->setLayer($layer);

        $range = $r->getPriceRange();
        $dbRanges = $r->getRangeItemCounts($range);
        $data = array();

        foreach ($dbRanges as $index=>$count) {
        $data[] = array(
        'label' => $this->_renderItemLabel($range, $index),
        'value' => $this->_renderItemValue($range, $index),
        'count' => $count,
        );
        }
        return $data;
    }*/

}
