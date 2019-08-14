<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Process extends Mage_Core_Controller_Varien_Front
{
    /**
     * Collects Magento routes
     *
     * @return Aitoc_Aitloadmon_Model_Process
     */
    protected function _init()
    {
        $router = Mage::getModel('aitloadmon/router');
        $router->collectRoutes('frontend', 'standard');
        $this->addRouter('custom', $router);

        $default = new Mage_Core_Controller_Varien_Router_Default();
        $this->addRouter('default', $default);

        return $this;
    }

    /**
     * Pre-processes data for saving
     *
     * @param array $data
     * @return array
     */
    private function _prepareForSave($data)
    {
        $result = array();
        foreach($data as $data_item)
        {
            if(isset($data_item['request_uri']) && isset($data_item['start']) && $data_item['start'] && isset($data_item['end']) && $data_item['end'])
            {
                if($groupId = $this->_getGroupIdByUri('http://some.host'.$data_item['request_uri']))
                {
                    if(!isset($result[$groupId]))
                    {
                        $result[$groupId] = array('sum'=>0,'max'=>0,'cnt'=>0);
                    }
                    $loadTime = (float)$data_item['end'] - (float)$data_item['start'];
                    $result[$groupId]['sum'] += $loadTime;
                    $result[$groupId]['end'] = $data_item['end'];
                    if($loadTime > $result[$groupId]['max'])
                    {
                        $result[$groupId]['max'] = $loadTime;
                    }
                    $result[$groupId]['cnt']++;
                }
            }

        }
        return $result;
    }

    /**
     * Saves the processed data to database
     *
     * @param array $result
     * @return float|int
     */
    private function _saveData($result)
    {
        $model = Mage::getModel('aitloadmon/aitloadmon');
        $calcModel = Mage::getModel('aitloadmon/calculator');
        $calcModel->load(0);
        $maxAvgLoadTime = 0;
        $maxPageViewsPerMinute = 0;
        $sumLoadTime = 0;
        foreach($result as $groupId => $resultItem)
        {
            $avgLoadTime = $resultItem['sum']/$resultItem['cnt'];
            if($avgLoadTime > $maxAvgLoadTime)
            {
                $maxAvgLoadTime = $avgLoadTime;
            }
            $model->load(0);
            $model->setData(array(

                'measure_time'              => date('Y-m-d H:i:s',ceil($resultItem['end']/60)*60),
                'load_time_avg'             => $avgLoadTime,
                'load_time_max'             => $resultItem['max'],
                'page_group_id'             => $groupId,
                'page_views'                => $resultItem['cnt'],
                'max_page_views_per_minute' => $resultItem['cnt'],

            ));
            $model->save();

            $calcModel->addData(array(
                'pg'.$groupId.'_avg_load'   => $avgLoadTime,
                'pg'.$groupId.'_views'      => $resultItem['cnt'],
            ));

            $sumLoadTime += $resultItem['sum'];
            $maxPageViewsPerMinute += $resultItem['cnt'];
        }

        if($maxPageViewsPerMinute)
        {
            $calcModel->addData(array(
                'load_time_avg'             => $sumLoadTime/$maxPageViewsPerMinute,
                'max_page_views_per_minute' => $maxPageViewsPerMinute,
            ));
            $calcModel->save();
        }
        return $maxAvgLoadTime;
    }

    /**
     * Returns a load level based on load time
     *
     * @param float|int $maxAvgLoadTime
     * @return int
     */
    private function _getLoadLevelByLoad($maxAvgLoadTime)
    {
        $yellow = Mage::getStoreConfig('system/aitloadmon/load_yellow');
        $red = Mage::getStoreConfig('system/aitloadmon/load_red');
        $black = Mage::getStoreConfig('system/aitloadmon/load_black');
        if($maxAvgLoadTime > $black)
        {
            $loadLevel = Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_BLACK;
        }
        elseif($maxAvgLoadTime > $red)
        {
            $loadLevel = Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_RED;
        }
        elseif($maxAvgLoadTime > $yellow)
        {
            $loadLevel = Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_YELLOW;
        }
        else
        {
            $loadLevel = Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_GREEN;
        }
        return $loadLevel;
    }

    /**
     * Splits the data by minutes
     *
     * @param $data
     * @return array
     */
    private function _getTimedData($data)
    {
        $return = array();
        foreach($data as $dataItem)
        {
            if(isset($dataItem['end']))
            {
                $minute = ceil($dataItem['end']/60);
                if(!isset($dataItem['end']))
                {
                    continue;
                }
                if(!isset($return[$minute]))
                {
                    $return[$minute] = array();
                }
                $return[$minute][] = $dataItem;
            }
        }

        return $return;
    }

    /**
     * Processes the data collected earlier
     *
     * @return array
     */
    public function processData()
    {

        $this->_init();
        $rawData = Aitoc_Aitloadmon_Collect::getData();
        $maxAvgLoadTime = 0;

        $timedData = $this->_getTimedData($rawData);

        foreach($timedData as $data)
        {
            $result = $this->_prepareForSave($data);
            $maxAvgLoadTimeTemp = $this->_saveData($result);
            $maxAvgLoadTime = ($maxAvgLoadTime<$maxAvgLoadTimeTemp)?$maxAvgLoadTimeTemp:$maxAvgLoadTime;
        }

        $currentLoadLevel = $this->_getLoadLevelByLoad($maxAvgLoadTime);
        $oldLoadLevel = Mage::helper('aitloadmon')->getLoadLevel();
        if($currentLoadLevel != $oldLoadLevel)
        {
            Mage::helper('aitloadmon')->setLoadLevel($currentLoadLevel);
            Mage::dispatchEvent('aitloadmon_load_level_changed',array('from'=>$oldLoadLevel,'to'=>$currentLoadLevel));
        }
        return isset($result)?$result:array();
    }

    private function _getUrlCuts()
    {
        if(!isset($this->_urlCuts))
        {
            $stores = Mage::getModel('core/store')->getCollection();
            $storeUrls = array();
            $urlCuts = array();
            foreach($stores as $store)
            {
                $id = $store->getId();
                $storeUrls[] = Mage::getStoreConfig('web/unsecure/base_url',$id);
                $storeUrls[] = Mage::getStoreConfig('web/secure/base_url',$id);
                $storeUrls[] = Mage::getStoreConfig('web/unsecure/base_link_url',$id);
                $storeUrls[] = Mage::getStoreConfig('web/secure/base_link_url',$id);
            }
            foreach(array_unique($storeUrls) as $url)
            {
                $parsedUrl = parse_url($url);
                if(isset($parsedUrl['path']))
                {
                    $url = ltrim($parsedUrl['path'],'/');
                    if($url)
                    {
                        $urlCuts[] = $url;
                    }
                }
            }
            $this->_urlCuts = $urlCuts;
        }
        return $this->_urlCuts;
    }


    /**
     * Gets page view group based on its request uri
     *
     * @param string $uri
     * @return int|bool
     */
    private function _getGroupIdByUri($uri)
    {
        $urlCuts = $this->_getUrlCuts();
        foreach($urlCuts as $urlCut)
        {
            $uri = str_replace($urlCut,'',$uri);
        }
        $uri = str_replace('index.php/','',$uri);
        $request = new Mage_Core_Controller_Request_Http($uri);
        //commented if for 1401 support
        //if (!$request->isStraight()) {
            Mage::getModel('aitloadmon/urlRewrite')->rewrite($request);
        //}
        $this->_rewriteRequest($request);

        foreach ($this->_routers as $router) {
            if ($router->match($request)) {
                break;
            }
        }
        $module = $request->getModuleName();
        $groupIds = Mage::helper('aitloadmon')->getGroupsArray();
        if($module == 'admin')
        {
            return false;
        }
        elseif(isset($groupIds[$module]))
        {
            return $groupIds[$module];
        }
        else
        {
            return 1;
        }
    }

    /**
     * Applies rewrites to the request object
     *
     * @param $request
     */
    private function _rewriteRequest($request)
    {
        $config = Mage::getConfig()->getNode('global/rewrite');
        if (!$config) {
            return;
        }
        foreach ($config->children() as $rewrite) {
            $from = (string)$rewrite->from;
            $to = (string)$rewrite->to;
            if (empty($from) || empty($to)) {
                continue;
            }
            $from = $this->_processRewriteUrl($from);
            $to   = $this->_processRewriteUrl($to);

            $pathInfo = preg_replace($from, $to, $request->getPathInfo());

            if (isset($rewrite->complete)) {
                $request->setPathInfo($pathInfo);
            } else {
                $request->rewritePathInfo($pathInfo);
            }
        }

    }

}