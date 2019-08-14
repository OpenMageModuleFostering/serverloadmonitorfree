<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Calculator extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Preparing form for the block
     *
     * @return Mage_Adminhtml_Block_Widget_Form|void
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/calculator/submit'),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            )
        );
        $fieldset = $form->addFieldset('aitloadmon_form', array('legend'=>Mage::helper('aitloadmon')->__('Info for magento server configuration calculator')));
        /*$source = $fieldset->addField('source','select',array(
            'title'     => Mage::helper('aitloadmon')->__('Please select source for exemplary server load'),
            'label'     => Mage::helper('aitloadmon')->__('Please select source for exemplary server load'),
            'name' => 'source',
            'options' => array(
                'monitor'   => Mage::helper('aitloadmon')->__('Server Load Monitor'),
                //'log'       => Mage::helper('aitloadmon')->__('Magento Logs'),
                //'analytics' => Mage::helper('aitloadmon')->__('Google Analytics File'),
            ),
            'note' => 'note 1',
        ));
        /*$file = $fieldset->addField('csv_file', 'file', array(
            'label'     => Mage::helper('aitloadmon')->__('Google Analytics File'),
            'required'  => true,
            'name'      => 'csv_file',
        ));*/
        $fieldset->addField('logo', 'note', array(
            'text'     => '<p style="overflow:hidden;"><a href="http://www.aitoc.com/"><img src="http://www.aitoc.com/skin/frontend/default/aitocmain/images/logo_email.png" alt="AITOC Store" border="0"/></a></p>',
        ));
        $fieldset->addField('note', 'note', array(
            'text'     => Mage::helper('aitloadmon')->__('Thank you for using AITOC\'s Server Load Monitor extension. To provide yet more value we\'ve launched Magento Server Configuration Calculator service. This service allows to determine optimal server configuration for your store. Please click the "Get data" button below to use the data from Server Load Monitor.  Please also follow us on <a href="%s" target="_blank">Facebook</a> and/or on <a href="%s" target="_blank">Twitter</a> to get updates about this and other AITOC products.',
            'http://www.facebook.com/pages/AITOC/129183710447643','https://twitter.com/aitoc'),
        ));
        $fieldset->addField('submit', 'submit', array(
            'name'      => 'submit',
            'title'     => Mage::helper('aitloadmon')->__('Get Data'),
            'value'     => Mage::helper('aitloadmon')->__('Get Data'),
            'class'     => 'form-button',
            'onclick'   => 'var validator  = new Validation(\'edit_form\');if (!validator.validate()) {return false;}',            
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        /*$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($source->getHtmlId(), $source->getName())
                ->addFieldMap($file->getHtmlId(), $file->getName())
                ->addFieldDependence(
                $file->getName(),
                $source->getName(),
                'analytics'
            )
        );*/
    }
}