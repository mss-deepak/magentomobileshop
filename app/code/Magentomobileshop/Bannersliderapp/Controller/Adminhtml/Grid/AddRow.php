<?php
namespace Magentomobileshop\Bannersliderapp\Controller\Adminhtml\Grid;

use Magento\Framework\Controller\ResultFactory;

class AddRow extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
    }
    public function execute()
    {
        $rowId   = (int) $this->getRequest()->getParam('id');
        $rowData = $this->_objectManager->create('Magentomobileshop\Bannersliderapp\Model\Grid');
        if ($rowId) {
            $rowData  = $rowData->load($rowId);
            $rowName = $rowData->getName();
            if (!$rowData->getBannerId()) {
                $this->messageManager->addError(__('row data no longer exist.'));
                $this->_redirect('grid/grid/rowdata');
                return;
            }
        }
        $this->_coreRegistry->register('row_data', $rowData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title      = $rowId ? __('Edit Banner ') . $rowName : __('Add Banner');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magentomobileshop_Bannersliderapp::add_row');
    }
}
