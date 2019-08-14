<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon extends Mage_Core_Model_Mysql4_Abstract
{

    private $_sysInfo;


    /**
     * Standard model constructor
     */
    public function _construct()
    {   
        $this->_init('aitloadmon/aitloadmon', 'id');
    }

    /**
     * Initiates the data compression based on settings
     */
    public function compress()
    {

        $settings = Mage::getModel('aitloadmon/manage')->getSettings();

        if(!$settings['enabled'])
        {
            return;
        }

        //last day
        $format = $this->_getFormatByCompressType($settings['day']);
        $from = 'DATE_SUB( NOW( ) , INTERVAL 2 DAY )';
        $to = 'DATE_SUB( NOW( ) , INTERVAL 1 DAY )';
        $this->_compress($from, $to, $format);

        //last month
        $format = $this->_getFormatByCompressType($settings['month']);
        $from = 'DATE_SUB( NOW( ) , INTERVAL 2 MONTH )';
        $to = 'DATE_SUB( NOW( ) , INTERVAL 1 MONTH )';
        $this->_compress($from, $to, $format);

        //last year
        $format = $this->_getFormatByCompressType($settings['year']);
        $from = 'DATE_SUB( NOW( ) , INTERVAL 2 YEAR )';
        $to = 'DATE_SUB( NOW( ) , INTERVAL 1 YEAR )';
        $this->_compress($from, $to, $format);

    }

    /**
     * Returns compress formats for the data compression
     *
     * @return array
     */
    private function _getCompressFormats()
    {
        return array(
            1   =>  '%y-%m-%d-%H',
            2   =>  '%y-%m-%d',
            3   =>  '%y-%m',
        );
    }


    /**
     * Gets the compress format by the compress type
     *
     * @param $id
     * @return mixed
     */
    private function _getFormatByCompressType($id)
    {
        $formats = $this->_getCompressFormats();
        if(isset($formats[$id]))
        {
            return $formats[$id];
        }
        else
        {
            return $formats[1];
        }
    }

    /**
     * Initiates manual compression
     *
     * @param string $from
     * @param string $to
     * @param int $formatId
     */
    public function manualCompress($from, $to, $formatId)
    {
        $this->_compress('\''.$from.'\'', '\''.$to.'\'', $this->_getFormatByCompressType($formatId));
    }

    /**
     * Gets the system table info
     *
     * @return mixed
     */
    private function _getSysInfo()
    {
        if(!isset($this->_sysInfo))
        {
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $query = 'SHOW TABLE STATUS WHERE Name = \''.$this->getTable('aitloadmon/aitloadmon').'\'';
            $this->_sysInfo = $read->fetchRow($query);
        }

        return $this->_sysInfo;
    }

    /**
     * Gets the number of rows of the table
     *
     * @return int
     */
    public function getRows()
    {
        $data = $this->_getSysInfo();
        return isset($data['Rows'])?$data['Rows']:0;
    }

    /**
     * Gets the size of the table
     *
     * @return int
     */
    public function getWeight()
    {
        $data = $this->_getSysInfo();
        return isset($data['Data_length'])?$data['Data_length']:0;
    }

    /**
     * Compresses data in the database
     *
     * @param string $from
     * @param string $to
     * @param string $format
     */
    private function _compress($from, $to, $format)
    {
        $table = $this->getTable('aitloadmon');

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $write = $read = Mage::getSingleton('core/resource')->getConnection('core_write');

        $where = 'measure_time>='.$from.' AND measure_time<='.$to;

        $readIdsQuery = 'SELECT id FROM '.$table.' WHERE '.$where;
        $ids = $read->fetchCol($readIdsQuery);
        if(!empty($ids))
        {
            $insertQuery ='INSERT INTO '.$table.' (measure_time, load_time_avg, load_time_max, page_group_id, page_views, max_page_views_per_minute, concurrent)
                        SELECT MAX( measure_time ) AS measure_time, AVG( load_time_avg ) AS load_time_avg, MAX( load_time_max ) AS load_time_max, page_group_id, SUM( page_views ) AS page_views, MAX( max_page_views_per_minute ) AS max_page_views_per_minute, MAX( concurrent ) AS concurrent
                        FROM '.$table.'
                        WHERE '.$where.'
                        GROUP BY DATE_FORMAT( `measure_time` , \''.$format.'\' ) , page_group_id';
            $write->query($insertQuery);

            $deleteQuery = 'DELETE FROM '.$table.' WHERE id IN('.implode(',',$ids).')';
            $write->query($deleteQuery);
        }
    }

}