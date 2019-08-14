<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
if(!Mage::registry('aitloadmon_forced_disabled'))
{
    $moduleKey     = 'Aitoc_Aitloadmon';
    $moduleKeyPaid = 'Aitoc_Aitloadmonpaid';
    $filename      = Mage::getBaseDir().DS.'app'.DS.'etc'.DS.'modules'.DS.$moduleKey.'.xml';
    $filenamePaid  = Mage::getBaseDir().DS.'app'.DS.'etc'.DS.'modules'.DS.$moduleKeyPaid.'.xml';
    $enable = Mage::app()->getRequest()->getParam('enable');
    if(file_exists($filenamePaid) && simplexml_load_file($filenamePaid)->modules->$moduleKeyPaid->active == 'true' && isset($enable[$moduleKeyPaid]) && $enable[$moduleKeyPaid])
    {
        Mage::register('aitloadmon_forced_disabled',1);
        Mage::getModel('aitsys/module')->loadByModuleFile($filenamePaid)->getInstall()->uninstall();
        Mage::getModel('aitsys/module')->loadByModuleFile($filename)->getInstall()->uninstall();
        header('Location: '.Mage::helper('adminhtml')->getUrl('aitsys/index/index'));
        die();
    }
}