<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc.
 * @license License agreement could be found at the root folder of this package
 */
    $installer = $this;

    $installer->startSetup();
    $installer->run("
          DROP TABLE IF EXISTS {$this->getTable('aitloadmon/calculator')};
          CREATE TABLE {$this->getTable('aitloadmon/calculator')} (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `concurrent` int(11) NOT NULL,
          `pg1_concurrent_load` float NOT NULL,
          `pg1_concurrent` int(11) NOT NULL,
          `pg2_concurrent_load` float NOT NULL,
          `pg2_concurrent` int(11) NOT NULL,
          `pg3_concurrent_load` float NOT NULL,
          `pg3_concurrent` int(11) NOT NULL,
          `pg4_concurrent_load` float NOT NULL,
          `pg4_concurrent` int(11) NOT NULL,
          `pg5_concurrent_load` float NOT NULL,
          `pg5_concurrent` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM ;
        ALTER TABLE {$this->getTable('aitloadmon')} ADD `concurrent` INT NOT NULL;
        ");
    $installer->endSetup();