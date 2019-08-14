<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc.
 * @license License agreement could be found at the root folder of this package
 */
abstract class Aitoc_Aitloadmon_Model_Calculator_Abstract
{
    protected $_infoMethodPattern = '/_get\w+Info/';

    /**
     * Executes a system command and returns its output
     *
     * @param $command
     * @return string
     */
    protected function _exec($command)
    {
        $data = array();
        @exec($command, $data);
        if(count($data))
        {
            return implode("\r\n",$data);
        }
        else
        {
            return '';
        }
    }

    /**
     * Safely gets the contents of a file
     *
     * @param string $file
     * @return null|string
     */
    protected function _getFileSafe($file)
    {
        try{
            if($data = @file_get_contents($file))
            {
                return $data;
            }
            if($data = $this->_exec('cat '.$file.' 2>/dev/null'))
            {
                return $data;
            }
        }
        catch(Exception $e)
        {
            return null;
        }
        return null;
    }

    /**
     * Finds the methods that return required and returns them
     *
     * @return array
     */
    protected function _getInfoMethods()
    {
        $methods = get_class_methods(get_class($this));
        $infoMethods = array();
        foreach($methods as $method)
        {
            if(preg_match($this->_infoMethodPattern,$method))
            {
                $infoMethods[] = $method;
            }
        }

        return $infoMethods;
    }

    /**
     * Returns the required info as a joined array
     *
     * @return array
     */
    public function toArray()
    {
        $returnArray = array();
        foreach($this->_getInfoMethods() as $method)
        {
                $returnArray += $this->$method();
        }

        return $returnArray;
    }
}