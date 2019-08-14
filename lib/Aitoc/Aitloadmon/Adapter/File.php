<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adapter_File
{
    const DS = DIRECTORY_SEPARATOR;

    private static $_dataDir  = 'data';
    private static $_filename = 'data_';

    /**
     * Gets the path to the directory for the data files
     *
     * @return string
     */
    private function _getDataDir()
    {
        return Aitoc_Aitloadmon_Collect::getTempDir().self::$_dataDir.self::DS;
    }

    /**
     * Returns the file resource for writing
     *
     * @return resource
     */
    private function _getCurrentFileHandle()
    {
        $i = 0;
        $currentFileHandle = null;
        if(!file_exists($this->_getDataDir()))
        {
            mkdir($this->_getDataDir());
        }
        $curFilePath = $this->_getDataDir().self::$_filename;
        while(!$currentFileHandle)
        {
            $fileHandle = fopen($curFilePath.$i, 'a');
            $locked = flock($fileHandle, LOCK_EX);
            if($locked)
            {
                $currentFileHandle = $fileHandle;
            }
            $i++;
        }
        return $currentFileHandle;
    }

    /**
     * Writes a string to file
     *
     * @param string $string
     */
    private function _writeStringToFile($string)
    {
        $fileHandle = $this->_getCurrentFileHandle();
        fwrite($fileHandle,$string."\r\n");
        flock($fileHandle, LOCK_UN);
        fclose($fileHandle);
    }

    /**
     * Saves the data into storage
     *
     * @param array $params
     */
    public function save($params)
    {
        $this->_writeStringToFile(serialize($params));
    }

    /**
     * Gets the list of data files
     *
     * @return array
     */
    private function _getDataFileList()
    {
        return scandir($this->_getDataDir());
    }

    /**
     * Gets the data from the storage
     *
     * @return array
     */
    public function getData()
    {
        $dir = $this->_getDataDir();
        $data = array();
        foreach($this->_getDataFileList() as $fileName)
        {
            if($fileName != '.' && $fileName != '..')
            {
                $fileHandle = fopen($dir.$fileName, 'r');
                $locked = null;
                while(!$locked)
                {
                    $locked = flock($fileHandle, LOCK_EX);
                }

                while($string = fgets($fileHandle))
                {
                    $data = array_merge_recursive($data,unserialize($string));
                }
                flock($fileHandle, LOCK_UN);
                fclose($fileHandle);
                unlink($dir.$fileName);
            }
        }

        return $data;
    }

}