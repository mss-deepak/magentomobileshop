<?php

namespace Magentomobileshop\Connector\Model\Config\Source;

class Dashboard implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'new_product', 'label' => __('New Product')],
            ['value' => 'sale_product', 'label' => __('Sale Product')],
            ['value' => 'top_product', 'label' => __('Top Product')],
        ];
    }
}