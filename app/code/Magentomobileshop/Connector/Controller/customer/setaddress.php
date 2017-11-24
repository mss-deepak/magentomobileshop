<?php
namespace Magentomobileshop\Connector\Controller\customer;

class Setaddress extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper
    ) {
        $this->addressFactory = $addressFactory;
        $this->storeManager   = $storeManager;
        $this->customHelper   = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->setAddressApi();
    }

    public function setAddressApi()
    {
        try {
            $params              = $this->getRequest()->getParams();
            $respnse             = json_decode($params['data'], true);
            $is_default_billing  = json_decode($respnse[0], 1);
            $is_default_shipping = json_decode($respnse[1], 1);
            $customerId          = $is_default_shipping['userid'];

            if ($customerId) {
                if (!\Zend_Validate::is($is_default_shipping['firstname'], 'NotEmpty')) {
                    echo json_encode(array('status' => 'error', 'message' => __('Firstname should not be empty')));
                    exit;
                }
                if (!\Zend_Validate::is($is_default_shipping['lastname'], 'NotEmpty')) {
                    echo json_encode(array('status' => 'error', 'message' => __('Lastname should not be empty')));
                    exit;
                }
                if (!\Zend_Validate::is($is_default_shipping['street'], 'NotEmpty')) {
                    echo json_encode(array('status' => 'error', 'message' => __('Street should not be empty')));
                    exit;
                }
                if (!\Zend_Validate::is($is_default_shipping['city'], 'NotEmpty')) {
                    echo json_encode(array('status' => 'error', 'message' => __('City should not be empty')));
                    exit;
                }
                if (!\Zend_Validate::is($is_default_shipping['country_id'], 'NotEmpty') || $is_default_shipping['country_id'] == 'undefined') {
                    echo json_encode(array('status' => 'error', 'message' => __('Country_id should not be empty')));
                    exit;
                }
                if (!\Zend_Validate::is($is_default_shipping['telephone'], 'NotEmpty')) {
                    echo json_encode(array('status' => 'error', 'message' => __('Telephone should not be empty')));
                    exit;
                }

                if ($is_default_shipping['firstname'] == null) {
                    echo json_encode(array(
                        'status'  => 'error',
                        'message' => __('please enter the firstname,')));
                }
                /*  if($is_default_shipping['region'])
                $is_default_shipping['region'] = $is_default_shipping['region'];
                else
                $is_default_shipping['region_id'] = $is_default_shipping['region_id'];*/

                $addresss = $this->addressFactory;
                $address  = $addresss->create();
                $address->setCustomerId($customerId);
                $address->setFirstname($is_default_shipping['firstname']);
                $address->setLastname($is_default_shipping['lastname']);
                $address->setCountryId($is_default_shipping['country_id']);
                $address->setPostcode($is_default_shipping['postcode']);
                $address->setCity($is_default_shipping['city']);
                $address->setTelephone($is_default_shipping['telephone']);
                if (isset($is_default_shipping['region'])) {
                    $address->setRegion($is_default_shipping['region']);
                } else {
                    $address->setRegionId($is_default_shipping['region_id']);
                }
                $address->setStreet($is_default_shipping['street']);
                $address->setIsDefaultBilling($is_default_shipping['is_default_billing']);
                $address->setIsDefaultShipping($is_default_shipping['is_default_shipping']);
                $address->setSaveInAddressBook('1');

                $addressss = $this->addressFactory;
                $addresss  = $addressss->create();
                $addresss->setCustomerId($customerId);
                $addresss->setFirstname($is_default_billing['firstname']);
                $addresss->setLastname($is_default_billing['lastname']);
                $addresss->setCountryId($is_default_billing['country_id']);
                $addresss->setPostcode($is_default_billing['postcode']);
                $addresss->setCity($is_default_billing['city']);
                $addresss->setTelephone($is_default_billing['telephone']);
                if (isset($is_default_billing['region'])) {
                    $addresss->setRegion($is_default_billing['region']);
                } else {
                    $addresss->setRegionId($is_default_billing['region_id']);
                }
                $addresss->setStreet($is_default_billing['street']);
                $addresss->setIsDefaultBilling($is_default_billing['is_default_billing']);
                $addresss->setIsDefaultShipping($is_default_shipping['is_default_shipping']);
                $addresss->setSaveInAddressBook('1');
                try {
                    $address->save();
                    $addresss->save();
                    $new_array[]       = $address->getId();
                    $new_array[]       = $addresss->getId();
                    $result['id']      = $new_array;
                    $result['message'] = __('Address added successfully.');
                    $result['status']  = 'success';
                    echo json_encode($result);
                } catch (\Exception $e) {
                    echo json_encode(array(
                        'status'  => 'error',
                        'message' => __($e->getMessage()),
                    ));
                }
            } else {
                echo json_encode(array(
                    'status'  => 'error',
                    'message' => __('No matched email data.'),
                ));
            }
        } catch (\Exception $e) {
            echo json_encode(array(
                'status'  => 'error',
                'message' => __($e->getMessage()),
            ));
        }
        exit();
    }
}
