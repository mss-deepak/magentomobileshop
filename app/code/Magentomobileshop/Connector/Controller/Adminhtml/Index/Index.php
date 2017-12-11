<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context,
        \Magento\Backend\Model\Session $coreSession,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $repository,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->coreSession         = $coreSession;
        $this->_moduleList         = $moduleList;
        $this->storeManager        = $storeManager;
        $this->repository          = $repository;
        $this->requestInterface    = $requestInterface;
        $this->request             = $request;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $moduleCode = 'Magentomobileshop_Connector'; #Edit here with your Namespace_Module
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        $version    = $moduleInfo['setup_version'];
        $session    = $this->coreSession->getAppDatas();

        $array            = array();
        $array['version'] = $version;
        $push             = array_merge($session, $array);
        $data             = json_encode($push);
        $final_data       = base64_encode($data);
        $store  = $this->storeManager->getStore();
        $resultLayout = $this->resultLayoutFactory->create();
        $final_data = "";
        echo $resultLayout->getLayout()
          ->createBlock('Magentomobileshop\Connector\Block\Adminhtml\Msscontent')
          ->setTemplate('Magentomobileshop_Connector::default/Msscontent.phtml')
          ->toHtml();
        ?>


<?php
        echo "<script src='//code.jquery.com/jquery-latest.js'></script>
      <script type='text/javascript'>
        jQuery(document).ready(function() {
          window.location.href = 'https://www.magentomobileshop.com/user/activating-app?app=" . $final_data . "' ;

        });
      </script>";
    }
}
