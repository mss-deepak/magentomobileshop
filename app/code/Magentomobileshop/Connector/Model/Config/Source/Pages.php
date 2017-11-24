<?php

namespace Magentomobileshop\Connector\Model\Config\Source;

class Pages implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Staging')],
            ['value' => 1, 'label' => __('Production')],
        ];
    }
}