<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc.
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Calculator_Hardware extends Aitoc_Aitloadmon_Model_Calculator_Abstract
{
    private $_linuxCpuFile = '/proc/cpuinfo';
    private $_linuxMemFile = '/proc/meminfo';

    /**
     * Gets the CPU info of a server
     *
     * @return array
     */
    protected function _getCpuInfo()
    {
        $info = $this->_getFileSafe($this->_linuxCpuFile);
        if($info)
        {//^(processor\s+:\s+\d+)\s*$.*?
            $freqs = array();
            $bogomipss = array();
            $modelNames = array();
            if(preg_match_all('/^cpu\sMHz\s+:\s+([\d\.]+)\s*$/ms',$info,$matches))
            {
                $freqs = $matches[1];
            }
            if(preg_match_all('/^bogomips\s+:\s+([\d\.]+)\s*$/ms',$info,$matches))
            {
                $bogomipss = $matches[1];
            }
            if(preg_match_all('/^model\sname\s+:\s+([\w\d\s\(\)]+)\s*$/ms',$info,$matches))
            {
                $modelNames = $matches[1];
            }
            return array('cpu'=>array('cores'=>count($freqs),'freq'=>max($freqs),'bogomips'=>max($bogomipss),'model name'=>isset($modelNames[0])?$modelNames[0]:''));
        }

        return null;
    }

    /**
     * Gets the memory info of a server
     *
     * @return array|null
     */
    protected function _getMemInfo()
    {
        $info = $this->_getFileSafe($this->_linuxMemFile);
        if($info)
        {
            if(preg_match('/MemTotal:(.*?B)/si',$info,$matches))
            {
                return array('mem'=>trim($matches[1]));
            }
        }
        return null;
    }

    /**
     * Gets if the server is virual
     *
     * @return array
     */
    protected function _getVirtualInfo()
    {//todo think of the additional ways
        return array('virtual'=>file_exists('/proc/vz/veinfo')?'true':'false');
    }
}