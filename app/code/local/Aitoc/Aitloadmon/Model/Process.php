<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Process extends Mage_Core_Controller_Varien_Front
{
    /**
     * Array that will cache already parsed urls in this iteration
     * @var array
     */
    protected $_urlLog = array();

    /**
     * Array to cache store prefixes/folders that should be removed
     * @var array
     */
    protected $_urlCuts = null;

    /**
     * Collects Magento routes
     *
     * @return Aitoc_Aitloadmon_Model_Process
     */
    protected function _init()
    {
        Aitoc_Aitloadmon_Collect::saveCronStartedFlag();

        $router = Mage::getModel('aitloadmon/router');
        $router->collectRoutes('frontend', 'standard');
        $this->addRouter('custom', $router);

        $cms = new Mage_Cms_Controller_Router();
        $this->addRouter('cms', $cms);

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
        $concurrents = array();
        foreach($data as $data_item)
        {
            if(isset($data_item['request_uri']) && isset($data_item['start']) && $data_item['start'] && isset($data_item['end']) && $data_item['end'])
            {
                if($groupId = $this->_getGroupIdByUri('http://some.host'.$data_item['request_uri']))
                {
                    if(!isset($result[$groupId]))
                    {
                        $result[$groupId] = array('sum'=>0,'max'=>0,'cnt'=>0,'concurrent'=>0,'concurrent_load'=>0);
                        $concurrents[$groupId] = array();
                    }
                    $loadTime = (float)$data_item['end'] - (float)$data_item['start'];
                    $result[$groupId]['sum'] += $loadTime;
                    $result[$groupId]['end'] = $data_item['end'];
                    if($loadTime > $result[$groupId]['max'])
                    {
                        $result[$groupId]['max'] = $loadTime;
                    }
                    $result[$groupId]['cnt']++;
                    $concurrents[$groupId]['starts'][] = array('start' => $data_item['start'], 'loadTime' => $loadTime);
                    $concurrents[$groupId]['ends'][] = array('end' => $data_item['end'], 'loadTime' => $loadTime);
                }
            }

        }

        $tmp_data = array(); $cnt = 0;
        foreach($concurrents as $groupId => $groupData)
        {
            $tmp_data = array_merge_recursive($tmp_data, $groupData); $cnt+=count($groupData['starts']);
            list($result[$groupId]['concurrent'],$result[$groupId]['concurrent_load']) = $this->_getConcurrent($groupData);
        }
        $tmp_data = $this->_getConcurrent($tmp_data);
        $result['concurrent'] = $tmp_data[0];
        return $result;
    }

    private function _sortConcurrentData($a, $b)
    {
        $key = isset($a['start'])?'start':'end';
        return ($a[$key] < $b[$key]) ? -1 : 1;
    }


    /**
     * Calculates data for concurrent scripts and load time
     *
     * @param array $data
     * @return array
     */
    private function _getConcurrent($data)
    {
        $tmp_conc = 0;
        $tmp_load = 0;
        $result = array(0,0);
        $i = 0;
        if(!is_array($data['starts']) || !is_array($data['ends']))
        {
            return $result;
        }
        usort($data['starts'], array($this,'_sortConcurrentData'));
        usort($data['ends'], array($this,'_sortConcurrentData'));

        foreach($data['ends'] as $end)
        {
            while(isset($data['starts'][$i]) && $data['starts'][$i]['start'] <= $end['end'])
            {
                $tmp_conc++;
                $tmp_load += $data['starts'][$i]['loadTime'];
                $i++;
                if($result[0] < $tmp_conc)
                {
                    $result[0] = $tmp_conc;
                    $result[1] = (float)$tmp_load/$tmp_conc;
                }
            }
            $tmp_conc--;
            $tmp_load -= $end['loadTime'];
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
        $maxAvgLoadTime = 0;
        foreach($result as $groupId => $resultItem)
        {
            if(is_integer($groupId))
            {
                $avgLoadTime = $resultItem['sum']/$resultItem['cnt'];
                if($avgLoadTime > $maxAvgLoadTime)
                {
                    $maxAvgLoadTime = $avgLoadTime;
                }
                $model->unsetData();
                $model->setData(array(

                    'measure_time'              => date('Y-m-d H:i:s',ceil((int)$resultItem['end']/60)*60),
                    'load_time_avg'             => $avgLoadTime,
                    'load_time_max'             => $resultItem['max'],
                    'page_group_id'             => $groupId,
                    'page_views'                => $resultItem['cnt'],
                    'max_page_views_per_minute' => $resultItem['cnt'],
                    'concurrent'                => $resultItem['concurrent'],

                ));
                $model->save();

                $calcModel->addData(array(
                    'pg'.$groupId.'_concurrent_load'   => $resultItem['concurrent_load'],
                    'pg'.$groupId.'_concurrent'      => $resultItem['concurrent'],
                ));

            }
        }

        if($result['concurrent'])
        {
            $calcModel->setConcurrent($result['concurrent']);
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
                $minute = ceil((int)$dataItem['end']/60);
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
        if(is_null($this->_urlCuts))
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

        if (Mage::helper('aitloadmon')->checkUriExclude($uri))
        {
            return false;
        }
        if(isset($this->_urlLog[$uri])) {
            return $this->_urlLog[$uri];
        }
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
        $return = 1;//default is 'other'
        if($module == 'admin')
        {
            $return = false;
        }
        elseif(isset($groupIds[$module]))
        {
            $return = $groupIds[$module];
        }
        $this->_urlLog[$uri] = $return;
        return $return;
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
    
    protected function _processRewriteUrl($url)
    {
        $startPos = strpos($url, '{');
        if ($startPos!==false) {
            $endPos = strpos($url, '}');
            $routeName = substr($url, $startPos+1, $endPos-$startPos-1);
		$router = Mage::getModel('aitloadmon/router');//$this->getRouterByRoute($routeName);
            if ($router) {
                $fronName = $router->getFrontNameByRoute($routeName);
                $url = str_replace('{'.$routeName.'}', $fronName, $url);
            }
        }
        return $url;
    }
}