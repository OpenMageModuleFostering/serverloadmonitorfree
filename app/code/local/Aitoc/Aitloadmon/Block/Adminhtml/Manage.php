<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Returns the compress date types
     *
     * @return array
     */
    private function _getCompressTypes()
    {
        return Mage::getModel('aitloadmon/compressSource')->toArray();
    }

    /**
     * Preparing form for the block
     *
     * @return Mage_Adminhtml_Block_Widget_Form|void
     */
    protected function _prepareForm()
    {
        $settings = Mage::getModel('aitloadmon/manage')->getSettings();

        $form = new Varien_Data_Form(array(
                'id' => 'manageData_form',
                'action' => $this->getUrl('*/*/manage'),
                'method' => 'post',
            )
        );

        $fieldset = $form->addFieldset('manage_stat', array('legend'=>Mage::helper('aitloadmon')->__('Data stats').'<span style="font-size:10px;margin-left:20px;">('.Mage::helper('aitloadmon')->__('Server Load Monitor collects data from CMS, Checkout, Catalog, Catalogsearch and Other groups of pages every single minute by cron. It stores 60*5 values per hour, 24*60*5 per 24 hours.').')</span>'));

        $fieldset->addField('total_records', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Total records'),
            'title'     => Mage::helper('aitloadmon')->__('Total records'),
            'value'     => Mage::getModel('aitloadmon/aitloadmon')->getResource()->getRows(),
        ));

        $fieldset->addField('total_size', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Total size in bytes'),
            'title'     => Mage::helper('aitloadmon')->__('Total size in bytes'),
            'value'     => Mage::getModel('aitloadmon/aitloadmon')->getResource()->getWeight(),
        ));

        $fieldset = $form->addFieldset('manage_auto', array('legend'=>Mage::helper('aitloadmon')->__('Automatic data compression').'<span style="font-size:10px;margin-left:20px;">('.Mage::helper('aitloadmon')->__('In order not to overload database Server Load Monitor compresses data automatically.').')</span>'));

        $fieldset->addField('auto_enable','select', array(
            'label'     => Mage::helper('aitloadmon')->__('Enable automatic data compression'),
            'title'     => Mage::helper('aitloadmon')->__('Enable automatic data compression'),
            'name'      => 'auto_enable',
            'options'   => array(
                0 => Mage::helper('aitloadmon')->__('No'),
                1 => Mage::helper('aitloadmon')->__('Yes'),
            ),
            'value'     => isset($settings['enabled'])?$settings['enabled']:1,
        ));

        $fieldset->addField('auto_day','select', array(
            'label'     => Mage::helper('aitloadmon')->__('Compress data of last day to'),
            'title'     => Mage::helper('aitloadmon')->__('Compress data of last day to'),
            'name'      => 'auto_day',
            'options'   => $this->_getCompressTypes(),
            'value'     => isset($settings['day'])?$settings['day']:1,
        ));

        $fieldset->addField('auto_month','select', array(
            'label'     => Mage::helper('aitloadmon')->__('Compress data of last month to'),
            'title'     => Mage::helper('aitloadmon')->__('Compress data of last month to'),
            'name'      => 'auto_month',
            'options'   => $this->_getCompressTypes(),
            'value'     => isset($settings['month'])?$settings['month']:2,
        ));

        $fieldset->addField('auto_year','select', array(
            'label'     => Mage::helper('aitloadmon')->__('Compress data of last year to'),
            'title'     => Mage::helper('aitloadmon')->__('Compress data of last year to'),
            'name'      => 'auto_year',
            'options'   => $this->_getCompressTypes(),
            'value'     => isset($settings['year'])?$settings['year']:3,
        ));

        $fieldset->addField('submit_auto', 'submit', array(
            'name'      => 'submit_auto',
            'title'     => Mage::helper('aitloadmon')->__('Save settings'),
            'value'     => Mage::helper('aitloadmon')->__('Save settings'),
            'class'     => 'form-button',
        ));

        $fieldset = $form->addFieldset('manage_manual', array('legend'=>Mage::helper('aitloadmon')->__('Manual data compression').'<span style="font-size:10px;margin-left:20px;">('.Mage::helper('aitloadmon')->__('Default compression settings are optimized and recommended but Admin is enabled to choose preferred compression settings as well as compress the data manually.').')</span>'));

        $fieldset->addField('manual_from', 'date', array(
            'name'      => 'manual_from',
            'title'     => Mage::helper('aitloadmon')->__('From date'),
            'label'     => Mage::helper('aitloadmon')->__('From date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => 'yyyy-MM-dd HH:mm:ss',
            'value'     => date('Y-m-d H:i:s', time()-2*24*3600),
            'time'      => true,
        ));

        $fieldset->addField('manual_to', 'date', array(
            'name'      => 'manual_to',
            'title'     => Mage::helper('aitloadmon')->__('To date'),
            'label'     => Mage::helper('aitloadmon')->__('To date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => 'yyyy-MM-dd HH:mm:ss',
            'value'     => date('Y-m-d H:i:s', time()-24*3600),
            'time'      => true,
        ));

        $fieldset->addField('manual_compress','select', array(
            'label'     => Mage::helper('aitloadmon')->__('Compress data to'),
            'title'     => Mage::helper('aitloadmon')->__('Compress data to'),
            'name'      => 'manual_compress',
            'options'   => $this->_getCompressTypes(),
            'value'     => 1,
        ));

        $fieldset->addField('submit_manual', 'submit', array(
            'name'      => 'submit_manual',
            'title'     => Mage::helper('aitloadmon')->__('Compress data'),
            'value'     => Mage::helper('aitloadmon')->__('Compress data'),
            'class'     => 'form-button',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
    }

}