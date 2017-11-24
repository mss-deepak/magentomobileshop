<?php
namespace Magentomobileshop\Connector\Block\Adminhtml\System\Config;

    use Magento\Backend\Block\Template\Context;
    use Magento\Config\Block\System\Config\Form\Field;
    use Magento\Framework\Data\Form\Element\AbstractElement;

class cashondelivery extends Field
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return 'Payment Method is not active';
    }
}
