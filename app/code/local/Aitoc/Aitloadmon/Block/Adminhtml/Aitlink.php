<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Aitlink extends Mage_Adminhtml_Block_System_Config_Form_Field    
{
    protected function _getElementHtml (Varien_Data_Form_Element_Abstract $element)
    {
        $buttonBlock = $element->getForm()->getParent()->getLayout()->createBlock('adminhtml/widget_button');

        $data = array(
            'label'     => Mage::helper('adminhtml')->__('Manage'),
            'onclick'   => 'setLocation(\''.Mage::helper('adminhtml')->getUrl("adminhtml/aitloadmon/manage").'\' )',
            'class'     => '',
        );

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }
}