<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Aitloadmon_LineGraph extends Mage_Adminhtml_Block_Widget_Form
{

    private $_maxLabelsInGraph = 20;

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

        $type = $this->getType();
        $lineData = $this->_preProcessDataForLine($collection);
        $this->_processDataForLine($lineData, $type);

        if($this->getCompare())
        {
            $lineDataCompare = $this->_preProcessDataForLine($collectionCompare);
            $this->_processDataForLine($lineDataCompare, $type, true);
        }
    }

    /**
     * Getting the type of values we show in graph (average or maximum)
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->getParentBlock()->getType();
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
     * Returns html for graphics controls
     *
     * @return string
     */
    public function getCheckBoxes()
    {
        $groups = Mage::helper('aitloadmon')->getGroupsArray();
        $html = '';
        foreach($groups as $groupName => $groupValue)
        {
            $html .= '<label><input type="checkbox" checked="checked" rel="'.$groupValue.'" onclick="toggleLines(this);" class="linegraph_check"/>'.ucfirst($groupName).'</label>';
        }
        return $html;
    }


    /**
     * Pre-processes data for further data processing
     *
     * @param Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection $collection
     * @return array
     */
    private function _preProcessDataForLine($collection)
    {
        $data = array();

        $dateModel = Mage::getModel('core/date');
        foreach($collection as $item)
        {
            $pageGroupId = $item->getData('page_group_id');
            $time = $dateModel->date('Y-m-d H:i:s',$item->getData('measure_time'));
            if(!isset($data[$time]))
            {
                $data[$time] = array();
            }
            $data[$time][$pageGroupId]['avg'] = number_format($item->getLoadTimeAvg(),2);
            $data[$time][$pageGroupId]['max'] = number_format($item->getLoadTimeMax(),2);
            $data[$time][$pageGroupId]['pgv'] = $item->getPageViews();
            $data[$time][$pageGroupId]['cnc'] = $item->getConcurrent();
        }
        return $data;
    }

    /**
     * Processes data and stores it as this object property
     * @param $data
     * @param string $type
     * @param bool $compare
     */
    private function _processDataForLine($data, $type = 'avg', $compare = false)
    {
        $groups = Mage::helper('aitloadmon')->getGroupsArray();
        $dataString = '';
        $dataSizeString = '';
        $concurrentString = '';
        $gcnt = 0;
        foreach($groups as $pageGroupCode => $pageGroupId)
        {
            $dataString .= $pageGroupCode.': [';
            $dataSizeString .= $pageGroupId.': [';
            $concurrentString .= $pageGroupId.': [';
            $dcnt = 0;
            foreach($data as $timeData)
            {
                if(!isset($timeData[$pageGroupId][$type]))
                {
                    $dataString .= '0';
                    $dataSizeString .= '0';
                    $concurrentString .= '0';
                }
                else
                {
                    $dataString .= $timeData[$pageGroupId][$type];
                    $dataSizeString .= $timeData[$pageGroupId]['pgv'];
                    $concurrentString .= $timeData[$pageGroupId]['cnc'];
                }
                $dcnt++;
                if($dcnt != count($data))
                {
                    $dataString .= ',';
                    $dataSizeString .= ',';
                    $concurrentString .= ',';
                }
            }
            $dataString .= ']';
            $dataSizeString .= ']';
            $concurrentString .= ']';
            $gcnt++;
            if($gcnt != count($groups))
            {
                $dataString .= ',';
                $dataSizeString .= ',';
                $concurrentString .= ',';
            }
        }

        $method = 'setLineGraphData';
        if($compare)
        {
            $method .= 'Compared';
        }

        $times = $this->_prepareHorizontalLabels(array_keys($data));
        $this->$method(array('data'=>$dataString,'labels'=>'[\''.implode('\',\'',$times).'\']','dot_labels'=>'[\''.implode('\',\'',array_keys($data)).'\']','dataSize'=>$dataSizeString,'concurrents'=>$concurrentString));
    }

    /**
     * Preparing horizontal labels for graph
     *
     * @param array $labelArray
     * @return array
     */
    private function _prepareHorizontalLabels($labelArray = array())
    {
        $labelArrayCount = count($labelArray);
        if($labelArrayCount>$this->_maxLabelsInGraph)
        {
            $each = 2;
            while(($labelArrayCount/$each) > $this->_maxLabelsInGraph)
            {
                $each++;
            }
            $cnt = 0;
            foreach($labelArray as $key=>$value)
            {
                $cnt++;
                if($cnt==$each)
                {
                    $cnt=0;
                }
                else
                {
                    $labelArray[$key]='';
                }
            }
        }

        $currentDate = '';
        foreach($labelArray as $key=>$value)
        {
            if($value=='')
            {
                continue;
            }

            list($date,$time) = explode(' ',$value);
            if($currentDate != $date)
            {
                $currentDate = $date;
                $labelArray[$key]=$time.'\n\r'.$date;
            }
            else
            {
                $labelArray[$key]=$time;
            }
        }

        return $labelArray;
    }
}
