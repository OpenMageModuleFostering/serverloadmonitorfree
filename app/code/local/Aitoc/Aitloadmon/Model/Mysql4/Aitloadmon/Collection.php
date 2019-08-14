<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Standard constructor
     */
    public function _construct()
    {
        $this->_init('aitloadmon/aitloadmon');
    }

    /**
     * Returns a string for GROUP BY in SQL
     *
     * @param string $startDate
     * @param string $endDate
     * @return bool|string
     */
    private function _getGroupString($startDate, $endDate)
    {
        $diff = strtotime($endDate) - strtotime($startDate);
        switch($diff)
        {
            case ($diff>30*24*3600):
                $format = '%y-%m';
                break;
            case ($diff>24*3600):
                $format = '%y-%m-%d';
                break;
            case ($diff>3600):
                $format = '%y-%m-%d-%H';
                break;
            default:
                return false;
                //$format = '%y-%m-%d-%H-%i';
                break;
        }

        return 'DATE_FORMAT (`measure_time`, "'.$format.'"), page_group_id';
    }

    /**
     * Gets a data collection bounded by 2 dates
     *
     * @param string $startDate
     * @param string $endDate
     * @return Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection
     */
    public function filterByDates($startDate, $endDate)
    {
        if($groupString = $this->_getGroupString($startDate, $endDate))
        {
            $this->getSelect()->group($groupString)
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('measure_time, page_group_id, AVG(load_time_avg) AS load_time_avg, MAX(load_time_max) AS load_time_max, SUM(page_views) AS page_views');
        }

        $this->getSelect()->where('measure_time >=?',$startDate)
            ->where('measure_time <=?',$endDate)
            ->order('measure_time');
        return $this;
    }
}