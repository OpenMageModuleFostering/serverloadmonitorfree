<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adminhtml_AitloadmonController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Sets active menu, breadcrumbs and title
     *
     * @return Aitoc_Aitloadmon_Adminhtml_AitloadmonController
     */
    protected function _initIndexAction()
    {
        if(!class_exists('Aitoc_Aitloadmon_Collect',false))
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('aitloadmon')->__('Please add the following line to your index.php file(s):<br>include_once(dirname($_SERVER[\'SCRIPT_FILENAME\']).DIRECTORY_SEPARATOR.\'lib\'.DIRECTORY_SEPARATOR.\'Aitoc\'.DIRECTORY_SEPARATOR.\'Aitloadmon\'.DIRECTORY_SEPARATOR.\'Collect.php\');new Aitoc_Aitloadmon_Collect();'));
        }    
        $this->loadLayout()
            ->_setActiveMenu('system/aitloadmon')
            ->_addBreadcrumb(Mage::helper('aitloadmon')->__('View Stats'), Mage::helper('aitloadmon')->__('View Stats'));
        $this->_title(Mage::helper('aitloadmon')->__('Server Load Monitor'))->_title(Mage::helper('aitloadmon')->__('View Stats'));
        return $this;
    }

    /**
     * Sets active menu, breadcrumbs and title
     *
     * @return Aitoc_Aitloadmon_Adminhtml_AitloadmonController
     */
    protected function _initManageAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/aitloadmon')
            ->_addBreadcrumb(Mage::helper('aitloadmon')->__('Manage Data'), Mage::helper('aitloadmon')->__('Manage Data'));
        $this->_title(Mage::helper('aitloadmon')->__('Server Load Monitor'))->_title(Mage::helper('aitloadmon')->__('Manage Data'));
        return $this;
    }

    /**
     * Processes request and sets its data to the main block
     */
    public function indexAction() {
		$this->_initIndexAction();

        if(!$startDate = $this->getRequest()->getParam('startDate'))
        {
            $startDate = date('Y-m-d H:i:s', time()-3600+1);
        }

        if(!$endDate = $this->getRequest()->getParam('endDate'))
        {
            $endDate = date('Y-m-d H:i:s');
        }

        $type = $this->getRequest()->getParam('type');
        if(!$type)
        {
            $type = 'max';
        }

        $this->getLayout()->getBlock('main_bl')
            ->setType($type)
            ->setStartDate($startDate)
            ->setEndDate($endDate);

        $compare = $this->getRequest()->getParam('compare');

        if($compare && !$startDateCompare = $this->getRequest()->getParam('startDateCompare'))
        {
            $startDateCompare = date('Y-m-d H:i:s', time()-86400);
        }

        if($compare)
        {
            $endDateCompare = date('Y-m-d H:i:s', strtotime($startDateCompare) + (strtotime($endDate) - strtotime($startDate)));
            $this->getLayout()->getBlock('main_bl')->setCompare($compare)
                                                   ->setStartDateCompare($startDateCompare)
                                                   ->setEndDateCompare($endDateCompare);
        }

        $this->renderLayout();
    }

    /**
     * This action is used to display the manage data blocks and forms
     */
    public function manageAction() {
        if(Mage::app()->getRequest()->getParam('submit_auto'))
        {
            $data = array(
                'enabled'   => Mage::app()->getRequest()->getParam('auto_enable'),
                'year'      => Mage::app()->getRequest()->getParam('auto_year'),
                'month'     => Mage::app()->getRequest()->getParam('auto_month'),
                'day'       => Mage::app()->getRequest()->getParam('auto_day'),
            );
            Mage::getModel('aitloadmon/manage')->setSettings($data);
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('aitloadmon')->__('The settings are successfully saved.'));
        }
        elseif(Mage::app()->getRequest()->getParam('submit_manual'))
        {
            Mage::getModel('aitloadmon/aitloadmon')->getResource()->manualCompress(Mage::app()->getRequest()->getParam('manual_from'), Mage::app()->getRequest()->getParam('manual_to'), Mage::app()->getRequest()->getParam('manual_compress'));
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('aitloadmon')->__('The data is successfully compressed.'));
        }
        $this->_initManageAction();
        $this->renderLayout();
    }
}