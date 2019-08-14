<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_System extends Aitoc_Aitloadmon_Model_System_Abstract
{
    protected $_systemUrl = 'http://www.aitoc.com/monitor-module-news';
    protected $_systemKey = 'system/aitloadmon/system';
    protected $_systemDateKey = 'system/aitloadmon/system_date';
    protected $_expire = 86400;
}