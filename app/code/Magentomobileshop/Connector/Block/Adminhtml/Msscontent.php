<?php
namespace Magentomobileshop\Connector\Block\Adminhtml;

/**
 * Msscontent block
 */
class Msscontent extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function setRegister()
    {
        return $this->coreRegistry->registry('secure_key');
    }

    public function unsetRegister()
    {
        return $this->coreRegistry->unregister('secure_key');
    }
}
