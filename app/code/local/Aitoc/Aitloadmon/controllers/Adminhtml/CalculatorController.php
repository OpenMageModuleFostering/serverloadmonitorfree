<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adminhtml_CalculatorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Opens the form for data source selection for Server Calculator
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/aitloadmon')
            ->_addBreadcrumb(Mage::helper('aitloadmon')->__('Info for Magento Server Configuration Calculator'), Mage::helper('aitloadmon')->__('Info for Magento Server Configuration Calculator'));
        $this->_title(Mage::helper('aitloadmon')->__('Server Load Monitor'))->_title(Mage::helper('aitloadmon')->__('Info for Magento Server Configuration Calculator'));
        $this->renderLayout();
    }

    /**
     * Displays the result to enter to Server Calculator
     */
    public function submitAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/aitloadmon')
            ->_addBreadcrumb(Mage::helper('aitloadmon')->__('Info for Magento Server Configuration Calculator'), Mage::helper('aitloadmon')->__('Info for Server Calculator'));
        $this->_title(Mage::helper('aitloadmon')->__('Server Load Monitor'))->_title(Mage::helper('aitloadmon')->__('Info for Magento Server Configuration Calculator'));

        $model = Mage::getModel('aitloadmon/calculator');
        if(isset($_FILES['csv_file']['name']))
        {
            $model->setCsvFile($_FILES['csv_file']['tmp_name']);
        }
        $method = 'get'.ucfirst($this->getRequest()->getParam('source')).'Visitors';
        $this->getLayout()->getBlock('aitload_calculator_result')
            ->setProducts($model->getProductInfo())
            ->setLoad($model->$method());
        $this->renderLayout();
    }

}