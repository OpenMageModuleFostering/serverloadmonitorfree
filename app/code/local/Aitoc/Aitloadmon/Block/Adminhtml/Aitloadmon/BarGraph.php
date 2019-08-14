<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Aitloadmon_BarGraph extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Gets collection(s) from parent block and processes data for javascript graphics
     */
    protected function _beforeToHtml()
    {
        $collection = $this->getParentBlock()->getCollection();
        if($this->getCompare())
        {
            $collectionCompare = $this->getParentBlock()->getCompareCollection();
        }

        $barData = $this->_preProcessDataForBar($collection);
        $barDataCompare = null;
        if($this->getCompare())
        {
            $barDataCompare = $this->_preProcessDataForBar($collectionCompare);
        }
        $this->_processDataForBar($barData,$barDataCompare);
    }

    /**
     * Returns whether the compare mode is enabled
     *
     * @return bool
     */
    public function getCompare()
    {
        return $this->getParentBlock()->getCompare();
    }

    /**
     * Pre-processes data for further data processing
     *
     * @param Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection $collection
     * @param bool $dataForCompare [false]
     * @return array
     */
    private function _preProcessDataForBar($collection, $dataForCompare = false)
    {
        $data = array();
        $totalPageViews = 0;
        $maxLoad = 0;
        $maxLoadTime = 0;
        $totalPageViewsByGroup = array();

        foreach($collection as $item)
        {
            $pageGroupId = $item->getData('page_group_id');
            if(!isset($totalPageViewsByGroup[$pageGroupId]))
            {
                $totalPageViewsByGroup[$pageGroupId]=0;
            }
            $totalPageViewsByGroup[$pageGroupId] += $item->getPageViews();
            $totalPageViews +=$item->getPageViews();
            if($item->getLoadTimeMax()>$maxLoad)
            {
                $maxLoad = $item->getLoadTimeMax();
                $maxLoadTime = $item->getMeasureTime();
            }
        }
        if(!$dataForCompare)
        {
            $this->getParentBlock()->setTotalPageViews($totalPageViews);
            $this->getParentBlock()->setMaxLoad($maxLoad);
            $this->getParentBlock()->setMaxLoadTime($maxLoadTime);
        }

        foreach($collection as $item)
        {
            $pageGroupId = $item->getData('page_group_id');
            if(!isset($data[$pageGroupId]))
            {
                $data[$pageGroupId] = array('avg'=>0,'max'=>0);
            }
            if($item->getLoadTimeMax()>$data[$pageGroupId]['max'])
            {
                $data[$pageGroupId]['max'] = $item->getLoadTimeMax();
            }
            $data[$pageGroupId]['avg'] += $item->getLoadTimeAvg() * $item->getPageViews()/$totalPageViewsByGroup[$pageGroupId];

        }
        return $data;
    }

    /**
     * Processes data and stores it as this object property
     *
     * @param array $data
     * @param bool $dataCompare
     */
    private function _processDataForBar($data, $dataCompare)
    {
        $loadAvg = 'avg_load: [';
        $loadMax = 'max_load: [';
        $groups = Mage::helper('aitloadmon')->getGroupsArray();
        $gcnt = 0;
        foreach($groups as $pageGroupId)
        {
            if(!isset($data[$pageGroupId]))
            {
                $loadAvg .= '0';
                $loadMax .= '0';
            }
            else
            {
                $loadAvg .= $data[$pageGroupId]['avg'];
                $loadMax .= $data[$pageGroupId]['max'];
            }

            if($dataCompare)
            {
                if(!isset($dataCompare[$pageGroupId]))
                {
                    $loadAvg .= ',0';
                    $loadMax .= ',0';
                }
                else
                {
                    $loadAvg .= ','.$dataCompare[$pageGroupId]['avg'];
                    $loadMax .= ','.$dataCompare[$pageGroupId]['max'];
                }
            }

            $gcnt++;
            if($gcnt != count($groups))
            {
                $loadAvg .= ',';
                $loadMax .= ',';
            }
        }
        $loadAvg .= ']';
        $loadMax .= ']';

        $this->setBarGraphData($loadMax.','.$loadAvg);
    }

    /**
     * Returns a JS array of labels
     *
     * @param bool $compareMode
     * @return string
     */
    public function getBarLabels($compareMode)
    {

        $groups = array_keys(Mage::helper('aitloadmon')->getGroupsArray());
        if($compareMode)
        {
            $glue = '\',\'\',\'';
        }
        else
        {
            $glue = '\',\'';
        }


        return '[\''.implode($glue, $groups).($compareMode?'\',\'':'').'\']';
    }
}
