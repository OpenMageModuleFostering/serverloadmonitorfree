<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_CalculatorResult extends Mage_Adminhtml_Block_Widget_Form
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
        $form->setUseContainer(false);
        $fieldset = $form->addFieldset('aitloadmon_form', array('legend'=>Mage::helper('aitloadmon')->__('Data to enter in server calculator')));

        $fieldset->addField('products_label', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Number of products on the Magento '),
            'title'     => Mage::helper('aitloadmon')->__('Number of products on the Magento '),
            'value'     => $this->getProducts(),
        ));

        $fieldset->addField('products', 'hidden', array(
            'value'     => $this->getProducts(),
            'name'      => 'products',
        ));

        $fieldset->addField('orders_label', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Number of orders'),
            'title'     => Mage::helper('aitloadmon')->__('Number of orders'),
            'value'     => $this->getOrders(),
        ));

        $fieldset->addField('orders', 'hidden', array(
            'value'     => $this->getOrders(),
            'name'      => 'orders',
        ));

        if($this->getLoad() == 0) {
            $note = Mage::helper('aitloadmon')->__('There is no enough data collected by Server Load Monitor yet and the default values will be used for now. FYI Server Load Monitor needs some time (min 7 days) to collect the data to use it for the AITOC Server configuration calculator service.');
            $value = 10;//default
        } else {
            $note = null;
            $value = $this->getLoad();
        }
        $fieldset->addField('visitors_label', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Concurrency'),
            'title'     => Mage::helper('aitloadmon')->__('Concurrency'),
            'value'     => $value,
            'note'      => $note,
        ));
            
        $fieldset->addField('max_load_time', 'hidden', array(
            'value'     => $value,
            'name'      => 'max_load_time',                
        ));

        /*$fieldset->addField('response_time', 'text', array(
            'label'     => Mage::helper('aitloadmon')->__('Response Time'),
            'title'     => Mage::helper('aitloadmon')->__('Response Time'),
            'class'     => 'validate-zero-or-greater',
            'required'  => true,
            'name'      => 'response_time',
        ));         */
        
        $fieldset->addField('send', 'submit', array(
            'value'     => Mage::helper('aitloadmon')->__('Send Data'),
            'class'     => 'form-button submit',
        ));
        
        $fieldset->addField('back', 'button', array(
            'value'     => Mage::helper('aitloadmon')->__('Go Back'),
            'onclick'   => 'document.location.href=\''.$this->getUrl('*/calculator/index').'\'',
            'class'     => 'form-button cancel',
        ));
        
        $this->setForm($form);
    }
    
    public function getCalculatorUrl()
    {
        return Mage::getModel('aitloadmon/sysInfo')->getSystemUrl();
    }
    
}