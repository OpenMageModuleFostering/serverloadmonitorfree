<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc.
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Calculator_Magento extends Aitoc_Aitloadmon_Model_Calculator_Abstract
{
    /**
     * Gets the system cache types
     *
     * @return array
     */
    protected function _getSystemcacheInfo()
    {
        return array('system_cache' => array('backend' => (string)Mage::app()->getConfig()->getNode('global/cache/backend'), 'slow_backend' => (string)Mage::app()->getConfig()->getNode('global/cache/slow_backend')));
    }

    /**
     * Gets the statuses of caches
     *
     * @return array
     */
    protected function _getCacheInfo()
    {
        $caches = array();
        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            $caches[$type->getId()] = $type->getStatus();
        }
        return array('caches' => $caches);
    }


    /**
     * Gets the compiler status
     *
     * @return array
     */
    protected function _getCompilerInfo()
    {
        return array('compiler' => defined('COMPILER_INCLUDE_PATH'));
    }

    /**
     * Gets the number of customers
     *
     * @return array
     */
    protected function _getCustomerInfo()
    {
        return array('customers'=>Mage::getModel('customer/customer')->getCollection()->getSize());
    }

    /**
     * Gets the number of products
     *
     * @return array
     */
    protected function _getProductInfo()
    {
        return array('products'=>Mage::getModel('catalog/product')->getCollection()->getSize());
    }

    /**
     * Gets the number of quotes
     *
     * @return array
     */
    protected function _getQuoteInfo()
    {
        return array('quotes'=>Mage::getModel('sales/quote')->getCollection()->getSize());
    }

    /**
     * Gets the number of orders
     *
     * @return array
     */
    protected function _getOrderInfo()
    {
        return array('orders'=>Mage::getModel('sales/order')->getCollection()->getSize());
    }

    /**
     * Gets Magento version
     *
     * @return array
     */
    protected function _getVersionInfo()
    {
        return array('version'=>Mage::getVersion());
    }

    /**
     * Populates an array with load data
     *
     * @param array $data
     */
    protected function _populateLoadData(&$data)
    {
        $colArray = array();
        for($i=1; $i<6; $i++)
        {
            $colArray['pg'.$i.'_concurrent_load'] = 'pg'.$i.'_concurrent_load';
        }
        $colArray['concurrent'] = 'concurrent';


        $collection = Mage::getModel('aitloadmon/calculator')->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($colArray);


        foreach($collection as $item)
        {
            if($item->getConcurrent())
            {
                if(!isset($data[$item->getConcurrent()]))
                {
                    $data[$item->getConcurrent()] = array();
                }
                for($i=1; $i<6; $i++)
                {
                    if(!isset($data[$item->getConcurrent()][$i]))
                    {
                        $data[$item->getConcurrent()][$i] = array();
                    }
                    if($item->getData('pg'.$i.'_concurrent_load'))
                    {
                        $data[$item->getConcurrent()][$i][] = $item->getData('pg'.$i.'_concurrent_load');
                    }
                }
            }

        }
        unset($collection);

        foreach($data as $concurrentGroupId => $concurrentGroupData)
        {
            foreach($concurrentGroupData as $pageGroupId => $pageGroupData)
            {
                unset($data[$concurrentGroupId][$pageGroupId]);
                sort($pageGroupData);
                $cnt = count($pageGroupData);
                if($cnt)
                {
                    $data[$concurrentGroupId][$pageGroupId]['min'] = (string)round($pageGroupData[0],3);
                    $data[$concurrentGroupId][$pageGroupId]['avg'] = (string)round(array_sum($pageGroupData)/$cnt,3);
                    $data[$concurrentGroupId][$pageGroupId]['max'] = (string)round($pageGroupData[$cnt-1],3);
                    $data[$concurrentGroupId][$pageGroupId]['med'] = (string)round($pageGroupData[ceil($cnt*0.5)-1],3);
                    $data[$concurrentGroupId][$pageGroupId]['90l'] = (string)round($pageGroupData[ceil($cnt*0.9)-1],3);
                }
            }
        }
    }

    /**
     * Populates an array with cnc sum pages data
     *
     * @param array $data
     */
    protected function _populateCncData(&$data)
    {
        $colArray = array();
        for($i=1; $i<6; $i++)
        {
            $colArray['pg'.$i.'_concurrent'] = 'SUM(pg'.$i.'_concurrent)';
        }
        $colArray['concurrent'] = 'concurrent';
        $collection = Mage::getModel('aitloadmon/calculator')->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($colArray)
            ->group('concurrent');

        foreach($collection as $item)
        {
            for($i=1; $i<6; $i++)
            {
                if($item->getData('pg'.$i.'_concurrent'))
                {
                    $data[$item->getConcurrent()][$i]['cnc'] = $item->getData('pg'.$i.'_concurrent');
                }
            }
        }
    }

    /**
     * Gets the info about server load
     *
     * @return array
     */
    protected function _getLoadInfo()
    {
        $data = array();
        $this->_populateLoadData($data);
        $this->_populateCncData($data);
        Mage::getModel('aitloadmon/calculator')->getResource()->deleteAll();
        return array('load'=>$data);
    }


    protected function _getMonitorVersionInfo()
    {
        return array('monitor_version' => (string)Mage::getConfig()->getNode()->modules->Aitoc_Aitloadmon->version);
    }

    /**
     * Gets the info whether the booster is enabled
     *
     * @return array
     */
    protected function _getBoosterInfo()
    {
        $enabled = 'false';
        $config  = @simplexml_load_file(Mage::getBaseDir().DS.'app'.DS.'etc'.DS.'modules'.DS.'Aitoc_Aitpagecache.xml');
        if ($config){
            $enabled = (string)$config->modules->Aitoc_Aitpagecache->active;
        }

        return array('booster'=>$enabled);
    }
}