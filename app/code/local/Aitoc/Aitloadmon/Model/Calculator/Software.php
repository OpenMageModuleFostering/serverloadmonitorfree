<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc.
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Calculator_Software extends Aitoc_Aitloadmon_Model_Calculator_Abstract
{
    protected $_acceleratorInis = array('eAccelerator'=>'eaccelerator.enable','APC'=>'apc.enabled','xCache'=>'xcache.cacher','Zend Optimizer+'=>'zend_optimizer.optimization_level','WinCache'=>'wincache.ocenabled');
    protected $_osInfoFiles = array('/etc/SUSE-release','/etc/redhat-release','/etc/redhat_version','/etc/fedora-release','/etc/slackware-release','/etc/slackware-version',
        '/etc/debian_release','/etc/debian_version','/etc/mandrake-release','/etc/yellowdog-release','/etc/sun-release','/etc/release','/etc/gentoo-release','/etc/UnitedLinux-release',
        '/etc/lsb-release'
    );

    /**
     * Gets OS info from system files
     *
     * @return null|string
     */
    protected function _readOsInfoFromFiles()
    {
        foreach($this->_osInfoFiles as $file)
        {
            if($data = $this->_getFileSafe($file))
            {
                if(strlen($data)>256)
                {
                    $data = substr($data,0,256);
                }
                break;
            }
        }
        return $data;
    }

    /**
     * Gets the OS info
     *
     * @return array
     */
    protected function _getOsInfo()
    {
        if(!($os = $this->_exec('lsb_release -a 2>/dev/null')) && !($os = $this->_readOsInfoFromFiles()))
        {
            $os = php_uname();
        }
        return array('os'=>$os);
    }


    /**
     * Get webserver type
     *
     * @return array
     */
    protected function _getWebserverInfo()
    {
        return array('webserver' => isset($_SERVER["SERVER_SOFTWARE"])?$_SERVER["SERVER_SOFTWARE"]:'');
    }

    /**
     * Get MySQL version
     *
     * @return array
     */
    protected function _getMysqlInfo()
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT VERSION()';
        return array('mysql' => $readConnection->fetchOne($query));
    }

    /**
     * Get PHP information
     *
     * @return array
     */
    protected function _getPhpInfo()
    {

        return array('php' => array('version' => phpversion(),'memory_limit' => ini_get('memory_limit'), 'server_api' => php_sapi_name()));
    }

    /**
     * Get PHP Accelerator information
     *
     * @return array
     */
    protected function _getPhpAccelInfo()
    {
        $accelerator = array();
        foreach($this->_acceleratorInis as $name => $setting)
        {
            if(ini_get($setting))
            {
                $accelerator[] = $name;
            }
        }

        return array('php_accel' => $accelerator);
    }

}