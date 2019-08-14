<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Indicator extends Mage_Adminhtml_Block_Template
{
    /**
     * Returns an array of colors to use for different load levels in indicator
     *
     * @return string
     */
    private function _getColors()
    {
        return array(
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_GREEN     => '#1B7B00',
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_YELLOW    => '#f5861a',
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_RED       => '#a0151e',
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_BLACK     => '#000000',
        );
    }

    /**
     * Returns the color of the current load level or a green color whe the load level is unavailable for some reason
     *
     * @return string
     */
    public function getColor()
    {
        $colors = $this->_getColors();
        $loadLevel = Mage::helper('aitloadmon')->getLoadLevel();
        if($loadLevel && isset($colors[$loadLevel]))
        {
            return $colors[$loadLevel];
        }
        return $colors[Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_GREEN];
    }
}