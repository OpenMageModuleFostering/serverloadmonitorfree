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
        $fieldset = $form->addFieldset('aitloadmon_form', array('legend'=>Mage::helper('aitloadmon')->__('Data to enter in server calculator')));

        $fieldset->addField('products', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Product number'),
            'title'     => Mage::helper('aitloadmon')->__('Product number'),
            'value'     => $this->getProducts(),
        ));

        $fieldset->addField('max_load_time', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Exemplary server load'),
            'title'     => Mage::helper('aitloadmon')->__('Exemplary server load'),
            'value'     => $this->getLoad(),
        ));
        
        $fieldset->addField('back', 'button', array(
            'value'     => Mage::helper('aitloadmon')->__('Go Back'),
            'onclick'   => 'document.location.href=\''.$this->getUrl('*/calculator/index').'\'',
            'class'     => 'form-button submit',
        ));        
        
        $this->setForm($form);
    }
}