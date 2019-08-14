<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Analytics
{
    /**
     * Gets the max number of visitors per minute parsing google analytics file
     *
     * @param string $filename
     * @return int
     */
    public function getVisitors($filename)
    {
        $f = fopen($filename,"r");
        $max = 0;
        while($line = fgetcsv($f,0,','))
        {
            if(count($line)==2 && $line[0]!='' && isset($line[1]))
            {
                if($line[1]>$max)
                {
                    $max = $line[1];
                }
            }
        }
        fclose($f);

        return ceil($max/60);
    }
}