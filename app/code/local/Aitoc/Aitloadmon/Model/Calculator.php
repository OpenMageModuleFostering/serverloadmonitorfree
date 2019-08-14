<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Calculator extends Mage_Core_Model_Abstract
{

    private $_apiUrl = 'http://www.aitoc.com/api/xmlrpc/statistic/';

    private $_infoClasses = array('magento','hardware','software');
    private $_period = '1 WEEK';

    /**
     * Standard model constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitloadmon/calculator');
    }

    /**
     * Getter for use in calculator results
     *
     * @return int
     */
    public function getProductInfo()
    {
        return Mage::getModel('catalog/product')->getCollection()->getSize();
    }

    /**
     * Collects the info that will be sent to Server Calculator
     *
     * @return array
     */
    private function _collectSendingInfo()
    {
        $data = array();
        foreach($this->_infoClasses as $class)
        {
            $model = Mage::getModel('aitloadmon/calculator_'.$class);
            if($model instanceof Aitoc_Aitloadmon_Model_Calculator_Abstract)
            {
                $data += $model->toArray();
            }
        }
        return $data;
    }

    /**
     * Sends the info required for server calculator and clears the load info table
     */
    public function sendCalcInfo()
    {
        $client = new Zend_XmlRpc_Client($this->_apiUrl);
        try{
            $client->call('call', array('1234567890', 'aitcalc.addStatistic', array($this->_collectSendingInfo())));
        }catch(Exception $e){
            Mage::log('Data sending error in '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * Gets the maximum number of page views per minute based on Magento logs
     *
     * @return int
     */
    public function getLogVisitors()
    {
        $collection = Mage::getModel('aitloadmon/dblog')->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('COUNT(*) AS visitors')
            ->where('visit_time > DATE_SUB(NOW(), INTERVAL '.$this->_period.')')
            ->group('DATE_FORMAT(visit_time,"%y-%m-%d-%H-%i")');
        $max = 0;
        foreach($collection as $item)
        {
            if($item->getVisitors()>$max)
            {
                $max = $item->getVisitors();
            }
        }
        return $max;
    }

    /**
     * Gets the maximum number of page views per minute based on monitor
     *
     * @return int
     */
    public function getMonitorVisitors()
    {
        $collection = Mage::getModel('aitloadmon/aitloadmon')->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('SUM(max_page_views_per_minute) AS visitors')
            ->where('measure_time > DATE_SUB(NOW(), INTERVAL '.$this->_period.')')
            ->group('DATE_FORMAT(measure_time,"%y-%m-%d-%H-%i")');

        $max = 0;
        foreach($collection as $item)
        {
            if($item->getVisitors()>$max)
            {
                $max = $item->getVisitors();
            }
        }
        return $max;
    }


    /**
     * Gets the maximum number of page views per minute based on Google Analytics file
     *
     * @return int|null
     */
    public function getAnalyticsVisitors()
    {
        if(file_exists($this->getCsvFile()))
        {
            return Mage::getModel('aitloadmon/analytics')->getVisitors($this->getCsvFile());
        }

        return null;
    }
}