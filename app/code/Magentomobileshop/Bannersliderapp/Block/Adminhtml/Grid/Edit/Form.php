<?php
namespace Magentomobileshop\Bannersliderapp\Block\Adminhtml\Grid\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_systemStore;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magentomobileshop\Bannersliderapp\Model\Status $options,
        array $data = []
    ) {
        $this->_options       = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model      = $this->_coreRegistry->registry('row_data');
        $form       = $this->_formFactory->create(
            ['data' => [
                'id'      => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action'  => $this->getData('action'),
                'method'  => 'post',
            ],
            ]
        );
        $form->setHtmlIdPrefix('mssslider_');
        if ($model->getBannerId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Banner'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('banner_id', 'hidden', ['name' => 'banner_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Banner'), 'class' => 'fieldset-wide']
            );
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name'     => 'name',
                'label'    => __('Title'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'thumbnail',
            'image',
            [
                'name' => 'thumbnail',
                'title' => __('Image'),
                'label' => __('Image'),
                'required' => true,
                'class' => 'thumbnail',
                'index' => 'image',
                'renderer'  => '\Magentomobileshop\Bannersliderapp\Block\Adminhtml\Grid\Renderer\LogoImage',

            ]
        );
        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);
        $fieldset->addField(
            'image_alt',
            'editor',
            [
                'name'   => 'image_alt',
                'label'  => __('Description'),
                'class'  => 'required-entry',
                'style'  => 'height:10em;',
                'config' => $wysiwygConfig,
            ]
        );
        $fieldset->addField(
            'order_banner',
            'text',
            [
                'name'  => 'order_banner',
                'label' => __('Order'),
            ]
        );
        $fieldset->addField(
            'url_type',
            'select',
            [
                'name'               => 'url_type',
                'label'              => __('Link To'),
                'values'             => [
                                            'Category' => 'Category',
                                            'Product' => 'Product'
                                        ],
                'after_element_html' => '<small>Add Catagory link section</small>',
            ]
        );
        $fieldset->addField(
            'check_type',
            'select',
            [
                'name'   => 'check_type',
                'label'  => __('Display on page'),
                'values' => [
                                'home_view' => 'Home view',
                                'category_view' => 'Category view'
                            ],
            ]
        );
        $fieldset->addField(
            'product_id',
            'text',
            [
                'name'   => 'product_id',
                'label'  => __('Product id to display'),
            ]
        );
        $fieldset->addField(
            'category_id',
            'text',
            [
                'name'   => 'category_id',
                'label'  => __('Category id to display'),
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name'   => 'status',
                'label'  => __('Banner Status'),
                'values' => [
                                '-1' => 'Please Select..',
                                'enable' => 'Enable',
                                'disable' => 'Disable'],
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
