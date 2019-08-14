<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */     
    $installer = $this;
     
    $installer->startSetup();
     
    $installer->run("
     
    -- DROP TABLE IF EXISTS {$this->getTable('aitloadmon')};
    CREATE TABLE {$this->getTable('aitloadmon')} (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `measure_time` datetime NOT NULL,
          `load_time_avg` float NOT NULL,
          `load_time_max` float NOT NULL,
          `page_group_id` int(11) NOT NULL,
          `page_views` int(11) NOT NULL,
          `max_page_views_per_minute` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `measure_time` (`measure_time`)
        ) ENGINE=MyISAM ;

        ");

    $installer->run("

    -- DROP TABLE IF EXISTS {$this->getTable('aitloadmon/calculator')};
    CREATE TABLE {$this->getTable('aitloadmon/calculator')} (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `load_time_avg` float NOT NULL,
          `max_page_views_per_minute` int(11) NOT NULL,
          `pg1_avg_load` float NOT NULL,
          `pg1_views` int(11) NOT NULL,
          `pg2_avg_load` float NOT NULL,
          `pg2_views` int(11) NOT NULL,
          `pg3_avg_load` float NOT NULL,
          `pg3_views` int(11) NOT NULL,
          `pg4_avg_load` float NOT NULL,
          `pg4_views` int(11) NOT NULL,
          `pg5_avg_load` float NOT NULL,
          `pg5_views` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM ;

        ");
     
    $installer->endSetup();