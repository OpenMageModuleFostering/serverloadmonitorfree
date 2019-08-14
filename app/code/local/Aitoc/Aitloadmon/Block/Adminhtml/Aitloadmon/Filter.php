<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Aitloadmon_Filter extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     *  Preparing needed data for form
     */
    private function _prepareData()
    {
        $collection = $this->getParentBlock()->getCollection();

        $dateModel = Mage::getModel('core/date');
        $totalPageViews = 0;
        $maxLoad        = 0;
        $maxLoadTime    = 0;

        foreach($collection as $item)
        {
            $totalPageViews +=$item->getPageViews();
            if($item->getLoadTimeMax()>$maxLoad)
            {
                $maxLoad = $item->getLoadTimeMax();
                $maxLoadTime = $item->getMeasureTime();
            }
        }

        $this->setTotalPageViews($totalPageViews);
        $this->setMaxLoad($maxLoad);
        $this->setMaxLoadTime($dateModel->date('Y-m-d H:i:s',$maxLoadTime));
    }

    /**
     * Preparing form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $this->_prepareData();

        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/index'),
                'method' => 'post',
            )
        );
        $fieldset = $form->addFieldset('aitloadmon_form', array('legend'=>Mage::helper('aitloadmon')->__('Custom Filter Options')));

        $fieldset->addField('starts', 'date', array(
            'name'      => 'startDate',
            'title'     => Mage::helper('aitloadmon')->__('Start Date'),
            'label'     => Mage::helper('aitloadmon')->__('Start Date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd HH:mm:ss',
            'value' =>  $this->getParentBlock()->getStartDate(),
            'time'      => true,
            'required'  => true,
        ));

        $fieldset->addField('ends', 'date', array(
             'name'      => 'endDate',
             'title'     => Mage::helper('aitloadmon')->__('End Date'),
             'label'     => Mage::helper('aitloadmon')->__('End Date'),
             'image'     => $this->getSkinUrl('images/grid-cal.gif'),
             'format' => 'yyyy-MM-dd HH:mm:ss',
             'value' =>  $this->getParentBlock()->getEndDate(),
             'time'      => true,
             'required'  => true,
        ));


        $fieldset->addField('type','select',array(
            'title'     => Mage::helper('aitloadmon')->__('Graph displays:'),
            'label'     => Mage::helper('aitloadmon')->__('Graph displays:'),
            'name' => 'type',
            'options' => array(
                'max' => Mage::helper('aitloadmon')->__('Maximum load'),
                'avg' => Mage::helper('aitloadmon')->__('Average load'),
            ),
            'value' =>  $this->getParentBlock()->getType(),
        ));


        $compare = $fieldset->addField('compare','select',array(
            'title'     => Mage::helper('aitloadmon')->__('Compare with the same period'),
            'label'     => Mage::helper('aitloadmon')->__('Compare with the same period'),
            'name' => 'compare',
            'options' => array(
                0 => Mage::helper('aitloadmon')->__('No'),
                1 => Mage::helper('aitloadmon')->__('Yes'),
            ),
            'value' =>  $this->getParentBlock()->getCompare(),
        ));

        $compareDate = $fieldset->addField('starts_compare', 'date', array(
            'name'      => 'startDateCompare',
            'title'     => Mage::helper('aitloadmon')->__('From Date'),
            'label'     => Mage::helper('aitloadmon')->__('From Date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd HH:mm:ss',
            'value' =>  $this->getParentBlock()->getStartDateCompare(),
            'time'      => true,
        ));


        $fieldset->addField('submit1', 'submit', array(
            'name'      => 'submit1',
            'title'     => Mage::helper('aitloadmon')->__('Filter'),
            'value'     => Mage::helper('aitloadmon')->__('Filter'),
            'class'     => 'form-button',
        ));

        $fieldset = $form->addFieldset('aitloadmon_stat', array('legend'=>Mage::helper('aitloadmon')->__('Global stats for the selected period')));


        $fieldset->addField('page_views', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Total page views'),
            'title'     => Mage::helper('aitloadmon')->__('Total page views'),
            'value'     => $this->getTotalPageViews(),
        ));


        $fieldset->addField('max_load', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Max load'),
            'title'     => Mage::helper('aitloadmon')->__('Max load'),
            'value'     => number_format($this->getMaxLoad(),2).' Sec',
        ));

        $fieldset->addField('max_load_time', 'label', array(
            'label'     => Mage::helper('aitloadmon')->__('Max load time'),
            'title'     => Mage::helper('aitloadmon')->__('Max load time'),
            'value'     => $this->getMaxLoadTime(),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($compare->getHtmlId(), $compare->getName())
                ->addFieldMap($compareDate->getHtmlId(), $compareDate->getName())
                ->addFieldDependence(
                $compareDate->getName(),
                $compare->getName(),
                1
            )
        );
        return parent::_prepareForm();
    }
}