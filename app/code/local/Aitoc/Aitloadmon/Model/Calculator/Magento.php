<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc.
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Calculator_Magento extends Aitoc_Aitloadmon_Model_Calculator_Abstract
{
    private $_interval = '5';

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
     * Gets the info about server load
     *
     * @return array
     */
    protected function _getLoadInfo()
    {
        $collection = Mage::getModel('aitloadmon/calculator')->getCollection();
        $colString = '';
        for($i=1; $i<6; $i++)
        {
            $colString .= 'MAX(pg'.$i.'_avg_load) AS pg'.$i.'_avg_load, pg'.$i.'_views, ';
        }
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($colString.'CEIL(max_page_views_per_minute/'.$this->_interval.')*'.$this->_interval.' AS views, MAX(load_time_avg) AS load')
            ->group('views');
        $data = array();
        foreach($collection as $item)
        {
            $data[$item->getViews()] = array('avg_load'=>$item->getLoad());
            for($i=1; $i<6; $i++)
            {
                $key = 'pg'.$i;
                $data[$item->getViews()][$key.'_avg_load'] = $item->getData($key.'_avg_load');
                $data[$item->getViews()][$key.'_views'] = $item->getData($key.'_views');
            }
        }
        Mage::getModel('aitloadmon/calculator')->getResource()->deleteAll();
        return array('load'=>$data);
    }

    /**
     * Gets the info whether the booster is installed
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