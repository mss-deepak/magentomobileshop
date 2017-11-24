<?php

namespace Magentomobileshop\Connector\Model\Config\Source;

class Banktransfer implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Disable')],
            ['value' => 1, 'label' => __('Enable')],
        ];
    }
}